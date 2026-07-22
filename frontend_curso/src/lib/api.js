import axios from 'axios';

const configuredBase = import.meta.env.VITE_API_URL || window?.__API_BASE__ || '/api';
const normalizedBaseURL = configuredBase.endsWith('/api')
  ? configuredBase
  : `${configuredBase.replace(/\/$/, '')}/api`;

const api = axios.create({
  baseURL: normalizedBaseURL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Caché en memoria: guarda respuestas GET por 30 segundos
const cache = new Map();
const CACHE_TTL = 30_000;

api.interceptors.request.use((config) => {
  if (config.method === 'get') {
    const key = config.url + JSON.stringify(config.params ?? {});
    const hit = cache.get(key);
    if (hit && Date.now() - hit.ts < CACHE_TTL) {
      // Usa un adapter personalizado para resolver la promesa de inmediato sin ir a red
      config.adapter = function (config) {
        return Promise.resolve({
          data: hit.data,
          status: 200,
          statusText: 'OK',
          headers: {},
          config,
          request: {}
        });
      };
    }
  }
  return config;
});

api.interceptors.response.use((response) => {
  if (response.config.method === 'get') {
    const key = response.config.url + JSON.stringify(response.config.params ?? {});
    cache.set(key, { data: response.data, ts: Date.now() });
  }
  return response;
});

// Función para invalidar caché de un endpoint (llamar tras POST/PUT/DELETE)
export function invalidateCache(endpoint) {
  for (const key of cache.keys()) {
    if (key.startsWith(endpoint)) {
      cache.delete(key);
    }
  }
}

export default api;
