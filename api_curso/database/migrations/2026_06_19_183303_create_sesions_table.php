<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesions', function (Blueprint $table) {
            $table->id();
            // Identificador de la semana, por ejemplo: "S1", "S2"
            $table->string('semana');
            // Fechas de la semana, por ejemplo: "08 y 10/04/2026 (D)..."
            $table->string('fecha');
            // Indicador de logro general de la semana
            $table->text('indicador_logro')->nullable();
            // Contenidos (Temas que se van a dictar)
            $table->text('contenidos')->nullable();
            // Número y Título de la sesión (Ej: "Nro. 01 Introducción a los Servicios web")
            $table->string('sesion_aprendizaje')->nullable();
            // Indicador de logro específico de la sesión
            $table->text('indicador_logro_sesion')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesions');
    }
};
