import axios from 'axios';

const api = axios.create({
  baseURL: window?.__API_BASE__ || 'http://localhost:8000/api', // Laravel API endpoint (overridable)
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export default api;
