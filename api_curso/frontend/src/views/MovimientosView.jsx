import { useEffect, useState } from 'react';
import { Plus, TrendingUp, TrendingDown, ArrowUpDown } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Select } from '../components/ui/Select';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';
import api from '../lib/api';

const EMPTY_FORM = {
  id_producto: '',
  cantidad: '',
  id_motivo_movimiento: '',
  observaciones: '',
};

export default function MovimientosView() {
  const { items: movimientos, loading, error, fetchData, clearError } = useCrud('/inventario/movimientos', 'id_movimiento_inventario');

  const [productos, setProductos] = useState([]);
  const [motivos, setMotivos] = useState([]);
  const [auxiliarLoading, setAuxiliarLoading] = useState(true);

  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState(EMPTY_FORM);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);

  useEffect(() => {
    Promise.all([
      api.get('/productos'),
      api.get('/motivos-movimiento'),
    ]).then(([prodRes, motRes]) => {
      setProductos(prodRes.data?.data ?? prodRes.data ?? []);
      setMotivos(motRes.data?.data ?? motRes.data ?? []);
    }).catch(err => console.error('[MovimientosView] datos auxiliares:', err))
      .finally(() => setAuxiliarLoading(false));
  }, []);

  const openModal = () => {
    setFormData(EMPTY_FORM);
    setFormError(null);
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setFormData(EMPTY_FORM);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setFormError(null);
    try {
      await api.post('/inventario/movimientos', formData);
      await fetchData();
      closeModal();
    } catch (err) {
      setFormError(err.response?.data?.message || 'Error al registrar el movimiento');
    } finally {
      setSaving(false);
    }
  };

  // Determina si el movimiento es entrada o salida según la cantidad
  const isEntrada = (mov) => (mov.cantidad ?? 0) > 0;

  // Obtiene un color semáforo según tipo de movimiento
  const getMotivoColor = (mov) => {
    const nombre = mov.motivo?.nombre?.toLowerCase() || '';
    if (nombre.includes('compra') || nombre.includes('entrada') || nombre.includes('ajuste +')) {
      return { bg: 'bg-emerald-400/10', text: 'text-emerald-400', Icon: TrendingUp };
    }
    if (nombre.includes('merma') || nombre.includes('pérdida') || nombre.includes('salida')) {
      return { bg: 'bg-red-400/10', text: 'text-red-400', Icon: TrendingDown };
    }
    return { bg: 'bg-blue-400/10', text: 'text-blue-400', Icon: ArrowUpDown };
  };

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de movimientos de inventario">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Movimientos</h1>
          <p className="text-text-muted mt-1">Entradas, salidas y ajustes de stock.</p>
        </div>
        <Button
          onClick={openModal}
          className="gap-2 self-start sm:self-auto"
          id="btn-nuevo-movimiento"
          disabled={auxiliarLoading}
        >
          <Plus size={18} />
          Nuevo Movimiento
        </Button>
      </div>

      {/* Error global */}
      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-xs ml-4">Cerrar</button>
        </div>
      )}

      {/* Lista */}
      <div className="grid gap-3">
        {loading ? (
          Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="h-20 rounded-lg bg-surface/50 animate-pulse" />
          ))
        ) : movimientos.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-text-muted">
            <ArrowUpDown size={40} className="mb-3 opacity-40" />
            <p>No hay movimientos registrados.</p>
            <Button onClick={openModal} variant="ghost" className="mt-3 gap-2 text-primary">
              <Plus size={16} /> Registrar el primero
            </Button>
          </div>
        ) : (
          movimientos.map((mov, i) => {
            const { bg, text, Icon } = getMotivoColor(mov);
            return (
              <div
                key={mov.id_movimiento_inventario}
                className="p-4 bg-surface rounded-lg flex justify-between items-center gap-4 border border-border/30 hover:border-border/60 transition-colors animate-in fade-in"
                style={{ animationDelay: `${i * 40}ms` }}
                role="listitem"
              >
                <div className="flex items-start gap-3">
                  <div className={`p-2 rounded-lg shrink-0 mt-0.5 ${bg} ${text}`}>
                    <Icon size={18} />
                  </div>
                  <div>
                    <div className="font-semibold text-text">{mov.producto?.nombre || `Producto #${mov.id_producto}`}</div>
                    <div className="flex flex-wrap gap-x-3 gap-y-0.5 text-sm text-text-muted">
                      <span>{mov.motivo?.nombre || 'Motivo desconocido'}</span>
                      {mov.observaciones && <span className="italic truncate max-w-xs">"{mov.observaciones}"</span>}
                    </div>
                  </div>
                </div>

                <div className="text-right shrink-0">
                  <div className={`text-xl font-bold ${(mov.cantidad ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400'}`}>
                    {(mov.cantidad ?? 0) >= 0 ? '+' : ''}{mov.cantidad}
                  </div>
                  <div className="text-xs text-text-muted">
                    {new Date(mov.created_at).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' })}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Modal formulario */}
      <Modal isOpen={modalOpen} onClose={closeModal} title="Nuevo Movimiento de Inventario">
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de movimiento">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}

          <div className="space-y-1">
            <label htmlFor="mov-producto" className="block text-sm font-medium text-text-muted">
              Producto <span className="text-red-400">*</span>
            </label>
            <Select
              id="mov-producto"
              value={formData.id_producto}
              onChange={(e) => setFormData(p => ({ ...p, id_producto: e.target.value }))}
              required
            >
              <option value="">Selecciona un producto</option>
              {productos.map(p => (
                <option key={p.id_producto} value={p.id_producto}>
                  {p.nombre} (stock: {p.stock_actual ?? 0})
                </option>
              ))}
            </Select>
          </div>

          <div className="space-y-1">
            <label htmlFor="mov-motivo" className="block text-sm font-medium text-text-muted">
              Motivo <span className="text-red-400">*</span>
            </label>
            <Select
              id="mov-motivo"
              value={formData.id_motivo_movimiento}
              onChange={(e) => setFormData(p => ({ ...p, id_motivo_movimiento: e.target.value }))}
              required
            >
              <option value="">Selecciona un motivo</option>
              {motivos.map(m => (
                <option key={m.id_motivo_movimiento} value={m.id_motivo_movimiento}>{m.nombre}</option>
              ))}
            </Select>
          </div>

          <div className="space-y-1">
            <label htmlFor="mov-cantidad" className="block text-sm font-medium text-text-muted">
              Cantidad <span className="text-red-400">*</span>
              <span className="text-xs font-normal ml-1">(negativa para salida)</span>
            </label>
            <Input
              id="mov-cantidad"
              type="number"
              placeholder="Ej: 10 o -5"
              value={formData.cantidad}
              onChange={(e) => setFormData(p => ({ ...p, cantidad: e.target.value }))}
              required
              autoFocus
            />
          </div>

          <div className="space-y-1">
            <label htmlFor="mov-observaciones" className="block text-sm font-medium text-text-muted">Observaciones</label>
            <Input
              id="mov-observaciones"
              placeholder="Nota u observación opcional"
              value={formData.observaciones}
              onChange={(e) => setFormData(p => ({ ...p, observaciones: e.target.value }))}
            />
          </div>

          <div className="flex gap-3 pt-2">
            <Button type="submit" className="flex-1" disabled={saving} id="btn-registrar-movimiento">
              {saving ? 'Registrando...' : 'Registrar Movimiento'}
            </Button>
            <Button type="button" variant="outline" onClick={closeModal} className="flex-1">Cancelar</Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
