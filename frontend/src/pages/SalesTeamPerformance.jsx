import {useEffect,useMemo,useState} from 'react'
import {Award,CheckCircle2,Clock3,Target,Users} from 'lucide-react'
import api from '../api'
import {Badge,Loading} from '../components/UI'
import {money} from '../config'

export default function SalesTeamPerformance(){
  const [team,setTeam]=useState()

  useEffect(()=>{
    api.get('/dashboard').then(response=>setTeam(response.data.staff_performance||[]))
  },[])

  const summary=useMemo(()=>{
    if(!team)return null
    return {
      leader:team[0],
      assigned:team.reduce((total,person)=>total+person.assigned_leads_count,0),
      converted:team.reduce((total,person)=>total+person.converted_leads_count,0),
      overdue:team.reduce((total,person)=>total+person.overdue_followups_count,0),
    }
  },[team])

  if(!team)return <Loading/>

  return <section className="mb-7">
    <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
      <div>
        <p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">Sales team accountability</p>
        <h2 className="font-serif text-2xl font-semibold">Lead conversion and follow-up performance</h2>
        <p className="mt-1 text-sm text-[#857d70]">Compare ownership, conversion quality, pending work and generated revenue.</p>
      </div>
      {summary?.leader&&<div className="flex items-center gap-2 rounded-xl border border-[#ead9ad] bg-[#fffaf0] px-4 py-2 text-sm">
        <Award size={17} className="text-[#a67c2b]"/>
        <span>Top conversion: <b>{summary.leader.name}</b> · {summary.leader.conversion_rate}%</span>
      </div>}
    </div>

    <div className="mb-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <Metric icon={Users} label="Assigned leads" value={summary?.assigned||0}/>
      <Metric icon={CheckCircle2} label="Converted leads" value={summary?.converted||0}/>
      <Metric icon={Target} label="Team conversion" value={summary?.assigned?`${Math.round(summary.converted/summary.assigned*1000)/10}%`:'0%'}/>
      <Metric icon={Clock3} label="Overdue follow-ups" value={summary?.overdue||0} danger={summary?.overdue>0}/>
    </div>

    <div className="card overflow-hidden">
      {!team.length?<div className="p-8 text-center text-sm text-[#857d70]">Add active Sales Managers or Sales Executives to begin tracking performance.</div>:<div className="overflow-x-auto">
        <table className="w-full min-w-[980px] text-left">
          <thead><tr className="border-b border-[#ece6da] bg-[#fcfbf8]">
            {['Rank / salesperson','Assigned','Open','Converted','Conversion','Pending follow-ups','Overdue','Completed follow-ups','Sales revenue'].map(label=><th key={label} className="px-4 py-3 text-[10px] font-bold uppercase tracking-[.1em] text-[#888073]">{label}</th>)}
          </tr></thead>
          <tbody>{team.map((person,index)=><tr key={person.id} className="border-b border-[#f0ece4] last:border-0 hover:bg-[#fcfaf5]">
            <td className="px-4 py-4">
              <div className="flex items-center gap-3">
                <div className={`flex h-9 w-9 items-center justify-center rounded-full font-semibold ${index===0?'bg-[#b58b36] text-white':'bg-[#f1eadb] text-[#8f6a25]'}`}>{index+1}</div>
                <div><b className="text-sm">{person.name}</b><p className="text-xs text-[#91897c]">{person.role?.name}</p></div>
              </div>
            </td>
            <td className="px-4 py-4 font-semibold">{person.assigned_leads_count}</td>
            <td className="px-4 py-4">{person.open_leads_count}</td>
            <td className="px-4 py-4 font-semibold text-emerald-700">{person.converted_leads_count}</td>
            <td className="px-4 py-4"><Badge>{person.conversion_rate}%</Badge></td>
            <td className="px-4 py-4">{person.pending_followups_count}</td>
            <td className="px-4 py-4"><span className={person.overdue_followups_count?'font-bold text-red-600':'text-[#81796d]'}>{person.overdue_followups_count}</span></td>
            <td className="px-4 py-4">{person.completed_followups_count}</td>
            <td className="px-4 py-4 font-semibold">{money(person.sales_revenue)}</td>
          </tr>)}</tbody>
        </table>
      </div>}
    </div>
  </section>
}

function Metric({icon:Icon,label,value,danger=false}){
  return <div className="card flex items-center gap-4 p-4">
    <span className={`flex h-10 w-10 items-center justify-center rounded-xl ${danger?'bg-red-50 text-red-600':'bg-[#f7f1e4] text-[#aa8131]'}`}><Icon size={18}/></span>
    <div><b className="text-xl">{value}</b><p className="text-xs text-[#81796d]">{label}</p></div>
  </div>
}
