import { useState, useEffect } from 'react'
import api from './api/client'
import Login from './pages/Login'
import Register from './pages/Register'
import TicketList from './pages/TicketList'
import TicketDetail from './pages/TicketDetail'
import Navbar from './components/Navbar'

const PAGES = { LIST:'list', DETAIL:'detail', LOGIN:'login', REGISTER:'register' }

function App() {
  const [token, setToken] = useState(() => localStorage.getItem('token'))
  const [user, setUser] = useState(null)
  const [page, setPage] = useState(PAGES.LIST)
  const [currentTicketId, setCurrentTicketId] = useState(null)

  useEffect(() => {
    if (!token) { setUser(null); return }
    api.get('/api/user', { headers: { Authorization: `Bearer ${token}` } })
      .then(r => { setUser(r.data); setPage(PAGES.LIST); })
      .catch(() => { localStorage.removeItem('token'); setToken(null); setUser(null); })
  }, [token])

  const login = (t, u) => { localStorage.setItem('token', t); setToken(t); setUser(u); }
  const logout = () => { localStorage.removeItem('token'); setToken(null); setUser(null); setPage(PAGES.LIST); }

  if (!token || !user) {
    return page === PAGES.REGISTER
      ? <Register onLogin={(t,u)=>{login(t,u);setPage(PAGES.LIST)}} onSwitch={()=>setPage(PAGES.LOGIN)} />
      : <Login onLogin={login} onSwitch={()=>setPage(PAGES.REGISTER)} />
  }

  return (
    <div className="min-h-screen">
      <Navbar user={user} onLogout={logout} onNewTicket={()=>setPage(PAGES.LIST)} />
      <div className="max-w-4xl mx-auto p-4">
        {page === PAGES.LIST && <TicketList user={user} token={token} onSelectTicket={(id)=>{setCurrentTicketId(id);setPage(PAGES.DETAIL)}} />}
        {page === PAGES.DETAIL && <TicketDetail user={user} token={token} ticketId={currentTicketId} onBack={()=>setPage(PAGES.LIST)} />}
      </div>
    </div>
  )
}
export default App
