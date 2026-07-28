import {useEffect,useMemo,useState} from 'react'
import {Building2,FileText,Landmark,Pencil,Plus,Save,ShieldCheck,Trash2} from 'lucide-react'
import api from '../api'
import {ErrorText,Loading,Modal} from '../components/UI'

export default function Settings(){
  const [data,setData]=useState(),[form,setForm]=useState({}),[roleForm,setRoleForm]=useState(),[branchForm,setBranchForm]=useState(),[error,setError]=useState()
  const load=()=>api.get('/settings').then(response=>{setData(response.data);const values={};Object.values(response.data.settings).flat().forEach(setting=>values[setting.key]=setting.value);setForm(values)})
  useEffect(()=>{load()},[])
  const permissionGroups=useMemo(()=>(data?.permissions||[]).reduce((groups,permission)=>{(groups[permission.module]??=[]).push(permission);return groups},{}),[data])
  if(!data)return <Loading/>
  const save=async()=>{await api.put('/settings',{settings:form});alert('Settings saved')}
  const field=(key,label)=><label><span className="label">{label}</span><input className="field" value={form[key]??''} onChange={event=>setForm({...form,[key]:event.target.value})}/></label>
  const openRole=role=>{setError();setRoleForm(role?{id:role.id,name:role.name,hierarchy_level:role.hierarchy_level,permissions:role.permissions.map(permission=>permission.id)}:{name:'',hierarchy_level:4,permissions:[]})}
  const togglePermission=id=>setRoleForm(current=>({...current,permissions:current.permissions.includes(id)?current.permissions.filter(value=>value!==id):[...current.permissions,id]}))
  const toggleModule=permissions=>setRoleForm(current=>{const ids=permissions.map(permission=>permission.id);const all=ids.every(id=>current.permissions.includes(id));return {...current,permissions:all?current.permissions.filter(id=>!ids.includes(id)):[...new Set([...current.permissions,...ids])]}})
  const saveRole=async event=>{event.preventDefault();setError();try{roleForm.id?await api.put(`/settings/roles/${roleForm.id}`,roleForm):await api.post('/settings/roles',roleForm);setRoleForm();load()}catch(exception){setError(exception)}}
  const openBranch=branch=>{setError();setBranchForm(branch?{...branch}:{name:'',code:'',phone:'',email:'',city:'',address:'',is_active:true})}
  const saveBranch=async event=>{event.preventDefault();setError();try{branchForm.id?await api.put(`/settings/branches/${branchForm.id}`,branchForm):await api.post('/settings/branches',branchForm);setBranchForm();load()}catch(exception){setError(exception)}}
  const deleteBranch=async branch=>{if(!confirm(`Delete branch “${branch.name}”?`))return;setError();try{await api.delete(`/settings/branches/${branch.id}`);load()}catch(exception){setBranchForm({...branch});setError(exception)}}
  return <div>
    <div className="mb-6 flex items-end justify-between"><div><p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">Administration</p><h2 className="page-title mt-1">CRM Settings</h2></div><button className="btn-primary" onClick={save}><Save size={16}/>Save changes</button></div>
    <div className="grid gap-5 xl:grid-cols-2">
      <section className="card p-6"><h3 className="mb-5 flex items-center gap-2 font-serif text-xl font-semibold"><Building2 className="text-[#b58b36]"/>Company profile</h3><div className="grid gap-4 md:grid-cols-2">{field('company.name','Company name')}{field('company.gst','GST number')}{field('company.currency','Currency')}{field('tax.gst_rate','GST rate (%)')}</div></section>
      <section className="card p-6"><h3 className="mb-5 flex items-center gap-2 font-serif text-xl font-semibold"><FileText className="text-[#b58b36]"/>Invoice & loyalty</h3><div className="grid gap-4 md:grid-cols-2">{field('invoice.prefix','Invoice prefix')}{field('loyalty.points_per_1000','Points per ₹1,000')}</div></section>
      <section className="card p-6"><div className="mb-4 flex items-center justify-between"><h3 className="flex items-center gap-2 font-serif text-xl font-semibold"><Landmark className="text-[#b58b36]"/>Branches</h3><button className="btn-secondary" onClick={()=>openBranch()}><Plus size={15}/>Add branch</button></div>{data.branches.map(branch=><div key={branch.id} className="mb-3 flex items-center gap-3 rounded-xl border border-[#e7e0d3] p-4"><div className="flex-1"><b>{branch.name}</b><p className="mt-1 text-xs text-[#827a6e]">{branch.code} · {branch.city||'No city'} · {branch.phone||'No phone'}</p><p className={`mt-1 text-xs font-semibold ${branch.is_active?'text-emerald-700':'text-red-600'}`}>{branch.is_active?'Active':'Inactive'}</p></div><button className="rounded-lg p-2 hover:bg-[#f4ecda]" onClick={()=>openBranch(branch)} title="Edit branch"><Pencil size={16}/></button><button className="rounded-lg p-2 text-red-600 hover:bg-red-50" onClick={()=>deleteBranch(branch)} title="Delete branch"><Trash2 size={16}/></button></div>)}</section>
      <section className="card p-6">
        <div className="mb-4 flex items-center justify-between"><div><h3 className="flex items-center gap-2 font-serif text-xl font-semibold"><ShieldCheck className="text-[#b58b36]"/>Roles & permissions</h3><p className="mt-1 text-xs text-[#8b8376]">Level 1 is the highest authority. Managers must have a lower level number than their reports.</p></div><button className="btn-secondary" onClick={()=>openRole()}><Plus size={15}/>Create role</button></div>
        {data.roles.sort((a,b)=>a.hierarchy_level-b.hierarchy_level).map(role=><button type="button" onClick={()=>openRole(role)} key={role.id} className="flex w-full items-center justify-between border-b border-[#eee9df] py-3 text-left hover:bg-[#faf8f3]"><div><b className="text-sm">{role.name}</b><p className="text-xs text-[#8b8376]">{role.permissions.length} permissions · Hierarchy level {role.hierarchy_level}</p></div><span className="rounded-full bg-[#f4ecda] px-3 py-1 text-xs text-[#98712a]">{role.slug}</span></button>)}
      </section>
    </div>
    {roleForm&&<Modal wide title={roleForm.id?'Edit role & permissions':'Create role'} onClose={()=>setRoleForm()}><form onSubmit={saveRole}>
      <ErrorText error={error}/><div className="mb-6 grid gap-4 md:grid-cols-2"><label><span className="label">Role name *</span><input required className="field" value={roleForm.name} onChange={event=>setRoleForm({...roleForm,name:event.target.value})}/></label><label><span className="label">Hierarchy level *</span><input required min="1" max="100" type="number" className="field" value={roleForm.hierarchy_level} onChange={event=>setRoleForm({...roleForm,hierarchy_level:Number(event.target.value)})}/></label></div>
      <h4 className="mb-3 font-semibold">Module permissions</h4><div className="grid gap-4 md:grid-cols-2">{Object.entries(permissionGroups).map(([module,permissions])=><div className="rounded-xl border border-[#e7e0d3] p-4" key={module}><label className="mb-3 flex items-center gap-2 font-semibold capitalize"><input type="checkbox" checked={permissions.every(permission=>roleForm.permissions.includes(permission.id))} onChange={()=>toggleModule(permissions)}/>{module.replaceAll('-',' ')}</label><div className="grid grid-cols-2 gap-2">{permissions.map(permission=><label className="flex items-center gap-2 text-xs" key={permission.id}><input type="checkbox" checked={roleForm.permissions.includes(permission.id)} onChange={()=>togglePermission(permission.id)}/>{permission.slug.split('.').at(-1)}</label>)}</div></div>)}</div>
      <div className="mt-6 flex justify-end"><button className="btn-primary"><Save size={15}/>Save role</button></div>
    </form></Modal>}
    {branchForm&&<Modal title={branchForm.id?'Edit branch':'Add branch'} onClose={()=>setBranchForm()}><form onSubmit={saveBranch} className="grid gap-4 md:grid-cols-2">
      <div className="md:col-span-2"><ErrorText error={error}/></div>
      <label><span className="label">Branch name *</span><input required className="field" value={branchForm.name} onChange={event=>setBranchForm({...branchForm,name:event.target.value})}/></label>
      <label><span className="label">Branch code *</span><input required className="field" value={branchForm.code} onChange={event=>setBranchForm({...branchForm,code:event.target.value.toUpperCase()})}/></label>
      <label><span className="label">Phone</span><input className="field" value={branchForm.phone||''} onChange={event=>setBranchForm({...branchForm,phone:event.target.value})}/></label>
      <label><span className="label">Email</span><input type="email" className="field" value={branchForm.email||''} onChange={event=>setBranchForm({...branchForm,email:event.target.value})}/></label>
      <label><span className="label">City</span><input className="field" value={branchForm.city||''} onChange={event=>setBranchForm({...branchForm,city:event.target.value})}/></label>
      <label><span className="label">Status</span><select className="field" value={branchForm.is_active?'1':'0'} onChange={event=>setBranchForm({...branchForm,is_active:event.target.value==='1'})}><option value="1">Active</option><option value="0">Inactive</option></select></label>
      <label className="md:col-span-2"><span className="label">Address</span><textarea rows="3" className="field" value={branchForm.address||''} onChange={event=>setBranchForm({...branchForm,address:event.target.value})}/></label>
      <div className="flex justify-end md:col-span-2"><button className="btn-primary"><Save size={15}/>Save branch</button></div>
    </form></Modal>}
  </div>
}
