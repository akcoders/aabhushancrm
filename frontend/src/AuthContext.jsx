/* eslint-disable react-refresh/only-export-components */
import {createContext,useContext,useState} from 'react'
import api from './api'
const AuthContext=createContext(null)
export function AuthProvider({children}){const [user,setUser]=useState(()=>JSON.parse(localStorage.getItem('crm_user')||'null'));const login=async(email,password)=>{const {data}=await api.post('/auth/login',{email,password});localStorage.setItem('crm_token',data.token);localStorage.setItem('crm_user',JSON.stringify(data.user));setUser(data.user)};const logout=async()=>{try{await api.post('/auth/logout')}finally{localStorage.clear();setUser(null)}};const hasPermission=permission=>user?.role?.slug==='super-admin'||user?.role?.permissions?.some(item=>item.slug===permission);const value={user,login,logout,hasPermission};return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>}
export const useAuth=()=>useContext(AuthContext)
