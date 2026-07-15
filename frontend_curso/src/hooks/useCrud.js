import { useState, useEffect, useCallback, useRef } from 'react';
import api, { invalidateCache } from '../lib/api';

/**
 * Hook genérico para operaciones CRUD sobre un endpoint de la API.
 * - Optimistic updates: actualiza la lista local de inmediato sin esperar re-fetch.
 * - Caché en memoria vía api.js: evita peticiones duplicadas en 30 s.
 *
 * @param {string} endpoint - Ruta base de la API (ej. '/categorias')
 * @param {string} [idKey='id'] - Nombre del campo que actúa como PK (ej. 'id_categoria')
 */
export function useCrud(endpoint, idKey = 'id') {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const abortRef = useRef(null);

  const fetchData = useCallback(async ({ silent = false } = {}) => {
    // Aborta la petición anterior si todavía estaba en vuelo
    if (abortRef.current) abortRef.current.abort();
    const controller = new AbortController();
    abortRef.current = controller;

    if (!silent) setLoading(true);
    try {
      const res = await api.get(endpoint, { signal: controller.signal });
      setItems(res.data?.data ?? res.data ?? []);
      setError(null);
    } catch (err) {
      if (err.name === 'CanceledError' || err.name === 'AbortError') return;
      const msg = err.response?.data?.message || `Error al cargar ${endpoint}`;
      setError(msg);
      console.error(`[useCrud] GET ${endpoint}:`, err);
    } finally {
      setLoading(false);
    }
  }, [endpoint]);

  useEffect(() => {
    fetchData();
    return () => abortRef.current?.abort();
  }, [fetchData]);

  const createItem = useCallback(async (data) => {
    const res = await api.post(endpoint, data);
    const created = res.data?.data ?? res.data;
    // Optimistic: agrega el item a la lista local sin re-fetch
    if (created && created[idKey]) {
      setItems(prev => [created, ...prev]);
    }
    invalidateCache(endpoint);
    return created;
  }, [endpoint, idKey]);

  const updateItem = useCallback(async (id, data) => {
    const res = await api.put(`${endpoint}/${id}`, data);
    const updated = res.data?.data ?? res.data;
    // Optimistic: reemplaza el item en la lista
    if (updated) {
      setItems(prev => prev.map(item =>
        String(item[idKey]) === String(id) ? { ...item, ...updated } : item
      ));
    }
    invalidateCache(endpoint);
    return updated;
  }, [endpoint, idKey]);

  const deleteItem = useCallback(async (id) => {
    // Optimistic: elimina de la lista antes de la respuesta
    setItems(prev => prev.filter(item => String(item[idKey]) !== String(id)));
    try {
      await api.delete(`${endpoint}/${id}`);
      invalidateCache(endpoint);
    } catch (err) {
      // Si falla, refresca para restaurar estado real
      await fetchData({ silent: true });
      throw err;
    }
  }, [endpoint, idKey, fetchData]);

  const clearError = useCallback(() => setError(null), []);

  return { items, loading, error, fetchData, createItem, updateItem, deleteItem, clearError };
}
