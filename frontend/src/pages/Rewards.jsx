import {useCallback,useEffect,useState} from 'react'
import {Award,Gift,Star} from 'lucide-react'
import api from '../api'
import {Badge,Loading} from '../components/UI'
import {useAuth} from '../AuthContext'

export default function Rewards(){
  const [data,setData]=useState(),[requests,setRequests]=useState([])
  const {hasPermission}=useAuth()
  const load=useCallback(()=>{api.get('/rewards').then(response=>setData(response.data));if(hasPermission('rewards.update'))api.get('/reward-redemptions').then(response=>setRequests(response.data.data))},[hasPermission])
  useEffect(()=>{load()},[load])
  const redeem=async reward=>{
    if(!confirm(`Redeem ${reward.name} for ${reward.points_required} points?`))return
    await api.post(`/rewards/${reward.id}/redeem`)
    load()
  }
  const decide=async(item,status)=>{await api.patch(`/reward-redemptions/${item.id}/decide`,{status});load()}
  if(!data)return <Loading/>
  return <div>
    <div className="mb-6"><p className="text-xs font-semibold uppercase tracking-wider text-[#a67c2b]">Staff recognition</p><h2 className="page-title">My Rewards</h2><p className="mt-1 text-sm text-[#81796d]">Complete assigned tasks, earn points, and request rewards.</p></div>
    <div className="card mb-6 flex items-center gap-5 bg-[#29261f] p-6 text-white"><Award size={36} className="text-[#dfc477]"/><div><div className="text-3xl font-bold">{data.points.toLocaleString('en-IN')}</div><div className="text-xs uppercase tracking-widest text-white/50">Available reward points</div></div></div>
    <div className="mb-7 grid gap-4 md:grid-cols-3">{data.catalog.map(reward=><div className="card p-5" key={reward.id}><Gift className="text-[#b58b36]"/><h3 className="mt-4 font-serif text-xl font-semibold">{reward.name}</h3><p className="mt-1 min-h-10 text-xs text-[#81796d]">{reward.description}</p><button disabled={data.points<reward.points_required} onClick={()=>redeem(reward)} className="btn-primary mt-4 w-full"><Star size={14}/>{reward.points_required} points</button></div>)}</div>
    <div className="card overflow-hidden"><h3 className="border-b border-[#eee8dc] px-5 py-4 font-serif text-xl font-semibold">Redemption history</h3>{data.redemptions.length?data.redemptions.map(item=><div className="flex items-center justify-between border-b border-[#eee9df] px-5 py-4" key={item.id}><div><b className="text-sm">{item.reward?.name}</b><p className="text-xs text-[#81796d]">{item.points} points · {new Date(item.created_at).toLocaleDateString('en-IN')}</p></div><Badge>{item.status}</Badge></div>):<div className="p-8 text-center text-sm text-[#81796d]">No reward requests yet.</div>}</div>
    {hasPermission('rewards.update')&&<div className="card mt-6 overflow-hidden"><h3 className="border-b border-[#eee8dc] px-5 py-4 font-serif text-xl font-semibold">Team reward approvals</h3>{requests.map(item=><div className="flex flex-wrap items-center gap-3 border-b border-[#eee9df] px-5 py-4" key={item.id}><div className="flex-1"><b>{item.user?.name} · {item.reward?.name}</b><p className="text-xs text-[#81796d]">{item.points} points</p></div><Badge>{item.status}</Badge>{item.status==='Requested'&&<><button className="btn-secondary" onClick={()=>decide(item,'Rejected')}>Reject</button><button className="btn-primary" onClick={()=>decide(item,'Approved')}>Approve</button></>}</div>)}</div>}
  </div>
}
