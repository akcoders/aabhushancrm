import axios from 'axios'
const api=axios.create({baseURL:import.meta.env.VITE_API_URL||'http://127.0.0.1:8000/api',headers:{Accept:'application/json'}})
api.interceptors.request.use(c=>{const t=localStorage.getItem('crm_token');if(t)c.headers.Authorization=`Bearer ${t}`;return c})
api.interceptors.response.use(r=>r,e=>{if(e.response?.status===401){localStorage.removeItem('crm_token');localStorage.removeItem('crm_user');if(!location.pathname.startsWith('/login'))location.href='/login'}return Promise.reject(e)})
export default api
