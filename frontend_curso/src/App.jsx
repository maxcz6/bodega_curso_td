import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import AppLayout from './components/layout/AppLayout';
import DashboardView from './views/DashboardView';
import ProductsView from './views/ProductsView';
import CategoriasView from './views/CategoriasView';
import ClientesView from './views/ClientesView';
import VentasView from './views/VentasView';
import MovimientosView from './views/MovimientosView';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<AppLayout />}>
          <Route index element={<DashboardView />} />
          <Route path="productos" element={<ProductsView />} />
          <Route path="categorias" element={<CategoriasView />} />
          <Route path="clientes" element={<ClientesView />} />
          <Route path="ventas" element={<VentasView />} />
          <Route path="movimientos" element={<MovimientosView />} />
        </Route>
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Router>
  );
}

export default App;
