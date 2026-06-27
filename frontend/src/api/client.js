import axios from 'axios';
const api = axios.create({ baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000' });
api.interceptors.request.use(c => {
  if (localStorage.token) c.headers.Authorization = `Bearer ${localStorage.token}`;
  return c;
});
export default api;
