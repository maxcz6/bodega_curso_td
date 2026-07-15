import { useState } from 'react';
import { Plus, Edit2, Trash2, Users } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Modal } from '../components/ui/Modal';
import { useCrud } from '../hooks/useCrud';
import { useLocation, useNavigate } from 'react-router-dom';

const EMPTY_FORM = { nombres: '', direccion: '', telefono: '', dni_ruc: '' };

export default function ClientesView() {
  const { items: clientes, loading, error, createItem, updateItem, deleteItem, clearError } = useCrud('/clientes', 'id_cliente');
  const location = useLocation();
  const navigate = useNavigate();

  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState(EMPTY_FORM);
  const [editingId, setEditingId] = useState(null);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState(null);
  const [search, setSearch] = useState('');

  const filtered = clientes.filter(c =>
    c.nombres?.toLowerCase().includes(search.toLowerCase()) ||
    c.direccion?.toLowerCase().includes(search.toLowerCase()) ||
    c.dni_ruc?.toLowerCase().includes(search.toLowerCase())
  );

  const openNew = () => {
    setFormData(EMPTY_FORM);
    setEditingId(null);
    setFormError(null);
    setModalOpen(true);
  };

  const openEdit = (cli) => {
    setFormData({
      nombres: cli.nombres || '',
      direccion: cli.direccion || '',
      telefono: cli.telefono || '',
      dni_ruc: cli.dni_ruc || '',
    });
    setEditingId(cli.id_cliente);
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
      setFormError(err.response?.data?.message || 'Error al guardar el cliente');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id, nombre) => {
    if (!confirm(`¿Eliminar al cliente "${nombre}"? Esta acción no se puede deshacer.`)) return;
    try {
      await deleteItem(id);
    } catch (err) {
      alert(err.response?.data?.message || 'Error al eliminar el cliente');
    }
  };

  // Genera iniciales para el avatar
  const getInitials = (nombres) =>
    nombres?.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() || '?';

  // Color de avatar basado en nombre
  const avatarColors = [
    'bg-blue-400/20 text-blue-400',
    'bg-emerald-400/20 text-emerald-400',
    'bg-purple-400/20 text-purple-400',
    'bg-orange-400/20 text-orange-400',
    'bg-pink-400/20 text-pink-400',
  ];
  const getAvatarColor = (nombres) => avatarColors[(nombres?.charCodeAt(0) || 0) % avatarColors.length];

  return (
    <div className="space-y-6" role="main" aria-label="Gestión de clientes">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Clientes</h1>
          <p className="text-text-muted mt-1">
            {clientes.length > 0 ? `${clientes.length} clientes registrados` : 'Gestiona el directorio de clientes'}
          </p>
        </div>
        <div className="flex gap-2 self-start sm:self-auto">
          {location.state?.returnTo && (
            <Button variant="outline" onClick={() => navigate(location.state.returnTo)}>
              Volver a Ventas
            </Button>
          )}
          <Button onClick={openNew} className="gap-2" id="btn-nuevo-cliente">
            <Plus size={18} />
            Nuevo Cliente
          </Button>
        </div>
      </div>

      {/* Error global */}
      {error && (
        <div className="p-3 bg-red-400/20 border border-red-400/30 text-red-400 rounded-lg flex justify-between items-center" role="alert">
          <span>{error}</span>
          <button onClick={clearError} className="text-red-400 hover:text-red-300 text-xs ml-4">Cerrar</button>
        </div>
      )}

      {/* Búsqueda */}
      {!loading && clientes.length > 0 && (
        <Input
          placeholder="Buscar por nombre, email o documento..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          id="input-buscar-clientes"
          aria-label="Buscar clientes"
        />
      )}

      {/* Lista */}
      <div className="grid gap-3">
        {loading ? (
          Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="h-20 rounded-lg bg-surface/50 animate-pulse" />
          ))
        ) : filtered.length === 0 ? (
          clientes.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 text-text-muted">
              <Users size={40} className="mb-3 opacity-40" />
              <p>No hay clientes registrados.</p>
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
          filtered.map((cli, i) => (
            <div
              key={cli.id_cliente}
              className="p-4 bg-surface rounded-lg flex justify-between items-center gap-4 animate-in fade-in slide-in-from-bottom-2 border border-border/30 hover:border-border/60 transition-colors"
              style={{ animationDelay: `${i * 40}ms` }}
              role="listitem"
            >
              <div className="flex items-center gap-3">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 ${getAvatarColor(cli.nombres)}`}>
                  {getInitials(cli.nombres)}
                </div>
                <div>
                  <div className="font-medium text-text">{cli.nombres}</div>
                  <div className="text-sm text-text-muted flex flex-wrap gap-x-3">
                    {cli.direccion && <span>{cli.direccion}</span>}
                    {cli.telefono && <span>{cli.telefono}</span>}
                    {cli.dni_ruc && <span className="font-mono text-xs">DNI/RUC: {cli.dni_ruc}</span>}
                  </div>
                </div>
              </div>
              <div className="flex gap-2 shrink-0">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => openEdit(cli)}
                  aria-label={`Editar ${cli.nombres}`}
                  id={`btn-editar-cli-${cli.id_cliente}`}
                >
                  <Edit2 size={14} />
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handleDelete(cli.id_cliente, cli.nombres)}
                  className="text-red-400 hover:bg-red-400/10 hover:border-red-400/40"
                  aria-label={`Eliminar ${cli.nombres}`}
                  id={`btn-eliminar-cli-${cli.id_cliente}`}
                >
                  <Trash2 size={14} />
                </Button>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Modal formulario */}
      <Modal
        isOpen={modalOpen}
        onClose={closeModal}
        title={editingId ? 'Editar Cliente' : 'Nuevo Cliente'}
      >
        <form onSubmit={handleSubmit} className="space-y-4" aria-label="Formulario de cliente">
          {formError && (
            <div className="p-3 bg-red-400/20 text-red-400 rounded-lg text-sm" role="alert">{formError}</div>
          )}
          <div className="space-y-1">
            <label htmlFor="cli-nombre" className="block text-sm font-medium text-text-muted">
              Nombre <span className="text-red-400">*</span>
            </label>
            <Input
              id="cli-nombre"
              placeholder="Nombres completos"
              value={formData.nombres}
              onChange={(e) => setFormData({ ...formData, nombres: e.target.value })}
              required
              autoFocus
            />
          </div>
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1">
              <label htmlFor="cli-documento" className="block text-sm font-medium text-text-muted">DNI / RUC</label>
              <Input
                id="cli-documento"
                placeholder="12345678"
                value={formData.dni_ruc}
                onChange={(e) => setFormData({ ...formData, dni_ruc: e.target.value })}
              />
            </div>
            <div className="space-y-1">
              <label htmlFor="cli-telefono" className="block text-sm font-medium text-text-muted">Teléfono</label>
              <Input
                id="cli-telefono"
                placeholder="999 999 999"
                value={formData.telefono}
                onChange={(e) => setFormData({ ...formData, telefono: e.target.value })}
              />
            </div>
          </div>
          <div className="space-y-1">
            <label htmlFor="cli-direccion" className="block text-sm font-medium text-text-muted">Dirección</label>
            <Input
              id="cli-direccion"
              placeholder="Av. Ejemplo 123"
              value={formData.direccion}
              onChange={(e) => setFormData({ ...formData, direccion: e.target.value })}
            />
          </div>
          <div className="flex gap-3 pt-2">
            <Button type="submit" className="flex-1" disabled={saving} id="btn-guardar-cliente">
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
