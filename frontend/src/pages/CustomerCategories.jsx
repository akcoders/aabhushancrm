import {useEffect,useState} from 'react'
import {Plus,RefreshCw,Trash2} from 'lucide-react'
import api from '../api'
import {Badge,Field,Loading,Modal} from '../components/UI'

const blank={name:'',category:'Premium',minimum_purchase:0,maximum_purchase:'',priority:1,is_active:true}
export default function CustomerCategories(){
  const [rows,setRows]=useState(),[editing,setEditing]=useState(),[form,setForm]=useState(blank)
  const load=()=>api.get('/customer-category-rules').then(response=>setRows(response.data))
  useEffect(()=>{load()},[])
  const save=async event=>{event.preventDefault();editing?.id?await api.put(`/customer-category-rules/${editing.id}`,form):await api.post('/customer-category-rules',form);setEditing();setForm(blank);load()}
  if(!rows)return <Loading/>
  return <div><div className="mb-6 flex flex-wrap items-end justify-between gap-3"><div><p className="text-xs font-semibold uppercase tracking-wider text-[#a67c2b]">Automatic segmentation</p><h2 className="page-title">Customer Category Rules</h2><p className="mt-1 text-sm text-[#81796d]">Customers are categorized from lifetime purchase value unless manually overridden.</p></div><div className="flex gap-2"><button className="btn-secondary" onClick={async()=>{await api.post('/customers/recategorize');alert('Categories recalculated')}}><RefreshCw size={15}/>Recalculate all</button><button className="btn-primary" onClick={()=>{setEditing({});setForm(blank)}}><Plus size={15}/>Add rule</button></div></div><div className="card overflow-hidden">{rows.map(rule=><div className="flex items-center gap-4 border-b border-[#eee9df] px-5 py-4" key={rule.id}><button className="flex-1 text-left" onClick={()=>{setEditing(rule);setForm(rule)}}><b>{rule.name}</b><p className="text-xs text-[#81796d]">₹{Number(rule.minimum_purchase).toLocaleString('en-IN')} to {rule.maximum_purchase?`₹${Number(rule.maximum_purchase).toLocaleString('en-IN')}`:'and above'}</p></button><Badge>{rule.category}</Badge><button className="text-red-500" onClick={async()=>{if(confirm('Delete this category rule?')){await api.delete(`/customer-category-rules/${rule.id}`);load()}}}><Trash2 size={16}/></button></div>)}</div>{editing&&<Modal title={`${editing.id?'Edit':'Add'} category rule`} onClose={()=>setEditing()}><form onSubmit={save} className="grid gap-4 md:grid-cols-2">{[['name','Rule name','text',true],['category','Category','select',true,['Normal','Premium','VIP','HNI']],['minimum_purchase','Minimum purchase','number',true],['maximum_purchase','Maximum purchase','number'],['priority','Priority','number',true]].map(field=><Field key={field[0]} field={field} value={form[field[0]]} onChange={(key,value)=>setForm({...form,[key]:value})}/>) }<button className="btn-primary md:col-span-2">Save rule</button></form></Modal>}</div>
}
