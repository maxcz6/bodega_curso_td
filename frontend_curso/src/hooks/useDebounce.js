import { useState, useEffect } from 'react';

/**
 * Hook para retrasar la actualización de un valor (debounce).
 * Muy útil para inputs de búsqueda, previniendo re-renders innecesarios y ataques DoS a nivel frontend.
 * 
 * @param {any} value - El valor a observar (ej. texto de búsqueda)
 * @param {number} delay - El tiempo en milisegundos a esperar (por defecto 300ms)
 * @returns {any} El valor debounced (que solo se actualiza cuando el usuario deja de escribir)
 */
export function useDebounce(value, delay = 300) {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    // Configuramos el timer
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    // Limpiamos el timer si el valor cambia (el usuario sigue escribiendo)
    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]); // Solo se re-ejecuta si value o delay cambian

  return debouncedValue;
}
