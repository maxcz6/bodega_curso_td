import React from 'react';
import { AlertTriangle, RefreshCcw } from 'lucide-react';
import { Button } from './ui/Button';

export class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    // Actualiza el estado para que el siguiente renderizado muestre la UI de repuesto
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    // Aquí puedes registrar el error en un servicio de reporte de errores (ej. Sentry)
    console.error("ErrorBoundary atrapó un error:", error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      // Puedes renderizar cualquier UI de repuesto
      return (
        <div className="flex flex-col items-center justify-center min-h-[50vh] p-6 text-center animate-in fade-in zoom-in-95 duration-300">
          <div className="w-16 h-16 bg-red-400/20 text-red-400 rounded-2xl flex items-center justify-center mb-6">
            <AlertTriangle size={32} />
          </div>
          <h2 className="text-2xl font-bold mb-2">¡Ups! Algo salió mal</h2>
          <p className="text-text-muted max-w-md mb-6">
            Ha ocurrido un error inesperado al cargar esta sección. Hemos registrado el problema para solucionarlo.
          </p>
          <div className="bg-surface p-4 rounded-lg border border-border/30 max-w-lg w-full text-left overflow-auto mb-6">
            <code className="text-xs text-red-400 font-mono">
              {this.state.error?.toString() || 'Error desconocido'}
            </code>
          </div>
          <Button 
            onClick={() => window.location.reload()}
            className="gap-2"
          >
            <RefreshCcw size={16} />
            Recargar Página
          </Button>
        </div>
      );
    }

    return this.props.children; 
  }
}
