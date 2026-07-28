import {useEffect,useState} from 'react'
import api from './api'

export default function useCompanyName(){
  const [companyName,setCompanyName]=useState('Your Company')

  useEffect(()=>{
    api.get('/settings').then(response=>{
      const settings=Object.values(response.data.settings||{}).flat()
      const configured=settings.find(setting=>setting.key==='company.name')?.value
      if(configured)setCompanyName(String(configured))
    })
  },[])

  return companyName
}
