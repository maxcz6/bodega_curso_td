# API - Servicios Web

Esta es una API sencilla desarrollada en Laravel, diseñada específicamente para ser amigable con principiantes. Su propósito es servir la información completa del sílabo del curso "Servicios Web".

---

##  Lógica del Negocio

La lógica de negocio de esta API es directa y minimalista. En lugar de crear un sistema complejo con múltiples tablas relacionales (por ejemplo, separar semanas, temas, objetivos y sesiones en tablas distintas), se optó por una arquitectura de **Tabla Única (`sesiones`)**. 

**¿Por qué?**
Para que un principiante pueda entender fácilmente el flujo de los datos (Petición -> Ruta -> Controlador -> Modelo -> Base de Datos). 

**¿Cómo funciona?**
Toda la información correspondiente a una semana de clases (su identificador como "S1", las fechas de dictado, el indicador de logro, los contenidos/temas a dictar y el nombre de la sesión) se encapsula en un único registro. 
Cuando una aplicación Front-End (o un usuario) hace una petición `GET` a `/api/sesiones`, la API simplemente va a la base de datos, extrae todos los registros de esta tabla y los devuelve estructurados en formato JSON. Si se hace un `POST`, recibe el JSON, lo valida mínimamente y lo inserta como una nueva fila.

---

##  Archivos Creados y los Más Importantes

Todo el código original y complejo de Laravel ya viene pre-instalado, pero para que esta API funcione, se han creado y configurado 5 archivos fundamentales. **Estos son los que debes estudiar para entender el proyecto:**

1. **El Modelo** (`app/Models/Sesion.php`)
   * **Importancia:** Alta. Representa la tabla `sesiones` en la base de datos.
   * **¿Qué hace?** Aquí se define la propiedad `$fillable` (los campos que permitimos guardar: semana, fecha, indicador_logro, etc.). Es el "molde" de nuestros datos.

2. **El Controlador** (`app/Http/Controllers/SesionController.php`)
   * **Importancia:** Muy Alta. Es el cerebro de la API.
   * **¿Qué hace?** Contiene la lógica de qué hacer cuando alguien llama a la API. Tiene métodos explicados paso a paso para listar (`index`), guardar (`store`), buscar (`show`), actualizar (`update`) y eliminar (`destroy`) sesiones.

3. **Las Rutas** (`routes/api.php`)
   * **Importancia:** Alta. 
   * **¿Qué hace?** Son las "puertas de entrada". Aquí le decimos a Laravel: *"Si alguien entra a la URL `/api/sesiones` con el método GET, envíalo a la función `index` del `SesionController`"*.

4. **La Migración** (`database/migrations/..._create_sesions_table.php`)
   * **Importancia:** Media (se usa más que todo al instalar).
   * **¿Qué hace?** Define la estructura de la base de datos (crea las columnas `semana`, `fecha`, `contenidos` con tipo `string` o `text`).

5. **El Poblador / Seeder** (`database/seeders/SesionSeeder.php`)
   * **Importancia:** Alta para pruebas.
   * **¿Qué hace?** Es un archivo que contiene las 16 semanas del sílabo transcritas en código. Al ejecutarlo, llena la base de datos automáticamente para que no tengas que ingresar el temario a mano.

---

##  ¿Cómo iniciar la API una vez descargada de GitHub?

Si acabas de clonar o descargar este proyecto de GitHub, sigue estos pasos para hacerlo funcionar en tu computadora:

### Paso 1: Abrir la terminal
Abre CMD, PowerShell o la terminal de tu editor de código (como VS Code) y navega hasta la carpeta del proyecto:
```bash
cd ruta/hacia/el/proyecto/api_curso
```

### Paso 2: Instalar dependencias
Laravel requiere descargar las librerías de terceros (Vendor) para funcionar. Ejecuta:
```bash
composer install
```

### Paso 3: Configurar el entorno
Laravel usa un archivo `.env` para las configuraciones locales. Haz una copia del archivo de ejemplo:
```bash
cp .env.example .env
```
*(Si usas Windows CMD y `cp` no funciona, usa `copy .env.example .env`)*

### Paso 4: Generar la llave de la aplicación
Por seguridad, Laravel necesita una clave única. Ejecuta:
```bash
php artisan key:generate
```

### Paso 5: Crear la base de datos y llenarla de datos (El Sílabo)
Laravel 11 usa SQLite por defecto, por lo que no necesitas XAMPP ni MySQL. Este comando creará la base de datos, las tablas y además **insertará las 16 semanas de clases automáticamente**.
```bash
php artisan migrate:fresh --seed
```

### Paso 6: Levantar el servidor
Finalmente, enciende la API:
```bash
php artisan serve
```
 `http://localhost:8000/api/sesiones`

---

##  Datos Incluidos: Programación de Contenidos y Sesiones

Al ejecutar el comando de "semillas" (`--seed` en el paso 5), la base de datos se poblará exactamente con esta información:

* **S1 (08 y 10/04/2026)**: Introducción a los Servicios web. (Evaluación diagnóstica, Definición, Características, Ventajas y desventajas, Buenas prácticas).
* **S2 (15 y 17/04/2026)**: Componentes de servicios web. (Proveedor, Solicitante, Corredor, Actores).
* **S3 (22 y 24/04/2026)**: Arquitectura y tipos de servicios web. (Estándares y Tipos).
* **S4 (29 y 01/05/2026)**: Requerimiento de software e instalación. (Novedades de Visual Studio, requerimientos, descarga e instalación).
* **S5 (06 y 08/05/2026)**: Software para servicios web. (Lenguaje .NET, interfaz de Visual Studio, primeros pasos).
* **S6 (13 y 15/05/2026)**: Servicios web. (Elementos, Método de página, Configuración básica).
* **S7 (20 y 22/05/2026)**: Operaciones del servicio web. (Expresiones en C#, fórmulas, conversión de variables, apuntadores).
* **S8 (28 y 29/05/2026)**: Formularios de escritorio. (Windows Forms, Controles avanzados).
* **S9 (03 y 05/06/2026)**: Estructuras de control condicionales. (if, if else, if else if, switch).
* **S10 (10 y 12/06/2026)**: Estructuras de control repetitivas. (while, do while, for, foreach).
* **S11 (17 y 19/06/2026)**: Formulario para la web. (Páginas ASP .NET, controles web).
* **S12 (24 y 26/06/2026)**: API REST. (Qué es, Principios, Buenas prácticas, Términos básicos).
* **S13 (01 y 03/07/2026)**: Consumo de servicios web API. (Conexión a BD, casos de uso).
* **S14 (08 y 10/07/2026)**: Pruebas de servicios web. (Protocolo y cómo probar).
* **S15 (15 y 17/07/2026)**: Certificación de servicios web. (Condiciones, Entradas/Salidas, Dimensionamiento).
* **S16 (22 y 24/07/2026)**: Proyecto de servicios y consumo web. (Presentación de producto y manejo de errores).
