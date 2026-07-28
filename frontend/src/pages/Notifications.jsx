import {useEffect,useState} from 'react'
import {Bell,CheckCheck} from 'lucide-react'
import api from '../api'
import {Badge,Loading} from '../components/UI'

export default function Notifications(){
  const [rows,setRows]=useState()
  const load=()=>api.get('/notifications').then(response=>setRows(response.data.data))
  useEffect(()=>{load()},[])
  const readAll=async()=>{await api.post('/notifications/read-all');load()}
  const enableBrowserNotifications=async()=>{
    if(!('Notification' in window))return alert('This browser does not support notifications.')
    const permission=await Notification.requestPermission()
    if(permission==='granted')new Notification('Kalasha CRM notifications enabled',{body:'Daily updates will appear while the application is running.',icon:'/crm/favicon.png'})
  }
  if(!rows)return <Loading/>
  return <div><div className="mb-6 flex flex-wrap items-end justify-between gap-2"><div><p className="text-xs font-semibold uppercase tracking-wider text-[#a67c2b]">Daily updates</p><h2 className="page-title">Notifications</h2></div><div className="flex gap-2"><button className="btn-secondary" onClick={enableBrowserNotifications}><Bell size={16}/>Enable device alerts</button><button className="btn-secondary" onClick={readAll}><CheckCheck size={16}/>Mark all read</button></div></div><div className="card overflow-hidden">{rows.length?rows.map(item=><button key={item.id} onClick={async()=>{await api.post(`/notifications/${item.id}/read`);load()}} className={`flex w-full gap-4 border-b border-[#eee9df] p-5 text-left ${item.read_at?'bg-white':'bg-[#fffaf0]'}`}><Bell size={18} className="mt-1 text-[#b58b36]"/><div className="flex-1"><b className="text-sm">{item.title}</b><p className="mt-1 text-sm text-[#6f685d]">{item.message}</p><p className="mt-1 text-[10px] text-[#9a9285]">{new Date(item.created_at).toLocaleString('en-IN')}</p></div>{!item.read_at&&<Badge>New</Badge>}</button>):<div className="p-12 text-center text-sm text-[#81796d]">No notifications yet.</div>}</div></div>
}
