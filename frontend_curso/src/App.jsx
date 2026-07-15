import { lazy, Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import AppLayout from './components/layout/AppLayout';

// Lazy loading: cada vista se carga solo cuando el usuario navega a ella
const DashboardView       = lazy(() => import('./views/DashboardView'));
const ProductsView        = lazy(() => import('./views/ProductsView'));
const CategoriasView      = lazy(() => import('./views/CategoriasView'));
const ClientesView        = lazy(() => import('./views/ClientesView'));
const VentasView          = lazy(() => import('./views/VentasView'));
const MovimientosView     = lazy(() => import('./views/MovimientosView'));
const TipoComprobantesView = lazy(() => import('./views/TipoComprobantesView'));

function PageLoader() {
  return (
    <div className="flex items-center justify-center min-h-[40vh] text-text-muted">
      <div className="flex flex-col items-center gap-3">
        <div className="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin" />
        <span className="text-sm">Cargando...</span>
      </div>
    </div>
  );
}

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<AppLayout />}>
          <Route index element={
            <Suspense fallback={<PageLoader />}><DashboardView /></Suspense>
          } />
          <Route path="productos" element={
            <Suspense fallback={<PageLoader />}><ProductsView /></Suspense>
          } />
          <Route path="categorias" element={
            <Suspense fallback={<PageLoader />}><CategoriasView /></Suspense>
          } />
          <Route path="clientes" element={
            <Suspense fallback={<PageLoader />}><ClientesView /></Suspense>
          } />
          <Route path="ventas" element={
            <Suspense fallback={<PageLoader />}><VentasView /></Suspense>
          } />
          <Route path="comprobantes" element={
            <Suspense fallback={<PageLoader />}><TipoComprobantesView /></Suspense>
          } />
          <Route path="movimientos" element={
            <Suspense fallback={<PageLoader />}><MovimientosView /></Suspense>
          } />
        </Route>
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Router>
  );
}

export default App;
