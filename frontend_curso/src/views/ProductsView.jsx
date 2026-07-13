import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Search, Package, AlertTriangle } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Select } from '../components/ui/Select';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';
import api from '../lib/api';

const EMPTY_FORM = {
  nombre: '',
  descripcion: '',
  precio_compra: '',
  precio_venta: '',
  stock_actual: '',
  stock_minimo: '',
  id_categoria: '',
};

export default function ProductsView() {
  const {
    items: productos,
    loading,
    error,
    createItem,
    updateItem,
    deleteItem,
    clearError,
  } = useCrud('/productos', 'id_producto');

  const [categorias, setCategorias] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState(EMPTY_FORM);
  const [editingId, setEditingId] = useState(null);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);
  const [priceError, setPriceError] = useState('');
  const [search, setSearch] = useState('');

  // Cargar categorías para el selector del formulario
  useEffect(() => {
    api.get('/categorias')
      .then(res => setCategorias(res.data?.data ?? res.data ?? []))
      .catch(err => console.error('[ProductsView] categorias:', err));
  }, []);

  const filtered = productos.filter(p =>
    p.nombre?.toLowerCase().includes(search.toLowerCase()) ||
    p.categoria?.nombre?.toLowerCase().includes(search.toLowerCase())
  );

  const getPriceValidationMessage = (data) => {
    const compra = data.precio_compra === '' ? null : Number(data.precio_compra);
    const venta = data.precio_venta === '' ? null : Number(data.precio_venta);

    if (compra == null || venta == null) return '';
    if (Number.isNaN(compra) || Number.isNaN(venta)) return '';
    if (compra > venta) {
      return 'El precio de compra no puede ser mayor que el precio de venta.';
    }
    return '';
  };

  const openNew = () => {
    setFormData(EMPTY_FORM);
    setEditingId(null);
    setFormError(null);
    setPriceError('');
    setModalOpen(true);
  };

  const openEdit = (prod) => {
    setFormData({
      nombre: prod.nombre || '',
      descripcion: prod.descripcion || '',
      precio_compra: prod.precio_compra ?? '',
      precio_venta: prod.precio_venta ?? '',
      stock_actual: prod.stock_actual ?? '',
      stock_minimo: prod.stock_minimo ?? '',
      id_categoria: prod.id_categoria ?? prod.categoria?.id_categoria ?? '',
    });
    setEditingId(prod.id_producto);
    setFormError(null);
    setPriceError('');
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditingId(null);
    setFormData(EMPTY_FORM);
    setPriceError('');
  };

  const handleChange = (field) => (e) => {
    const rawValue = e.target.value;

    setFormData(prev => {
      const nextValue = rawValue;
      let nextData = { ...prev, [field]: nextValue };

      if ((field === 'precio_compra' || field === 'precio_venta') && nextValue !== '') {
        const compraRaw = field === 'precio_compra' ? nextValue : prev.precio_compra;
        const ventaRaw = field === 'precio_venta' ? nextValue : prev.precio_venta;

        const compra = compraRaw === '' ? null : Number(compraRaw);
        const venta = ventaRaw === '' ? null : Number(ventaRaw);

        if (compra != null && venta != null && !Number.isNaN(compra) && !Number.isNaN(venta)) {
          if (field === 'precio_compra' && compra > venta) {
            nextData = { ...nextData, precio_compra: String(venta) };
          }

          if (field === 'precio_venta' && venta < compra) {
            nextData = { ...nextData, precio_venta: String(compra) };
          }
        }
      }

      const validationMessage = getPriceValidationMessage(nextData);
      setPriceError(validationMessage);
      return nextData;
    });
  };

  const getApiErrorMessage = (err) => {
    const data = err?.response?.data;

    if (typeof data?.message === 'string' && data.message.trim()) {
      return data.message;
    }

    if (data?.errors && typeof data.errors === 'object') {
      const firstError = Object.values(data.errors).find(value => Array.isArray(value) && value.length > 0);
      if (firstError && typeof firstError[0] === 'string') {
        return firstError[0];
      }

      const fallbackError = Object.values(data.errors).find(value => typeof value === 'string' && value.trim());
      if (fallbackError) {
        return fallbackError;
      }
    }

    return err?.message || 'Error al guardar el producto';
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setFormError(null);

    const validationMessage = getPriceValidationMessage(formData);
    if (validationMessage) {
      setPriceError(validationMessage);
      setFormError(validationMessage);
      setSaving(false);
      return;
    }

    try {
      if (editingId) {
        await updateItem(editingId, formData);
      } else {
        await createItem(formData);
      }
      closeModal();
    } catch (err) {
      setFormError(getApiErrorMessage(err));
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id, nombre) => {
    if (!confirm(`¿Eliminar "${nombre}"? Esta acción no se puede deshacer.`)) return;
    try {
      await deleteItem(id);
    } catch (err) {
      alert(err.response?.data?.message || 'Error al eliminar el producto');
    }
  };

  const isLowStock = (prod) =>
    prod.stock_minimo != null && prod.stock_actual <= prod.stock_minimo;

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de productos">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Productos</h1>
          <p className="text-text-muted mt-1">
            {productos.length > 0
              ? `${productos.length} productos en catálogo`
              : 'Gestiona el catálogo de tu inventario'}
          </p>
        </div>
        <Button onClick={openNew} className="gap-2 self-start sm:self-auto" id="btn-nuevo-producto">
          <Plus size={18} />
          Nuevo Producto
        </Button>
      </div>

      {/* Error global */}
      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-red-400 hover:text-red-300 text-xs ml-4">Cerrar</button>
        </div>
      )}

      {/* Buscador */}
      {!loading && productos.length > 0 && (
        <div className="relative max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" size={16} />
          <Input
            id="input-buscar-productos"
            placeholder="Buscar por nombre o categoría..."
            className="pl-9"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Buscar productos"
          />
        </div>
      )}

      {/* Lista */}
      <div className="grid gap-4">
        {loading ? (
          Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="h-24 rounded-xl bg-surface/50 animate-pulse" />
          ))
        ) : filtered.length === 0 ? (
          productos.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 text-text-muted">
              <Package size={40} className="mb-3 opacity-40" />
              <p>No hay productos en el catálogo.</p>
              <Button onClick={openNew} variant="ghost" className="mt-3 gap-2 text-primary">
                <Plus size={16} /> Agregar el primero
              </Button>
            </div>
          ) : (
            <div className="text-center py-10 text-text-muted">
              Sin resultados para "<strong>{search}</strong>"
            </div>
          )
        ) : (
          filtered.map((prod, i) => (
            <div
              key={prod.id_producto}
              className={`p-4 rounded-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-bottom-2 border transition-colors ${
                isLowStock(prod)
                  ? 'bg-red-400/5 border-red-400/30 hover:border-red-400/50'
                  : 'bg-surface border-border/30 hover:border-border/60'
              }`}
              style={{ animationDelay: `${i * 40}ms` }}
              role="listitem"
            >
              {/* Info izquierda */}
              <div className="flex items-start gap-3">
                <div className={`p-2 rounded-lg mt-0.5 shrink-0 ${isLowStock(prod) ? 'bg-red-400/20 text-red-400' : 'bg-primary/10 text-primary'}`}>
                  {isLowStock(prod) ? <AlertTriangle size={18} /> : <Package size={18} />}
                </div>
                <div>
                  <div className="font-semibold text-text">{prod.nombre}</div>
                  <div className="flex flex-wrap gap-x-3 gap-y-0.5 mt-0.5">
                    <span className="text-xs text-text-muted">{prod.categoria?.nombre || 'Sin categoría'}</span>
                    {prod.descripcion && (
                      <span className="text-xs text-text-muted truncate max-w-xs">{prod.descripcion}</span>
                    )}
                  </div>
                  {isLowStock(prod) && (
                    <div className="text-xs text-red-400 mt-1 font-medium">⚠ Stock bajo</div>
                  )}
                </div>
              </div>

              {/* Info derecha */}
              <div className="flex items-center justify-between w-full sm:w-auto gap-4">
                <div className="flex gap-4 text-right">
                  <div>
                    <div className="text-xs text-text-muted">Precio</div>
                    <div className="font-bold text-emerald-400">
                      S/ {Number(prod.precio_venta ?? 0).toFixed(2)}
                    </div>
                  </div>
                  <div>
                    <div className="text-xs text-text-muted">Stock</div>
                    <div className={`font-bold ${isLowStock(prod) ? 'text-red-400' : 'text-text'}`}>
                      {prod.stock_actual ?? 0}
                      {prod.stock_minimo != null && (
                        <span className="text-xs font-normal text-text-muted"> / mín {prod.stock_minimo}</span>
                      )}
                    </div>
                  </div>
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    className="h-8 w-8 p-0"
                    onClick={() => openEdit(prod)}
                    aria-label={`Editar ${prod.nombre}`}
                    id={`btn-editar-prod-${prod.id_producto}`}
                  >
                    <Edit2 size={14} />
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    className="h-8 w-8 p-0 text-red-400 hover:bg-red-400/10 hover:border-red-400/40"
                    onClick={() => handleDelete(prod.id_producto, prod.nombre)}
                    aria-label={`Eliminar ${prod.nombre}`}
                    id={`btn-eliminar-prod-${prod.id_producto}`}
                  >
                    <Trash2 size={14} />
                  </Button>
                </div>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Modal formulario */}
      <Modal
        isOpen={modalOpen}
        onClose={closeModal}
        title={editingId ? 'Editar Producto' : 'Nuevo Producto'}
        className="max-w-2xl"
      >
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de producto">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}

          {/* Nombre y Categoría */}
          <div className="grid sm:grid-cols-2 gap-3">
            <div className="space-y-1">
              <label htmlFor="prod-nombre" className="block text-sm font-medium text-text-muted">
                Nombre <span className="text-red-400">*</span>
              </label>
              <Input
                id="prod-nombre"
                placeholder="Ej: Gaseosa 3L"
                value={formData.nombre}
                onChange={handleChange('nombre')}
                required
                autoFocus
              />
            </div>
            <div className="space-y-1">
              <label htmlFor="prod-categoria" className="block text-sm font-medium text-text-muted">
                Categoría
              </label>
              <Select
                id="prod-categoria"
                value={formData.id_categoria}
                onChange={handleChange('id_categoria')}
              >
                <option value="">Sin categoría</option>
                {categorias.map(c => (
                  <option key={c.id_categoria} value={c.id_categoria}>{c.nombre}</option>
                ))}
              </Select>
            </div>
          </div>

          {/* Descripción */}
          <div className="space-y-1">
            <label htmlFor="prod-descripcion" className="block text-sm font-medium text-text-muted">Descripción</label>
            <Input
              id="prod-descripcion"
              placeholder="Descripción opcional"
              value={formData.descripcion}
              onChange={handleChange('descripcion')}
            />
          </div>

          {/* Precios */}
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1">
              <label htmlFor="prod-precio-compra" className="block text-sm font-medium text-text-muted">Precio Compra</label>
              <Input
                id="prod-precio-compra"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                value={formData.precio_compra}
                onChange={handleChange('precio_compra')}
              />
            </div>
            <div className="space-y-1">
              <label htmlFor="prod-precio-venta" className="block text-sm font-medium text-text-muted">
                Precio Venta <span className="text-red-400">*</span>
              </label>
              <Input
                id="prod-precio-venta"
                type="number"
                step="0.01"
                min="0"
                placeholder="0.00"
                value={formData.precio_venta}
                onChange={handleChange('precio_venta')}
                required
              />
            </div>
          </div>
          {priceError && (
            <p className="text-sm text-red-400">{priceError}</p>
          )}

          {/* Stock */}
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1">
              <label htmlFor="prod-stock-actual" className="block text-sm font-medium text-text-muted">
                Stock Inicial {!editingId && <span className="text-red-400">*</span>}
              </label>
              <Input
                id="prod-stock-actual"
                type="number"
                min="0"
                placeholder="0"
                value={formData.stock_actual}
                onChange={handleChange('stock_actual')}
                required={!editingId}
              />
            </div>
            <div className="space-y-1">
              <label htmlFor="prod-stock-minimo" className="block text-sm font-medium text-text-muted">Stock Mínimo</label>
              <Input
                id="prod-stock-minimo"
                type="number"
                min="0"
                placeholder="5"
                value={formData.stock_minimo}
                onChange={handleChange('stock_minimo')}
              />
            </div>
          </div>

          <div className="flex gap-3 pt-2">
            <Button type="submit" className="flex-1" disabled={saving} id="btn-guardar-producto">
              {saving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Crear Producto')}
            </Button>
            <Button type="button" variant="outline" onClick={closeModal} className="flex-1">
              Cancelar
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
