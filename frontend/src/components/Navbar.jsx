function Navbar({user, onLogout, onNewTicket}) {
  return (
    <nav className="bg-gray-800 border-b border-gray-700">
      <div className="max-w-4xl mx-auto p-4 flex items-center justify-between">
        <div className="flex items-center gap-4">
          <span className="text-xl font-bold text-indigo-400">PulseDesk</span>
          <span className="text-sm text-gray-400">| {user.name} ({user.role})</span>
        </div>
        <div className="flex gap-3">
          <button onClick={onNewTicket} className="px-3 py-1 bg-indigo-600 rounded text-sm hover:bg-indigo-500">My Tickets</button>
          <button onClick={onLogout} className="px-3 py-1 bg-gray-700 rounded text-sm hover:bg-gray-600">Logout</button>
        </div>
      </div>
    </nav>
  );
}
export default Navbar;
