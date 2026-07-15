import { Outlet, NavLink } from 'react-router-dom';
import {
  LayoutDashboard,
  Package,
  FolderOpen,
  Users,
  ShoppingCart,
  ArrowUpDown,
  FileText,
} from 'lucide-react';

// Ítems del menú — sin Usuarios ni Sesiones
const NAV_ITEMS = [
  { name: 'Inicio',       path: '/',            icon: LayoutDashboard, end: true },
  { name: 'Productos',    path: '/productos',   icon: Package },
  { name: 'Categorías',  path: '/categorias',  icon: FolderOpen },
  { name: 'Clientes',    path: '/clientes',    icon: Users },
  { name: 'Ventas',      path: '/ventas',      icon: ShoppingCart },
  { name: 'Comprobantes', path: '/comprobantes', icon: FileText },
  { name: 'Movimientos', path: '/movimientos', icon: ArrowUpDown },
];

export default function AppLayout() {
  return (
    <div className="min-h-screen flex flex-col md:flex-row bg-background text-text">

      {/* ── Sidebar Desktop ── */}
      <aside className="hidden md:flex flex-col w-64 glass-panel border-r border-border/50 sticky top-0 h-screen overflow-y-auto">
        {/* Branding */}
        <div className="p-6 border-b border-border/30">
          <h1 className="text-2xl font-bold bg-gradient-to-r from-primary to-blue-400 bg-clip-text text-transparent select-none">
            BodegaApp
          </h1>
          <p className="text-xs text-text-muted mt-0.5">Sistema de gestión</p>
        </div>

        {/* Navegación */}
        <nav className="flex-1 px-3 py-4 space-y-1" aria-label="Navegación principal">
          {NAV_ITEMS.map((item) => (
            <NavLink
              key={item.name}
              to={item.path}
              end={item.end}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150 ${
                  isActive
                    ? 'bg-primary/15 text-primary font-semibold shadow-sm'
                    : 'text-text-muted hover:text-text hover:bg-surface'
                }`
              }
              aria-label={item.name}
            >
              {({ isActive }) => (
                <>
                  <item.icon
                    size={18}
                    strokeWidth={isActive ? 2.5 : 2}
                    className={isActive ? 'text-primary' : ''}
                  />
                  {item.name}
                  {isActive && (
                    <span className="ml-auto w-1.5 h-1.5 rounded-full bg-primary" aria-hidden="true" />
                  )}
                </>
              )}
            </NavLink>
          ))}
        </nav>

        {/* Footer del sidebar */}
        <div className="p-4 border-t border-border/30">
          <p className="text-xs text-text-muted text-center">v1.0 · Bodega Curso</p>
        </div>
      </aside>

      {/* ── Área de contenido principal ── */}
      <main className="flex-1 overflow-x-hidden pb-20 md:pb-0 min-w-0">

        {/* Header móvil */}
        <header className="md:hidden glass-panel sticky top-0 z-10 px-4 py-3 flex justify-between items-center border-b border-border/50">
          <h1 className="text-xl font-bold bg-gradient-to-r from-primary to-blue-400 bg-clip-text text-transparent">
            BodegaApp
          </h1>
          <span className="text-xs text-text-muted bg-surface px-2 py-0.5 rounded-full">v1.0</span>
        </header>

        {/* Contenido de la ruta actual */}
        <div className="p-4 md:p-8 max-w-4xl mx-auto">
          <div id="main-content" tabIndex={-1} className="animate-in fade-in duration-300">
            <Outlet />
          </div>
        </div>
      </main>

      {/* ── Barra de navegación inferior (Móvil) ── */}
      <nav
        className="md:hidden glass-panel fixed bottom-0 w-full border-t border-border/50 flex justify-around items-center h-16 px-1 z-20"
        aria-label="Navegación móvil"
      >
        {NAV_ITEMS.map((item) => (
          <NavLink
            key={item.name}
            to={item.path}
            end={item.end}
            className={({ isActive }) =>
              `flex flex-col items-center justify-center flex-1 h-full gap-0.5 transition-colors ${
                isActive ? 'text-primary' : 'text-text-muted'
              }`
            }
            aria-label={item.name}
          >
            {({ isActive }) => (
              <>
                <item.icon size={20} strokeWidth={isActive ? 2.5 : 2} />
                <span className="text-[10px] font-medium leading-tight">{item.name}</span>
              </>
            )}
          </NavLink>
        ))}
      </nav>

    </div>
  );
}
