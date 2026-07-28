import {useEffect,useState} from 'react'
import {ArrowLeft,Award,CheckCircle2,Clock3,IndianRupee,ListChecks,Target,TriangleAlert} from 'lucide-react'
import {Link,useParams} from 'react-router-dom'
import api from '../api'
import {Badge,Loading} from '../components/UI'

const number=value=>Number(value||0).toLocaleString('en-IN')
const date=value=>value?new Date(value).toLocaleString('en-IN',{dateStyle:'medium',timeStyle:'short'}):'—'

export default function StaffPerformance(){
  const {id}=useParams()
  const [data,setData]=useState()
  useEffect(()=>{api.get(`/staff/${id}/performance`).then(response=>setData(response.data))},[id])
  if(!data)return <Loading/>
  const {staff,summary,tasks,followups,redemptions}=data
  const cards=[
    ['Assigned actions',summary.assigned_actions,ListChecks],
    ['Pending actions',summary.pending_actions,Clock3],
    ['Overdue actions',summary.overdue_actions,TriangleAlert],
    ['Completed actions',summary.completed_actions,CheckCircle2],
    ['Taken on time',summary.timely_actions,Target],
    ['Timely completion',`${summary.timely_rate}%`,Clock3],
    ['Points earned',summary.reward_points_earned,Award],
    ['Available points',summary.reward_points_available,Award],
  ]
  return <div>
    <Link to="/staff" className="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-[#8c6b2b]"><ArrowLeft size={16}/>Back to staff</Link>
    <div className="card mb-6 flex flex-wrap items-center gap-5 p-6">
      <div className="flex h-16 w-16 items-center justify-center rounded-full bg-[#29261f] font-serif text-2xl text-[#dbbd70]">{staff.name[0]}</div>
      <div className="flex-1"><p className="text-xs font-semibold uppercase tracking-widest text-[#aa8131]">Staff performance profile</p><h2 className="page-title">{staff.name}</h2><p className="text-sm text-[#81796d]">{staff.email} · {staff.role?.name} · {staff.branch?.name||'No branch'} · Reports to {staff.reporting_manager?.name||'Top level'}</p></div>
      <Badge>{staff.is_active?'Active':'Inactive'}</Badge>
    </div>
    <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{cards.map(([label,value,Icon])=><div className="card p-5" key={label}><div className="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#f6edd8] text-[#a67c2b]"><Icon size={19}/></div><div className="text-2xl font-bold">{typeof value==='number'?number(value):value}</div><div className="text-xs text-[#81796d]">{label}</div></div>)}</div>
    <div className="mb-6 grid gap-4 md:grid-cols-4">
      <div className="card p-5"><p className="label">Assigned leads</p><b className="text-2xl">{number(summary.assigned_leads)}</b></div>
      <div className="card p-5"><p className="label">Converted leads</p><b className="text-2xl">{number(summary.converted_leads)}</b></div>
      <div className="card p-5"><p className="label">Sales completed</p><b className="text-2xl">{number(summary.sales_count)}</b></div>
      <div className="card p-5"><p className="label">Sales revenue</p><b className="flex items-center text-2xl"><IndianRupee size={20}/>{number(summary.sales_revenue)}</b></div>
    </div>
    <section className="card mb-6 overflow-x-auto"><h3 className="border-b border-[#eee8dc] px-5 py-4 font-serif text-xl font-semibold">Task action history</h3><table className="w-full text-left"><thead><tr className="text-xs uppercase tracking-wide text-[#8d8578]"><th className="px-5 py-3">Action</th><th className="px-5 py-3">Due</th><th className="px-5 py-3">Completed</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Reward</th></tr></thead><tbody>{tasks.map(task=><tr className="border-t border-[#eee9df]" key={task.id}><td className="px-5 py-4"><b className="text-sm">{task.title}</b><p className="text-xs text-[#81796d]">{task.customer?.name||task.lead?.name||task.task_type||'General task'}</p></td><td className="px-5 py-4 text-sm">{date(task.due_at)}</td><td className="px-5 py-4 text-sm">{date(task.completed_at)}</td><td className="px-5 py-4"><Badge>{task.status}</Badge></td><td className="px-5 py-4 text-sm">{task.rewarded_at?`${number(task.reward_points)} earned`:`${number(task.reward_points)} available`}</td></tr>)}</tbody></table>{!tasks.length&&<p className="p-8 text-center text-sm text-[#81796d]">No tasks assigned.</p>}</section>
    <section className="card mb-6 overflow-x-auto"><h3 className="border-b border-[#eee8dc] px-5 py-4 font-serif text-xl font-semibold">Follow-up action history</h3><table className="w-full text-left"><thead><tr className="text-xs uppercase tracking-wide text-[#8d8578]"><th className="px-5 py-3">Customer/lead</th><th className="px-5 py-3">Type</th><th className="px-5 py-3">Scheduled</th><th className="px-5 py-3">Last action</th><th className="px-5 py-3">Status</th></tr></thead><tbody>{followups.map(item=><tr className="border-t border-[#eee9df]" key={item.id}><td className="px-5 py-4 font-semibold">{item.customer?.name||item.lead?.name||'—'}</td><td className="px-5 py-4 text-sm">{item.type}</td><td className="px-5 py-4 text-sm">{date(item.scheduled_at)}</td><td className="px-5 py-4 text-sm">{date(item.updated_at)}</td><td className="px-5 py-4"><Badge>{item.status}</Badge></td></tr>)}</tbody></table>{!followups.length&&<p className="p-8 text-center text-sm text-[#81796d]">No follow-ups assigned.</p>}</section>
    <section className="card overflow-hidden"><h3 className="border-b border-[#eee8dc] px-5 py-4 font-serif text-xl font-semibold">Reward redemption history</h3>{redemptions.length?redemptions.map(item=><div className="flex items-center justify-between border-b border-[#eee9df] px-5 py-4" key={item.id}><div><b>{item.reward?.name}</b><p className="text-xs text-[#81796d]">{number(item.points)} points · {date(item.created_at)}</p></div><Badge>{item.status}</Badge></div>):<p className="p-8 text-center text-sm text-[#81796d]">No rewards redeemed.</p>}</section>
  </div>
}
