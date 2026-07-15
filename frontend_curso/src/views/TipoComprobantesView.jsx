import { useState } from 'react';
import { Plus, Edit2, Trash2, FileText } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';
import { useLocation, useNavigate } from 'react-router-dom';

const EMPTY_FORM = { nombre: '' };

export default function TipoComprobantesView() {
  const { items: tipos, loading, error, createItem, updateItem, deleteItem, clearError } = useCrud('/tipo-comprobantes', 'id_tipo_comprobante');
  const location = useLocation();
  const navigate = useNavigate();

  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState(EMPTY_FORM);
  const [editingId, setEditingId] = useState(null);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);

  const openNew = () => {
    setFormData(EMPTY_FORM);
    setEditingId(null);
    setFormError(null);
    setModalOpen(true);
  };

  const openEdit = (tipo) => {
    setFormData({ nombre: tipo.nombre || '' });
    setEditingId(tipo.id_tipo_comprobante);
    setFormError(null);
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditingId(null);
    setFormData(EMPTY_FORM);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setFormError(null);
    try {
      if (editingId) {
        await updateItem(editingId, formData);
      } else {
        await createItem(formData);
      }
      closeModal();
      
      if (location.state?.returnTo) {
        navigate(location.state.returnTo);
      }
    } catch (err) {
      setFormError(err.response?.data?.message || 'Error al guardar el comprobante');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id, nombre) => {
    if (!confirm(`¿Eliminar el comprobante "${nombre}"? Esta acción no se puede deshacer.`)) return;
    try {
      await deleteItem(id);
    } catch (err) {
      alert(err.response?.data?.message || 'Error al eliminar el comprobante');
    }
  };

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de tipos de comprobantes">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Comprobantes</h1>
          <p className="text-text-muted mt-1">
            {tipos.length > 0 ? `${tipos.length} comprobantes registrados` : 'Gestiona los tipos de comprobantes'}
          </p>
        </div>
        <div className="flex gap-2 self-start sm:self-auto">
          {location.state?.returnTo && (
            <Button variant="outline" onClick={() => navigate(location.state.returnTo)}>
              Volver a Ventas
            </Button>
          )}
          <Button onClick={openNew} className="gap-2" id="btn-nuevo-comprobante">
            <Plus size={18} />
            Nuevo Comprobante
          </Button>
        </div>
      </div>

      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-red-400 hover:text-red-300 text-xs ml-4">Cerrar</button>
        </div>
      )}

      <div className="grid gap-3">
        {loading ? (
          Array.from({ length: 2 }).map((_, i) => (
            <div key={i} className="h-16 rounded-lg bg-surface/50 animate-pulse" />
          ))
        ) : tipos.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-text-muted">
            <FileText size={40} className="mb-3 opacity-40" />
            <p>No hay comprobantes registrados.</p>
            <Button onClick={openNew} variant="ghost" className="mt-3 gap-2 text-primary">
              <Plus size={16} /> Agregar el primero
            </Button>
          </div>
        ) : (
          tipos.map((tipo, i) => (
            <div
              key={tipo.id_tipo_comprobante}
              className="p-4 bg-surface rounded-lg flex justify-between items-center gap-4 animate-in fade-in slide-in-from-bottom-2 border border-border/30 hover:border-border/60 transition-colors"
              style={{ animationDelay: `${i * 40}ms` }}
              role="listitem"
            >
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 bg-blue-400/20 text-blue-400">
                  <FileText size={18} />
                </div>
                <div>
                  <div className="font-medium text-text">{tipo.nombre}</div>
                  <div className="text-sm text-text-muted flex flex-wrap gap-x-3">
                    <span className="font-mono text-xs">ID: {tipo.id_tipo_comprobante}</span>
                  </div>
                </div>
              </div>
              <div className="flex gap-2 shrink-0">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => openEdit(tipo)}
                  aria-label={`Editar ${tipo.nombre}`}
                >
                  <Edit2 size={14} />
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handleDelete(tipo.id_tipo_comprobante, tipo.nombre)}
                  className="text-red-400 hover:bg-red-400/10 hover:border-red-400/40"
                  aria-label={`Eliminar ${tipo.nombre}`}
                >
                  <Trash2 size={14} />
                </Button>
              </div>
            </div>
          ))
        )}
      </div>

      <Modal
        isOpen={modalOpen}
        onClose={closeModal}
        title={editingId ? 'Editar Comprobante' : 'Nuevo Comprobante'}
      >
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de comprobante">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}
          <div className="space-y-1">
            <label htmlFor="comp-nombre" className="block text-sm font-medium text-text-muted">
              Nombre <span className="text-red-400">*</span>
            </label>
            <Input
              id="comp-nombre"
              placeholder="Ej: Ticket"
              value={formData.nombre}
              onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
              required
              autoFocus
            />
          </div>
          <div className="flex gap-3 pt-2">
            <Button type="submit" className="flex-1" disabled={saving}>
              {saving ? 'Guardando...' : (editingId ? 'Actualizar' : 'Guardar')}
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
