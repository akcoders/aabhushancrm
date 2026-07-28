import {useEffect,useState} from 'react'
import {Eye,Plus} from 'lucide-react'
import {Link} from 'react-router-dom'
import api from '../api'
import {Badge,Field,Loading,Modal} from '../components/UI'

export default function Staff(){
  const [rows,setRows]=useState(),[settings,setSettings]=useState(),[show,setShow]=useState(false)
  const [form,setForm]=useState({name:'',email:'',password:'Password@123',role_id:'',reporting_manager_id:'',branch_id:''})
  const load=()=>api.get('/staff').then(response=>setRows(response.data.data))
  useEffect(()=>{load();api.get('/settings').then(response=>setSettings(response.data))},[])
  const save=async event=>{event.preventDefault();await api.post('/staff',form);setShow(false);load()}
  const selectedRole=settings?.roles.find(role=>String(role.id)===String(form.role_id))
  const managers=(rows||[]).filter(staff=>staff.is_active&&staff.role?.hierarchy_level<selectedRole?.hierarchy_level)
  return <div>
    <div className="mb-6 flex items-end justify-between">
      <div><p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">Your team</p><h2 className="page-title mt-1">Staff Management</h2><p className="mt-1 text-sm text-[#81796d]">Open a staff profile to review actions, timeliness, sales and rewards.</p></div>
      <button onClick={()=>setShow(true)} className="btn-primary"><Plus size={17}/>Add staff</button>
    </div>
    <div className="card overflow-hidden">{!rows?<Loading/>:rows.map(staff=><div className="flex items-center gap-4 border-b border-[#eee9df] px-6 py-4" key={staff.id}>
      <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#29261f] font-serif text-lg text-[#dbbd70]">{staff.name[0]}</div>
      <div className="flex-1"><b>{staff.name}</b><p className="text-xs text-[#898174]">{staff.email} · {staff.branch?.name||'No branch'} · Reports to {staff.reporting_manager?.name||'Top level'}</p></div>
      <Badge>{staff.role?.name}</Badge><Badge>{staff.is_active?'Active':'Inactive'}</Badge>
      <Link to={`/staff/${staff.id}`} className="btn-secondary"><Eye size={15}/>Performance</Link>
    </div>)}</div>
    {show&&<Modal title="Add staff member" onClose={()=>setShow(false)}><form onSubmit={save} className="grid gap-4 md:grid-cols-2">
      <Field field={['name','Full name','text',true]} value={form.name} onChange={(key,value)=>setForm({...form,[key]:value})}/>
      <Field field={['email','Email','email',true]} value={form.email} onChange={(key,value)=>setForm({...form,[key]:value})}/>
      <Field field={['password','Temporary password','password',true]} value={form.password} onChange={(key,value)=>setForm({...form,[key]:value})}/>
      <label><span className="label">Role *</span><select required className="field" value={form.role_id} onChange={event=>setForm({...form,role_id:event.target.value,reporting_manager_id:''})}><option value="">Select role</option>{settings?.roles.sort((a,b)=>a.hierarchy_level-b.hierarchy_level).map(role=><option key={role.id} value={role.id}>{role.name} · Level {role.hierarchy_level}</option>)}</select></label>
      <label><span className="label">Reporting manager {selectedRole?.hierarchy_level>1&&'*'}</span><select required={selectedRole?.hierarchy_level>1} disabled={!selectedRole} className="field" value={form.reporting_manager_id} onChange={event=>setForm({...form,reporting_manager_id:event.target.value})}><option value="">{selectedRole?.hierarchy_level===1?'Top-level role—no manager':'Select higher-level manager'}</option>{managers.map(manager=><option key={manager.id} value={manager.id}>{manager.name} · {manager.role?.name}</option>)}</select></label>
      <label><span className="label">Branch</span><select className="field" value={form.branch_id} onChange={event=>setForm({...form,branch_id:event.target.value})}><option value="">Select branch</option>{settings?.branches.map(branch=><option key={branch.id} value={branch.id}>{branch.name}</option>)}</select></label>
      <div className="flex justify-end md:col-span-2"><button className="btn-primary">Create staff account</button></div>
    </form></Modal>}
  </div>
}
