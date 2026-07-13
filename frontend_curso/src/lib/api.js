import axios from 'axios';

const configuredBase = import.meta.env.VITE_API_URL || window?.__API_BASE__ || '/api';
const normalizedBaseURL = configuredBase.endsWith('/api')
  ? configuredBase
  : `${configuredBase.replace(/\/$/, '')}/api`;

const api = axios.create({
  baseURL: normalizedBaseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export default api;
