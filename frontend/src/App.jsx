import { useEffect, useState } from 'react'
import api from './api/client'
import './App.css'

function App() {
  const [status, setStatus] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    api.get('/api/health')
      .then((response) => {
        setStatus(response.data.status)
        setError(null)
      })
      .catch(() => {
        setStatus(null)
        setError(true)
      })
  }, [])

  return (
    <div className="min-h-screen bg-gray-900 text-white flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-4xl font-bold mb-4">PulseDesk</h1>
        <p className="text-gray-400 mb-6">Next-generation support desk platform</p>
        <div className="text-lg">
          {error ? <span>API: ❌ unreachable</span> : <span>API: ✅ {status ?? 'checking...'}</span>}
        </div>
      </div>
    </div>
  )
}

export default App
