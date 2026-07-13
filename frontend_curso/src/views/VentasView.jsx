import { useEffect, useState } from 'react';
import { Plus, Trash2, ShoppingCart, Eye } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Select } from '../components/ui/Select';
import { Input } from '../components/ui/Input';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';
import api from '../lib/api';

const EMPTY_DETALLE = { id_producto: '', cantidad: '', precio_unitario: '' };
const EMPTY_FORM = { id_cliente: '', id_tipo_comprobante: '', detalles: [{ ...EMPTY_DETALLE }] };

export default function VentasView() {
  const { items: ventas, loading, error, fetchData, clearError } = useCrud('/ventas', 'id_venta');

  const [productos, setProductos] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [tiposComprobante, setTiposComprobante] = useState([]);
  const [auxiliarLoading, setAuxiliarLoading] = useState(true);

  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState(null); // venta seleccionada para ver detalle
  const [formData, setFormData] = useState(EMPTY_FORM);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);

  useEffect(() => {
    Promise.all([
      api.get('/productos'),
      api.get('/clientes'),
      api.get('/tipo-comprobantes'),
    ]).then(([prodRes, cliRes, tiposRes]) => {
      setProductos(prodRes.data?.data ?? prodRes.data ?? []);
      setClientes(cliRes.data?.data ?? cliRes.data ?? []);
      setTiposComprobante(tiposRes.data?.data ?? tiposRes.data ?? []);
    }).catch(err => console.error('[VentasView] datos auxiliares:', err))
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

  const addDetalle = () =>
    setFormData(prev => ({ ...prev, detalles: [...prev.detalles, { ...EMPTY_DETALLE }] }));

  const removeDetalle = (idx) =>
    setFormData(prev => ({
      ...prev,
      detalles: prev.detalles.filter((_, i) => i !== idx)
    }));

  const updateDetalle = (idx, field, value) => {
    const detalles = [...formData.detalles];
    detalles[idx] = { ...detalles[idx], [field]: value };
    // Auto-completar precio al seleccionar producto
    if (field === 'id_producto' && value) {
      const prod = productos.find(p => String(p.id_producto) === String(value));
      if (prod) detalles[idx].precio_unitario = prod.precio_venta ?? '';
    }
    setFormData(prev => ({ ...prev, detalles }));
  };

  const total = formData.detalles.reduce((sum, d) => {
    const qty = parseFloat(d.cantidad) || 0;
    const price = parseFloat(d.precio_unitario) || 0;
    return sum + qty * price;
  }, 0);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (formData.detalles.some(d => !d.id_producto || !d.cantidad || !d.precio_unitario)) {
      setFormError('Completa todos los campos de los productos.');
      return;
    }
    setSaving(true);
    setFormError(null);
    try {
      await api.post('/ventas', formData);
      await fetchData();
      closeModal();
    } catch (err) {
      setFormError(err.response?.data?.message || 'Error al registrar la venta');
    } finally {
      setSaving(false);
    }
  };

  const getStatusBadge = (venta) => {
    const tipo = venta.tipo_comprobante?.nombre || venta.tipoComprobante?.nombre || '—';
    return tipo;
  };

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de ventas">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Ventas</h1>
          <p className="text-text-muted mt-1">
            {ventas.length > 0 ? `${ventas.length} ventas registradas` : 'Historial y registro de ventas'}
          </p>
        </div>
        <Button onClick={openModal} className="gap-2 self-start sm:self-auto" id="btn-nueva-venta" disabled={auxiliarLoading}>
          <Plus size={18} />
          Nueva Venta
        </Button>
      </div>

      {/* Error global */}
      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-xs ml-4">Cerrar</button>
        </div>
      )}

      {/* Lista de ventas */}
      <div className="grid gap-3">
        {loading ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-20 rounded-lg bg-surface/50 animate-pulse" />
          ))
        ) : ventas.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-text-muted">
            <ShoppingCart size={40} className="mb-3 opacity-40" />
            <p>No hay ventas registradas.</p>
            <Button onClick={openModal} variant="ghost" className="mt-3 gap-2 text-primary">
              <Plus size={16} /> Registrar la primera
            </Button>
          </div>
        ) : (
          ventas.map((venta, i) => (
            <div
              key={venta.id_venta}
              className="p-4 bg-surface rounded-lg flex justify-between items-center gap-4 border border-border/30 hover:border-border/60 transition-colors animate-in fade-in"
              style={{ animationDelay: `${i * 40}ms` }}
              role="listitem"
            >
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-emerald-400/10 text-emerald-400 shrink-0">
                  <ShoppingCart size={18} />
                </div>
                <div>
                  <div className="font-semibold text-text">
                    Venta #{venta.id_venta}
                    <span className="ml-2 text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full font-normal">
                      {getStatusBadge(venta)}
                    </span>
                  </div>
                  <div className="text-sm text-text-muted">
                    {venta.cliente?.nombre || 'Cliente genérico'} · {new Date(venta.created_at).toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' })}
                  </div>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="text-right">
                  <div className="font-bold text-emerald-400 text-lg">S/ {parseFloat(venta.total ?? 0).toFixed(2)}</div>
                  {venta.detalles?.length > 0 && (
                    <div className="text-xs text-text-muted">{venta.detalles.length} ítem(s)</div>
                  )}
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setDetailModal(venta)}
                  aria-label={`Ver detalle venta #${venta.id_venta}`}
                  id={`btn-detalle-venta-${venta.id_venta}`}
                >
                  <Eye size={14} />
                </Button>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Modal nueva venta */}
      <Modal isOpen={modalOpen} onClose={closeModal} title="Nueva Venta" className="max-w-2xl">
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de venta">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}

          <div className="grid sm:grid-cols-2 gap-3">
            <div className="space-y-1">
              <label htmlFor="venta-cliente" className="block text-sm font-medium text-text-muted">
                Cliente <span className="text-red-400">*</span>
              </label>
              <Select
                id="venta-cliente"
                value={formData.id_cliente}
                onChange={(e) => setFormData(p => ({ ...p, id_cliente: e.target.value }))}
                required
              >
                <option value="">Selecciona un cliente</option>
                {clientes.map(c => (
                  <option key={c.id_cliente} value={c.id_cliente}>{c.nombre}</option>
                ))}
              </Select>
            </div>
            <div className="space-y-1">
              <label htmlFor="venta-comprobante" className="block text-sm font-medium text-text-muted">
                Comprobante <span className="text-red-400">*</span>
              </label>
              <Select
                id="venta-comprobante"
                value={formData.id_tipo_comprobante}
                onChange={(e) => setFormData(p => ({ ...p, id_tipo_comprobante: e.target.value }))}
                required
              >
                <option value="">Selecciona tipo</option>
                {tiposComprobante.map(t => (
                  <option key={t.id_tipo_comprobante} value={t.id_tipo_comprobante}>{t.nombre}</option>
                ))}
              </Select>
            </div>
          </div>

          {/* Detalles */}
          <div className="space-y-2">
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-text-muted">Productos</span>
              <Button type="button" onClick={addDetalle} size="sm" variant="outline" className="gap-1 h-7 text-xs">
                <Plus size={12} /> Agregar
              </Button>
            </div>

            <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
              {formData.detalles.map((det, idx) => (
                <div key={idx} className="grid grid-cols-[1fr_80px_90px_32px] gap-2 items-center p-2 bg-background rounded-lg">
                  <Select
                    value={det.id_producto}
                    onChange={(e) => updateDetalle(idx, 'id_producto', e.target.value)}
                    required
                    aria-label={`Producto ${idx + 1}`}
                  >
                    <option value="">Producto</option>
                    {productos.map(p => (
                      <option key={p.id_producto} value={p.id_producto}>{p.nombre}</option>
                    ))}
                  </Select>
                  <Input
                    type="number"
                    min="1"
                    placeholder="Cant."
                    value={det.cantidad}
                    onChange={(e) => updateDetalle(idx, 'cantidad', e.target.value)}
                    required
                    className="text-center"
                    aria-label={`Cantidad ${idx + 1}`}
                  />
                  <Input
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Precio"
                    value={det.precio_unitario}
                    onChange={(e) => updateDetalle(idx, 'precio_unitario', e.target.value)}
                    required
                    aria-label={`Precio ${idx + 1}`}
                  />
                  <button
                    type="button"
                    onClick={() => removeDetalle(idx)}
                    disabled={formData.detalles.length === 1}
                    className="h-8 w-8 flex items-center justify-center rounded text-red-400 hover:bg-red-400/10 disabled:opacity-30 transition-colors"
                    aria-label={`Quitar producto ${idx + 1}`}
                  >
                    <Trash2 size={14} />
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Total */}
          <div className="flex justify-between items-center p-3 bg-surface rounded-lg border border-border/50">
            <span className="text-sm font-medium text-text-muted">Total estimado</span>
            <span className="text-xl font-bold text-emerald-400">S/ {total.toFixed(2)}</span>
          </div>

          <div className="flex gap-3 pt-1">
            <Button type="submit" className="flex-1" disabled={saving} id="btn-registrar-venta">
              {saving ? 'Registrando...' : 'Registrar Venta'}
            </Button>
            <Button type="button" variant="outline" onClick={closeModal} className="flex-1">Cancelar</Button>
          </div>
        </form>
      </Modal>

      {/* Modal detalle de venta */}
      <Modal isOpen={!!detailModal} onClose={() => setDetailModal(null)} title={`Detalle Venta #${detailModal?.id_venta}`}>
        {detailModal && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div>
                <div className="text-text-muted">Cliente</div>
                <div className="font-medium">{detailModal.cliente?.nombre || '—'}</div>
              </div>
              <div>
                <div className="text-text-muted">Comprobante</div>
                <div className="font-medium">{getStatusBadge(detailModal)}</div>
              </div>
              <div>
                <div className="text-text-muted">Fecha</div>
                <div className="font-medium">{new Date(detailModal.created_at).toLocaleString('es-PE')}</div>
              </div>
              <div>
                <div className="text-text-muted">Total</div>
                <div className="font-bold text-emerald-400 text-lg">S/ {parseFloat(detailModal.total ?? 0).toFixed(2)}</div>
              </div>
            </div>
            {detailModal.detalles?.length > 0 && (
              <div>
                <div className="text-sm text-text-muted mb-2">Productos</div>
                <div className="space-y-2">
                  {detailModal.detalles.map((d, i) => (
                    <div key={i} className="flex justify-between items-center p-2 bg-surface rounded text-sm">
                      <span>{d.producto?.nombre || `Producto #${d.id_producto}`}</span>
                      <span className="text-text-muted">{d.cantidad} × S/ {parseFloat(d.precio_unitario).toFixed(2)}</span>
                      <span className="font-semibold">S/ {(d.cantidad * d.precio_unitario).toFixed(2)}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}
            <Button variant="outline" onClick={() => setDetailModal(null)} className="w-full">Cerrar</Button>
          </div>
        )}
      </Modal>
    </div>
  );
}
