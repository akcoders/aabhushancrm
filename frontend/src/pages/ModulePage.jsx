import {useCallback,useEffect,useMemo,useRef,useState} from 'react'
import {useNavigate} from 'react-router-dom'
import {Download,Eye,Pencil,Plus,Search,SlidersHorizontal,Trash2,Upload} from 'lucide-react'
import api from '../api'
import {get,modules,money} from '../config'
import {Badge,Empty,ErrorText,Field,Loading,Modal} from '../components/UI'
import {useAuth} from '../AuthContext'

const amountFields=['balance','original_amount','estimated_amount','advance_payment','expense','value']

export default function ModulePage({module}){
  const cfg=modules[module]
  const {hasPermission}=useAuth()
  const nav=useNavigate()
  const [rows,setRows]=useState([])
  const [meta,setMeta]=useState()
  const [loading,setLoading]=useState(true)
  const [search,setSearch]=useState('')
  const [filters,setFilters]=useState({})
  const [editing,setEditing]=useState(null)
  const [form,setForm]=useState({})
  const [error,setError]=useState()
  const [busy,setBusy]=useState(false)
  const [salespeople,setSalespeople]=useState([])
  const importInput=useRef()

  useEffect(()=>{
    if(module==='leads'){
      api.get('/staff',{params:{sales_only:1,per_page:100}}).then(r=>setSalespeople(r.data.data||[]))
    }
  },[module])

  const fields=useMemo(()=>cfg.fields.map(field=>field[0]==='assigned_to'
    ? [...field.slice(0,4),salespeople.map(person=>({value:person.id,label:`${person.name} · ${person.role?.name||'Sales'}`}))]
    : field),[cfg.fields,salespeople])

  const load=useCallback(()=>{
    setLoading(true)
    api.get(`/${cfg.endpoint}`,{params:{search,...filters}})
      .then(r=>{setRows(r.data.data||r.data);setMeta(r.data.meta)})
      .finally(()=>setLoading(false))
  },[cfg.endpoint,search,filters])

  useEffect(()=>{const timer=setTimeout(load,250);return()=>clearTimeout(timer)},[load])

  const open=row=>{
    setEditing(row||{})
    setForm(row?Object.fromEntries(fields.map(([key])=>[key,get(row,key)??''])):{})
    setError(null)
  }

  const save=async event=>{
    event.preventDefault()
    setBusy(true)
    setError(null)
    try{
      editing?.id?await api.put(`/${cfg.endpoint}/${editing.id}`,form):await api.post(`/${cfg.endpoint}`,form)
      setEditing(null)
      load()
    }catch(exception){
      setError(exception)
    }finally{
      setBusy(false)
    }
  }

  const remove=async row=>{
    if(confirm(`Delete ${row.name||row.title||row.code||'this record'}?`)){
      await api.delete(`/${cfg.endpoint}/${row.id}`)
      load()
    }
  }

  const importExcel=async event=>{
    const file=event.target.files?.[0]
    if(!file)return
    const payload=new FormData()
    payload.append('file',file)
    setBusy(true)
    setError(null)
    try{
      const {data}=await api.post('/customers/import-excel',payload,{headers:{'Content-Type':'multipart/form-data'}})
      alert(data.message)
      load()
    }catch(exception){setError(exception)}finally{
      setBusy(false)
      event.target.value=''
    }
  }
  const downloadCustomerTemplate=async()=>{
    const response=await api.get('/customers/excel-template',{responseType:'blob'})
    const url=URL.createObjectURL(response.data)
    const link=document.createElement('a')
    link.href=url
    link.download='customer-import-template.xlsx'
    link.click()
    URL.revokeObjectURL(url)
  }

  const details=row=>{
    if(['leads','customers','custom-orders','exhibitions'].includes(module))nav(`/${module}/${row.id}`)
  }

  return <div>
    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">Relationship management</p>
        <h2 className="page-title mt-1">{cfg.title}</h2>
        <p className="mt-1 text-sm text-[#857d70]">{meta?.total??rows.length} records in your workspace</p>
      </div>
      <div className="flex gap-2">
        {module==='leads'&&<a className="btn-secondary" href={`${import.meta.env.VITE_API_URL||'http://127.0.0.1:8000/api'}/leads/export`}><Download size={16}/>Export</a>}
        {module==='customers'&&hasPermission('customers.create')&&<>
          <button className="btn-secondary" onClick={downloadCustomerTemplate}><Download size={16}/>Excel template</button>
          <input ref={importInput} type="file" accept=".xlsx,.xls" className="hidden" onChange={importExcel}/>
          <button disabled={busy} onClick={()=>importInput.current?.click()} className="btn-secondary"><Upload size={16}/>{busy?'Importing…':'Import Excel'}</button>
        </>}
        {hasPermission(`${module}.create`)&&<button onClick={()=>open()} className="btn-primary"><Plus size={17}/>Add {cfg.singular}</button>}
      </div>
    </div>
    <div className="card overflow-hidden">
      <div className="flex flex-wrap gap-3 border-b border-[#ece6da] p-4">
        <label className="relative min-w-56 flex-1">
          <Search className="absolute left-3 top-2.5 text-[#9c9589]" size={18}/>
          <input className="field pl-10" placeholder={`Search ${cfg.title.toLowerCase()}…`} value={search} onChange={event=>setSearch(event.target.value)}/>
        </label>
        {module==='leads'&&<select className="field w-auto min-w-44" value={filters.assigned_to||''} onChange={event=>setFilters({...filters,assigned_to:event.target.value})}>
          <option value="">All salespeople</option>
          {salespeople.map(person=><option key={person.id} value={person.id}>{person.name}</option>)}
        </select>}
        {Object.entries(cfg.filters||{}).map(([key,options])=><select key={key} className="field w-auto min-w-36" value={filters[key]||''} onChange={event=>setFilters({...filters,[key]:event.target.value})}>
          <option value="">All {key.replace('_',' ')}</option>
          {options.map(option=><option key={option}>{option}</option>)}
        </select>)}
        <button className="btn-secondary px-3"><SlidersHorizontal size={17}/></button>
      </div>
      {loading?<Loading/>:!rows.length?<Empty/>:<div className="overflow-x-auto">
        <table className="w-full min-w-[900px] text-left">
          <thead><tr className="border-b border-[#ece6da] bg-[#fcfbf8]">
            {cfg.columns.map(([,label])=><th key={label} className="px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-[#888073]">{label}</th>)}
            <th className="px-5 py-3 text-right text-[10px] uppercase text-[#888073]">Actions</th>
          </tr></thead>
          <tbody>{rows.map(row=><tr key={row.id} className="border-b border-[#f0ece4] hover:bg-[#fcfaf5]">
            {cfg.columns.map(([key],index)=>{
              let value=get(row,key)
              if(amountFields.includes(key))value=money(value)
              if(/_at$|_date$/.test(key)&&value)value=new Date(value).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})
              return <td key={key} className={`px-5 py-4 text-sm ${index===0?'font-semibold':'text-[#686155]'}`}>{/status|priority|category|type$/.test(key)?<Badge>{value}</Badge>:value??'—'}</td>
            })}
            <td className="px-5 py-4"><div className="flex justify-end gap-1">
              {['leads','customers','custom-orders','exhibitions'].includes(module)&&<button title="View" onClick={()=>details(row)} className="rounded-lg p-2 hover:bg-[#eee7d8]"><Eye size={16}/></button>}
              {hasPermission(`${module}.update`)&&<button title="Edit" onClick={()=>open(row)} className="rounded-lg p-2 hover:bg-[#eee7d8]"><Pencil size={16}/></button>}
              {hasPermission(`${module}.delete`)&&<button title="Delete" onClick={()=>remove(row)} className="rounded-lg p-2 text-red-500 hover:bg-red-50"><Trash2 size={16}/></button>}
            </div></td>
          </tr>)}</tbody>
        </table>
      </div>}
    </div>
    {editing&&<Modal title={`${editing.id?'Edit':'Add'} ${cfg.singular}`} onClose={()=>setEditing(null)}>
      <ErrorText error={error}/>
      <form onSubmit={save}>
        <div className="grid gap-4 md:grid-cols-2">{fields.map(field=><Field key={field[0]} field={field} value={form[field[0]]} onChange={(key,value)=>setForm({...form,[key]:value})}/>)}</div>
        <div className="mt-6 flex justify-end gap-2">
          <button type="button" className="btn-secondary" onClick={()=>setEditing(null)}>Cancel</button>
          <button disabled={busy} className="btn-primary">{busy?'Saving…':'Save record'}</button>
        </div>
      </form>
    </Modal>}
  </div>
}
