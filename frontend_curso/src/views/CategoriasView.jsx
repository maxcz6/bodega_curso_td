import { useState } from 'react';
import { Plus, Edit2, Trash2, FolderOpen } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';

const EMPTY_FORM = { nombre: '', descripcion: '' };

export default function CategoriasView() {
  const { items: categorias, loading, error, createItem, updateItem, deleteItem, clearError } = useCrud('/categorias', 'id_categoria');

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

  const openEdit = (cat) => {
    setFormData({ nombre: cat.nombre, descripcion: cat.descripcion || '' });
    setEditingId(cat.id_categoria);
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
    } catch (err) {
      setFormError(err.response?.data?.message || 'Error al guardar la categoría');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id, nombre) => {
    if (!confirm(`¿Eliminar la categoría "${nombre}"? Esta acción no se puede deshacer.`)) return;
    try {
      await deleteItem(id);
    } catch (err) {
      alert(err.response?.data?.message || 'Error al eliminar la categoría');
    }
  };

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de categorías">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Categorías</h1>
          <p className="text-text-muted mt-1">Organiza los productos por categoría.</p>
        </div>
        <Button onClick={openNew} className="gap-2 self-start sm:self-auto" id="btn-nueva-categoria">
          <Plus size={18} />
          Nueva Categoría
        </Button>
      </div>

      {/* Error global */}
      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-red-400 hover:text-red-300 text-xs ml-4">Cerrar</button>
        </div>
      )}

      {/* Lista */}
      <div className="grid gap-3">
        {loading ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-16 rounded-lg bg-surface/50 animate-pulse" />
          ))
        ) : categorias.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-text-muted">
            <FolderOpen size={40} className="mb-3 opacity-40" />
            <p>No hay categorías registradas.</p>
            <Button onClick={openNew} variant="ghost" className="mt-3 gap-2 text-primary">
              <Plus size={16} /> Crear la primera
            </Button>
          </div>
        ) : (
          categorias.map((cat, i) => (
            <div
              key={cat.id_categoria}
              className="p-4 bg-surface rounded-lg flex justify-between items-center gap-4 animate-in fade-in slide-in-from-bottom-2 border border-border/30 hover:border-border/60 transition-colors"
              style={{ animationDelay: `${i * 40}ms` }}
              role="listitem"
            >
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-primary/10 text-primary">
                  <FolderOpen size={16} />
                </div>
                <div>
                  <div className="font-medium text-text">{cat.nombre}</div>
                  {cat.descripcion && <div className="text-sm text-text-muted">{cat.descripcion}</div>}
                </div>
              </div>
              <div className="flex gap-2 shrink-0">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => openEdit(cat)}
                  aria-label={`Editar ${cat.nombre}`}
                  id={`btn-editar-cat-${cat.id_categoria}`}
                >
                  <Edit2 size={14} />
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handleDelete(cat.id_categoria, cat.nombre)}
                  className="text-red-400 hover:bg-red-400/10 hover:border-red-400/40"
                  aria-label={`Eliminar ${cat.nombre}`}
                  id={`btn-eliminar-cat-${cat.id_categoria}`}
                >
                  <Trash2 size={14} />
                </Button>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Modal de formulario */}
      <Modal
        isOpen={modalOpen}
        onClose={closeModal}
        title={editingId ? 'Editar Categoría' : 'Nueva Categoría'}
      >
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de categoría">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}
          <div className="space-y-1">
            <label htmlFor="cat-nombre" className="block text-sm font-medium text-text-muted">
              Nombre <span className="text-red-400">*</span>
            </label>
            <Input
              id="cat-nombre"
              placeholder="Ej: Bebidas"
              value={formData.nombre}
              onChange={(e) => {
                const val = e.target.value.replace(/[0-9]/g, '');
                setFormData({ ...formData, nombre: val });
              }}
              required
              autoFocus
            />
          </div>
          <div className="space-y-1">
            <label htmlFor="cat-descripcion" className="block text-sm font-medium text-text-muted">
              Descripción
            </label>
            <Input
              id="cat-descripcion"
              placeholder="Descripción opcional"
              value={formData.descripcion}
              onChange={(e) => {
                const val = e.target.value.replace(/[<>{}|=]/g, '');
                setFormData({ ...formData, descripcion: val });
              }}
            />
          </div>
          <div className="flex gap-3 pt-2">
            <Button type="submit" className="flex-1" disabled={saving} id="btn-guardar-categoria">
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
