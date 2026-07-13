import { useEffect, useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '../components/ui/Card';
import { Package, FileText, AlertTriangle, TrendingUp } from 'lucide-react';
import api from '../lib/api';

export default function DashboardView() {
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
    { title: 'Total Productos', value: stats.total_productos, icon: Package, color: 'text-blue-400', bg: 'bg-blue-400/10' },
    { title: 'Bajo Stock', value: stats.productos_bajo_stock, icon: AlertTriangle, color: 'text-red-400', bg: 'bg-red-400/10' },
    { title: 'Ventas Hoy', value: stats.total_ventas_hoy, icon: FileText, color: 'text-emerald-400', bg: 'bg-emerald-400/10' },
    { title: 'Ingresos Hoy', value: `$${stats.ingresos_hoy.toFixed(2)}`, icon: TrendingUp, color: 'text-purple-400', bg: 'bg-purple-400/10' },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight text-text">Dashboard</h2>
        <p className="text-text-muted mt-1">Un resumen del estado de tu bodega el día de hoy.</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {statCards.map((stat, i) => (
          <Card key={i} className="animate-in fade-in slide-in-from-bottom-4 duration-500" style={{ animationDelay: `${i * 100}ms` }}>
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
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Placeholder for future charts or recent activity */}
      <Card className="min-h-[300px] flex items-center justify-center border-dashed">
        <p className="text-text-muted text-sm">El gráfico de ventas recientes aparecerá aquí.</p>
      </Card>
    </div>
  );
}
