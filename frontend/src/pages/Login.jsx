import { useState } from 'react'
import api from '../api/client'
import TicketList from './TicketList'

export default function Login({ onLogin, onSwitch }) {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function submit(e) {
    e.preventDefault()
    setLoading(true)
    setError('')
    try {
      const { data } = await api.post('/api/login', { email, password })
      localStorage.setItem('token', data.token)
      onLogin(data.token, data.user)
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed')
    } finally { setLoading(false) }
  }

  return (
    <div className="min-h-screen flex items-center justify-center">
      <form onSubmit={submit} className="bg-gray-800 p-8 rounded shadow w-full max-w-md space-y-4">
        <h2 className="text-2xl font-bold text-center">Sign in</h2>
        {error && <div className="bg-red-900/50 p-3 rounded text-red-200 text-sm">{error}</div>}
        <div>
          <label className="block text-sm mb-1">Email</label>
          <input type="email" value={email} onChange={e=>setEmail(e.target.value)} required className="w-full bg-gray-700 rounded p-2 text-gray-100" />
        </div>
        <div>
          <label className="block text-sm mb-1">Password</label>
          <input type="password" value={password} onChange={e=>setPassword(e.target.value)} required className="w-full bg-gray-700 rounded p-2 text-gray-100" />
        </div>
        <button disabled={loading} className="w-full bg-indigo-600 py-2 rounded hover:bg-indigo-500 text-white">{loading ? '...' : 'Sign in'}</button>
        <p className="text-center text-sm text-gray-400">Try <code className="text-indigo-300">admin@acme.test</code> / <code className="text-indigo-300">password</code></p>
        <p className="text-center text-sm text-gray-400">No account? <button type="button" onClick={onSwitch} className="text-indigo-400 underline">Register</button></p>
      </form>
    </div>
  )
}
