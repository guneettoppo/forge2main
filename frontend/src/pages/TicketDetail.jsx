import { useState, useEffect } from 'react'
import api from '../api/client'

const COLORS = { open:'bg-blue-700', pending:'bg-yellow-700', resolved:'bg-green-700', closed:'bg-gray-600' }
const PRIORITY = { low:'bg-gray-700', medium:'bg-indigo-700', high:'bg-red-700', urgent:'bg-red-900' }

export default function TicketDetail({ user, token, ticketId, onBack }) {
  const [ticket, setTicket] = useState(null)
  const [comments, setComments] = useState([])
  const [body, setBody] = useState('')
  const [isInternal, setIsInternal] = useState(false)
  const [status, setStatus] = useState('')

  const load = async () => {
    const { data } = await api.get(`/api/tickets/${ticketId}`)
    setTicket(data)
    setStatus(data.status)
    const c = await api.get(`/api/tickets/${ticketId}/comments`)
    setComments(c.data.data)
  }

  useEffect(() => { load() }, [ticketId])

  const postComment = async (e) => {
    e.preventDefault()
    if (!body.trim()) return
    await api.post(`/api/tickets/${ticketId}/comments`, { body, is_internal: isInternal })
    setBody('')
    setIsInternal(false)
    load()
  }

  const updateStatus = async (s) => {
    await api.put(`/api/tickets/${ticketId}`, { status:s })
    setStatus(s)
    load()
  }

  if (!ticket) return <p className="text-gray-400">Loading…</p>

  return (
    <div className="space-y-4">
      <button onClick={onBack} className="text-sm text-indigo-400 hover:underline">← Back</button>

      <div className="bg-gray-800 p-6 rounded border border-gray-700">
        <h2 className="text-2xl font-bold">{ticket.subject}</h2>
        <p className="mt-2 text-gray-300">{ticket.description}</p>
        <div className="mt-4 flex flex-wrap gap-3 items-center">
          <span className={`px-2 py-1 rounded text-xs ${COLORS[status]||'bg-gray-700'}`}>{status}</span>
          <span className={`px-2 py-1 rounded text-xs ${PRIORITY[ticket.priority]||'bg-gray-700'}`}>{ticket.priority}</span>
          <span className="text-xs text-gray-500">#{ticket.id}</span>
        </div>
        <div className="mt-4 text-sm text-gray-400">
          Requester: {ticket.requester?.name} | Assignee: {ticket.assignee?.name}
        </div>
        {(user.role==='admin'||user.role==='agent') && (
          <div className="mt-4">
            <label className="text-sm mr-2">Set status:</label>
            {['open','pending','resolved','closed'].map(s=>(
              <button key={s} onClick={()=>updateStatus(s)} className={`mr-2 px-2 py-1 rounded text-xs ${status===s?'bg-indigo-600':'bg-gray-700'}`}>{s}</button>
            ))}
          </div>
        )}
      </div>

      <div className="bg-gray-800 p-4 rounded border border-gray-700">
        <h3 className="font-semibold mb-3">Conversation</h3>
        <div className="space-y-3 mb-4 max-h-96 overflow-y-auto">
          {comments.map(c => (
            <div key={c.id} className={`p-3 rounded ${c.is_internal && user.role==='customer'?'hidden':'bg-gray-700/50'}`}>
              <div className="text-xs text-gray-400 flex gap-2 items-center">
                <span>{c.author?.name}</span>
                <span>{new Date(c.created_at).toLocaleString()}</span>
                {c.is_internal && <span className="text-yellow-400">[internal]</span>}
              </div>
              <p className="text-sm mt-1">{c.body}</p>
            </div>
          ))}
          {comments.length===0 && <p className="text-sm text-gray-500">No comments yet.</p>}
        </div>

        <form onSubmit={postComment} className="space-y-2 border-t border-gray-700 pt-3">
          <textarea value={body} onChange={e=>setBody(e.target.value)} rows={2} className="w-full bg-gray-900 rounded p-2 text-sm" />
          {user.role !== 'customer' && (
            <label className="flex items-center gap-2 text-sm text-gray-400">
              <input type="checkbox" checked={isInternal} onChange={e=>setIsInternal(e.target.checked)} />
              Internal note (agents only)
            </label>
          )}
          <button type="submit" className="px-4 py-1 bg-indigo-600 rounded text-sm hover:bg-indigo-500">Post</button>
        </form>
      </div>
    </div>
  )
}
