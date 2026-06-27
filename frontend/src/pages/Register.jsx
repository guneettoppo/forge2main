import { useState } from 'react'
import api from '../api/client'

export default function Register({ onLogin, onSwitch }) {
  const [form, setForm] = useState({ name:'', email:'', password:'', org_name:'', org_slug:'' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function submit(e) {
    e.preventDefault()
    setLoading(true); setError('')
    try {
      const { data } = await api.post('/api/register', form)
      onLogin(data.token, data.user)
    } catch (err) {
      setError(err.response?.data?.message || 'Register failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center">
      <form onSubmit={submit} className="bg-gray-800 p-8 rounded shadow w-full max-w-md space-y-4">
        <h2 className="text-2xl font-bold text-center">Create org + admin</h2>
        {error && <div className="bg-red-900/50 p-3 rounded text-red-200 text-sm">{error}</div>}
        <input placeholder="Your name" value={form.name} onChange={e=>setForm({...form,name:e.target.value})} className="w-full bg-gray-700 rounded p-2" />
        <input placeholder="Email" type="email" value={form.email} onChange={e=>setForm({...form,email:e.target.value})} className="w-full bg-gray-700 rounded p-2" />
        <input placeholder="Password" type="password" value={form.password} onChange={e=>setForm({...form,password:e.target.value})} className="w-full bg-gray-700 rounded p-2" />
        <input placeholder="Organization name" value={form.org_name} onChange={e=>setForm({...form,org_name:e.target.value})} className="w-full bg-gray-700 rounded p-2" />
        <input placeholder="Org slug (optional)" value={form.org_slug} onChange={e=>setForm({...form,org_slug:e.target.value})} className="w-full bg-gray-700 rounded p-2" />
        <button disabled={loading} className="w-full bg-indigo-600 py-2 rounded text-white">{loading ? '...' : 'Create & Sign In'}</button>
        <p className="text-center text-sm text-gray-400">Already registered? <button type="button" onClick={onSwitch} className="text-indigo-400 underline">Sign in</button></p>
      </form>
    </div>
  )
}
