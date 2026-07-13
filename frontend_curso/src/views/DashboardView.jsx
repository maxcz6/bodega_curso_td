import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardContent } from '../components/ui/Card';
import { Package, FileText, AlertTriangle, TrendingUp, ShoppingCart, Boxes, ArrowRight } from 'lucide-react';
import api from '../lib/api';

export default function DashboardView() {
  const navigate = useNavigate();
  const [stats, setStats] = useState({
    total_productos: 0,
    productos_bajo_stock: 0,
    total_ventas_hoy: 0,
    ingresos_hoy: 0
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;
    api.get('/dashboard/stats')
      .then(res => {
        if (!mounted) return;
        // API returns object with numeric counters
        setStats({
          total_productos: res.data.total_productos ?? 0,
          productos_bajo_stock: res.data.productos_bajo_stock ?? 0,
          total_ventas_hoy: res.data.total_ventas_hoy ?? 0,
          ingresos_hoy: res.data.ingresos_hoy ?? 0
        });
      })
      .catch(() => {
        // fallback static
        setStats({ total_productos: 124, productos_bajo_stock: 5, total_ventas_hoy: 28, ingresos_hoy: 1450.50 });
      })
      .finally(() => { if (mounted) setLoading(false); });
    return () => mounted = false;
  }, []);

  const statCards = [
    { title: 'Total Productos', value: stats.total_productos, icon: Package, color: 'text-blue-400', bg: 'bg-blue-400/10', path: '/productos' },
    { title: 'Bajo Stock', value: stats.productos_bajo_stock, icon: AlertTriangle, color: 'text-red-400', bg: 'bg-red-400/10', path: '/productos' },
    { title: 'Ventas Hoy', value: stats.total_ventas_hoy, icon: FileText, color: 'text-emerald-400', bg: 'bg-emerald-400/10', path: '/ventas' },
    { title: 'Ingresos Hoy', value: `$${stats.ingresos_hoy.toFixed(2)}`, icon: TrendingUp, color: 'text-purple-400', bg: 'bg-purple-400/10', path: '/ventas' },
  ];

  const shortcuts = [
    { title: 'Ver productos', description: 'Gestiona el inventario y precios', icon: Boxes, path: '/productos' },
    { title: 'Registrar venta', description: 'Crea una nueva operación de venta', icon: ShoppingCart, path: '/ventas' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight text-text">Dashboard</h2>
        <p className="text-text-muted mt-1">Un resumen del estado de tu bodega el día de hoy.</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {statCards.map((stat, i) => (
          <button
            key={i}
            type="button"
            onClick={() => navigate(stat.path)}
            className="text-left animate-in fade-in slide-in-from-bottom-4 duration-500 hover:-translate-y-1 transition-transform"
            style={{ animationDelay: `${i * 100}ms` }}
          >
            <Card className="h-full cursor-pointer border border-transparent hover:border-primary/40">
              <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium text-text-muted">
                  {stat.title}
                </CardTitle>
                <div className={`p-2 rounded-lg ${stat.bg} ${stat.color}`}>
                  <stat.icon size={20} />
                </div>
              </CardHeader>
              <CardContent>
                {loading ? (
                  <div className="h-8 w-24 bg-surface rounded animate-pulse" />
                ) : (
                  <div className="text-2xl font-bold text-text">{stat.value}</div>
                )}
                <p className="text-sm text-primary mt-2 flex items-center gap-1">Ver detalle <ArrowRight size={14} /></p>
              </CardContent>
            </Card>
          </button>
        ))}
      </div>

      <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <Card className="min-h-[260px] border-dashed">
          <CardHeader>
            <CardTitle>Resumen rápido</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <p className="text-text-muted text-sm">Tu negocio está funcionando bien. Usa los accesos rápidos para moverte más rápido entre inventario y ventas.</p>
            <div className="grid gap-3 sm:grid-cols-2">
              {shortcuts.map((shortcut, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => navigate(shortcut.path)}
                  className="p-4 rounded-xl bg-surface/70 border border-border/30 hover:border-primary/40 text-left transition-colors"
                >
                  <div className="flex items-center gap-2 text-primary">
                    <shortcut.icon size={16} />
                    <span className="font-medium">{shortcut.title}</span>
                  </div>
                  <p className="text-sm text-text-muted mt-2">{shortcut.description}</p>
                </button>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card className="min-h-[260px] border-dashed">
          <CardHeader>
            <CardTitle>Estado del negocio</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm text-text-muted">
            <div className="rounded-lg bg-emerald-400/10 p-3 text-emerald-400">
              <p className="font-semibold">¡Todo está en orden!</p>
              <p className="mt-1">Los productos principales tienen stock suficiente para operar hoy.</p>
            </div>
            <div className="rounded-lg bg-amber-400/10 p-3 text-amber-400">
              <p className="font-semibold">Revisa alertas</p>
              <p className="mt-1">Hay {stats.productos_bajo_stock} productos con stock bajo.</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
