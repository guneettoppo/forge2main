import { useState, useEffect } from 'react'
import api from '../api/client'

export default function TicketList({ user, token, onSelectTicket }) {
  const [tickets, setTickets] = useState([])
  const [filters, setFilters] = useState({ status:'', priority:'', q:'' })
  const [loading, setLoading] = useState(true)

  const load = async () => {
    setLoading(true)
    try {
      const params = {};
      if (filters.status) params.status = filters.status;
      if (filters.priority) params.priority = filters.priority;
      if (filters.q) params.q = filters.q;
      const { data } = await api.get('/api/tickets', { params })
      setTickets(data.data)
    } finally { setLoading(false) }
  }

  useEffect(() => { load() }, [filters])

  return (
    <div className="space-y-4">
      <div className="flex gap-3 flex-wrap">
        <select value={filters.status} onChange={e=>setFilters({...filters,status:e.target.value})} className="bg-gray-800 rounded px-3 py-1 text-sm border border-gray-700">
          <option value="">All status</option>
          <option value="open">Open</option>
          <option value="pending">Pending</option>
          <option value="resolved">Resolved</option>
          <option value="closed">Closed</option>
        </select>
        <select value={filters.priority} onChange={e=>setFilters({...filters,priority:e.target.value})} className="bg-gray-800 rounded px-3 py-1 text-sm border border-gray-700">
          <option value="">All priority</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
        <input value={filters.q} onChange={e=>setFilters({...filters,q:e.target.value})} placeholder="Search tickets…" className="bg-gray-800 rounded px-3 py-1 text-sm border border-gray-700 flex-1 min-w-[200px]" />
      </div>

      {loading && <p className="text-gray-400">Loading…</p>}

      {!loading && tickets.length === 0 && <p className="text-gray-500">No tickets found.</p>}

      <div className="space-y-3">
        {tickets.map(t => (
          <div key={t.id} onClick={()=>onSelectTicket(t.id)} className="bg-gray-800 p-4 rounded cursor-pointer hover:bg-gray-750 border border-gray-700">
            <div className="flex justify-between items-start">
              <div>
                <span className="text-xs px-2 py-0.5 rounded bg-gray-700 mr-2">{t.status}</span>
                <span className="text-xs px-2 py-0.5 rounded bg-gray-700 mr-2">{t.priority}</span>
              </div>
              <span className="text-xs text-gray-500">#{t.id}</span>
            </div>
            <h3 className="mt-2 font-semibold">{t.subject}</h3>
            <p className="text-sm text-gray-400 line-clamp-2">{t.description}</p>
            <div className="mt-2 text-xs text-gray-500">
              {t.requester?.name} → {t.assignee?.name}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
