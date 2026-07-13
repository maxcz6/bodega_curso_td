<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SesionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiamos la tabla antes de sembrar para evitar duplicados si corremos el seeder varias veces
        \App\Models\Sesion::truncate();

        $sesiones = [
            [
                'semana' => 'S1',
                'fecha' => "08 y 10/04/2026 (D)\n07, 08 y 10/04/2026 (N)",
                'indicador_logro' => "C1.i1 Reconoce la importancia de los servicios Web según las buenas prácticas de desarrollo de software",
                'contenidos' => "Evaluación diagnóstica\n• Definición de servicio web\n• Características\n• Ventajas y desventajas\n• Buenas prácticas de desarrollo",
                'sesion_aprendizaje' => "Nro. 01 Introducción a los Servicios web",
                'indicador_logro_sesion' => "Identifica y fundamenta conceptos básicos, ventaja y desventajas de servicios web del material de enseñanza."
            ],
            [
                'semana' => 'S2',
                'fecha' => "15 y 17/04/2026 (D)\n14, 15 y 17/04/2026 (N)",
                'indicador_logro' => "", // El temario original no repite el indicador de capacidad/logro si es el mismo, dejaremos en blanco o podemos copiar el de S1 si es necesario.
                'contenidos' => "• Proveedor de servicios web\n• Solicitante de servicios web\n• Corredor de servicios web\n• Cómo funciona un servicio web Actores: Proveedor, solicitante e intermediario.",
                'sesion_aprendizaje' => "Nro 02 Componentes de servicios web",
                'indicador_logro_sesion' => "Describe los componentes para el servicio web en un organizador de conocimiento."
            ],
            [
                'semana' => 'S3',
                'fecha' => "22 y 24/04/2026 (D)\n21, 22 y 24/04/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Definiciones\n• Arquitectura de servicios web\n• Estándares de servicios web\n• Tipos de servicios web.",
                'sesion_aprendizaje' => "Nro 03 Arquitectura y tipos de servicios web",
                'indicador_logro_sesion' => "Define las arquitecturas y tipos de servicio web, del material de enseñanza."
            ],
            [
                'semana' => 'S4',
                'fecha' => "29 y 01/04/2026 (D)\n28, 29 y 01/05/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Herramientas a utilizar para servicios web\n• Novedades y versiones de Visual Studio\n• Requerimientos mínimos para la instalación de sistema\n• Descarga e instalación del software.",
                'sesion_aprendizaje' => "Nro. 04 Requerimiento de software e instalación",
                'indicador_logro_sesion' => "Instala las herramientas .NET en las computadoras de acuerdo a los requerimientos necesarios."
            ],
            [
                'semana' => 'S5',
                'fecha' => "06 y 08/05/2026 (D)\n05, 06 y 08/05/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Definiciones\n• Lenguaje de programación en .NET\n• Interfaz de la pantalla de inicio de Visual Studio\n• Interfaz principal de Visual Studio\n• Consejos y primeros pasos para aprender a programar",
                'sesion_aprendizaje' => "Nro 05 Software para servicios web",
                'indicador_logro_sesion' => "Explora el entorno de trabajo de las .NET y realiza un servicio web, de la guía de laboratorio."
            ],
            [
                'semana' => 'S6',
                'fecha' => "13 y 15/05/2026 (D)\n12, 13 y 15/05/2026 (N)",
                'indicador_logro' => "C1.i2 Crea servicios Web de acuerdo a los casos de uso y las buenas prácticas de gestión del ciclo de vida de desarrollo.",
                'contenidos' => "• Elementos para crear un servicio web\n• Método de página\n• Creación y configuración para un servicio web básico\n• Elementos para la codificación.",
                'sesion_aprendizaje' => "Nro 06 Servicios web",
                'indicador_logro_sesion' => "Identifica elementos, métodos, configuración para la creación de servicios web, del material de enseñanza."
            ],
            [
                'semana' => 'S7',
                'fecha' => "20 y 22/05/2026 (D)\n19, 20 y 22/05/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Expresiones en C#\n• Fórmulas para crear servicios web\n• Conversión de variables\n• Símbolos en gramática y apuntadores",
                'sesion_aprendizaje' => "Nro 07 Operaciones del servicio web",
                'indicador_logro_sesion' => "Usa expresiones, fórmulas, conversiones y símbolos gramáticas, para realizar operaciones de servicios web."
            ],
            [
                'semana' => 'S8',
                'fecha' => "28 y 29/05/2026 (D)\n26, 27 y 29/05/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Formulario - Windows Forms\n• Elementos básicos para crear formularios de escritorio\n• Controles avanzados",
                'sesion_aprendizaje' => "Nro 08 Formularios de escritorio",
                'indicador_logro_sesion' => "Describe elementos para crear formularios utilizando controles avanzados del material de enseñanza."
            ],
            [
                'semana' => 'S9',
                'fecha' => "03 y 05/06/2026 (D)\n02, 03 y 05/06/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Estructura de control condicional if e if else\n• Estructura de control condicional if else if\n• Estructura de control condicional switch",
                'sesion_aprendizaje' => "Nro 09 Estructuras de control condicionales",
                'indicador_logro_sesion' => "Diseña formularios con estructuras de control condicionales, según casos de uso propuestos."
            ],
            [
                'semana' => 'S10',
                'fecha' => "10 y 12/06/2026 (D)\n09, 10 y 12/06/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Estructura de control repetitivo while\n• Estructura de control repetitivo do while\n• Estructura de control repetitivo for\n• Estructura de control repetitivo for each.",
                'sesion_aprendizaje' => "Nro 10 Estructuras de control repetitivas",
                'indicador_logro_sesion' => "Resuelve casos utilizando estructuras de control repetitivos."
            ],
            [
                'semana' => 'S11',
                'fecha' => "17 y 19/06/2026 (D)\n16, 17 y 19/06/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Formularios para la web\n• Creación de páginas ASP .NET\n• Controles web\n• Tipos de controles\n• Ejecución de servicios\n• Aplicaciones de casos de uso y buenas prácticas de gestión.",
                'sesion_aprendizaje' => "Nro 11 Formulario para la web",
                'indicador_logro_sesion' => "Crea formularios utilizando controles web en ASP.NET."
            ],
            [
                'semana' => 'S12',
                'fecha' => "24 y 26/06/2026 (D)\n23, 24 y 26/06/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Qué es API Rest\n• Qué es una API Web\n• Principios de diseño de Rest\n• Buenas prácticas de API Rest\n• Términos básicos de API Rest",
                'sesion_aprendizaje' => "Nro 12 API REST",
                'indicador_logro_sesion' => "Diferencia las APIs por su utilidad para diseñar servicios web."
            ],
            [
                'semana' => 'S13',
                'fecha' => "01 y 03/07/2026 (D)\n30, 01 y 03/07/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Consumo de servicios web\n• Servicios web con conexión a base de datos\n• Aplicaciones de casos de uso y buenas prácticas.",
                'sesion_aprendizaje' => "Nro 13 Consumo de servicios web API",
                'indicador_logro_sesion' => "Crea servicios web para consumir, mediante consultas a la base de datos."
            ],
            [
                'semana' => 'S14',
                'fecha' => "08 y 10/07/2026 (D)\n07, 08 y 10/07/2026 (N)",
                'indicador_logro' => "C1.i3 Realiza las pruebas y certificación de los servicios web de acuerdo a las buenas prácticas de gestión de la configuración del software.",
                'contenidos' => "• Qué son las pruebas de servicios web\n• Protocolo de servicios web\n• Cómo probar un servicio web",
                'sesion_aprendizaje' => "Nro. 14 Pruebas de servicios web",
                'indicador_logro_sesion' => "Realiza pruebas de servicios web, a la aplicación web."
            ],
            [
                'semana' => 'S15',
                'fecha' => "15 y 17/07/2026 (D)\n14, 15 y 17/07/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Descripción funcional\n• Condiciones de servicios\n• Actores implicados\n• Entradas / salidas\n• Dimensionamiento",
                'sesion_aprendizaje' => "Nro. 15 Certificación de servicios web",
                'indicador_logro_sesion' => "Verifica y valida servicios web para su ejecución de las pruebas de rendimiento y elaboración del informe de resultados."
            ],
            [
                'semana' => 'S16',
                'fecha' => "22 y 24/07/2026 (D)\n21, 22 y 24/07/2026 (N)",
                'indicador_logro' => "",
                'contenidos' => "• Introducción al proyecto.\n• Métodos y operaciones HTTP\n• Consumo de servicios web.\n• Manejo de errores.\n• Presentación de producto de la Unidad Didáctica.",
                'sesion_aprendizaje' => "Nro. 16 Proyecto de servicios y consumo web",
                'indicador_logro_sesion' => "Desarrolla proyectos de servicios web, para su consumo previo a la prueba y certificación respectiva."
            ]
        ];

        // Insertamos en la BD
        foreach ($sesiones as $s) {
            \App\Models\Sesion::create($s);
        }
    }
}
