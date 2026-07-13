import { useState, useEffect, useCallback } from 'react';
import api from '../lib/api';

/**
 * Hook genérico para operaciones CRUD sobre un endpoint de la API.
 *
 * @param {string} endpoint - Ruta base de la API (ej. '/categorias')
 * @param {string} [idKey='id'] - Nombre del campo que actúa como PK (ej. 'id_categoria')
 * @returns {{
 *   items: Array,
 *   loading: boolean,
 *   error: string|null,
 *   fetchData: Function,
 *   createItem: Function,
 *   updateItem: Function,
 *   deleteItem: Function,
 *   clearError: Function
 * }}
 */
export function useCrud(endpoint, idKey = 'id') {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get(endpoint);
      // Normaliza distintas formas de respuesta de la API
      setItems(res.data?.data ?? res.data ?? []);
      setError(null);
    } catch (err) {
      const msg = err.response?.data?.message || `Error al cargar ${endpoint}`;
      setError(msg);
      console.error(`[useCrud] GET ${endpoint}:`, err);
    } finally {
      setLoading(false);
    }
  }, [endpoint]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const createItem = useCallback(async (data) => {
    const res = await api.post(endpoint, data);
    await fetchData();
    return res.data;
  }, [endpoint, fetchData]);

  const updateItem = useCallback(async (id, data) => {
    const res = await api.put(`${endpoint}/${id}`, data);
    await fetchData();
    return res.data;
  }, [endpoint, fetchData]);

  const deleteItem = useCallback(async (id) => {
    await api.delete(`${endpoint}/${id}`);
    await fetchData();
  }, [endpoint, fetchData]);

  const clearError = useCallback(() => setError(null), []);

  return { items, loading, error, fetchData, createItem, updateItem, deleteItem, clearError };
}
