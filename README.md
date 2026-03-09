# HPCars - Sistema de Gestión Interna de Alquiler de Coches

HPCars es un sistema de gestión interna diseñado específicamente para una empresa de alquiler de coches con una flota de aproximadamente 15 vehículos. El objetivo principal de la aplicación es centralizar las operaciones diarias, reducir los errores manuales y optimizar el tiempo de gestión.

## 🚀 Características Principales

*   **Gestión de Flota:** 
    *   Registro de vehículos (matrícula, modelo, año, estado).
    *   Control de disponibilidad (libre, alquilado, en mantenimiento).
    *   Seguimiento de kilometraje en tiempo real.
    *   Alertas de ITV (Inspección Técnica de Vehículos) y mantenimientos programados.
*   **Gestión de Clientes:** 
    *   Base de datos centralizada de clientes y conductores.
    *   Datos básicos de contacto e historial de alquileres por cliente.
*   **Gestión de Reservas:** 
    *   Registro de fechas, vehículo asignado, cliente y estado del alquiler.
    *   Seguimiento de estado (activa, completada, cancelada).
    *   Historial detallado que cruza clientes y vehículos.
*   **Generación de Contratos:** 
    *   Creación automática de contratos genéricos en formato PDF listos para firmar.
    *   Detalle de condiciones comerciales, datos del cliente, vehículo y fechas.
*   **Seguridad:**
    *   Autenticación de usuarios mediante Laravel Fortify.
    *   Soporte nativo para Autenticación de Dos Factores (2FA - TOTP).

## 🛠️ Stack Tecnológico

El proyecto está construido sobre las herramientas más modernas del ecosistema PHP/Laravel para garantizar rendimiento, seguridad y una excelente experiencia de usuario:

*   **Framework Core:** Laravel 12 (con su nueva estructura de directorios simplificada)
*   **Frontend Reactivo:** Livewire 4, Flux UI Free (v2), Tailwind CSS 4, Vite 7
*   **Autenticación:** Laravel Fortify
*   **Base de Datos:** SQLite (configurable a MySQL/PostgreSQL sin gran fricción a través del `.env`)
*   **Testing:** PHPUnit 11
*   **Requisitos del Servidor:** PHP 8.3.14+

## ⚙️ Estructura del Proyecto

El proyecto sigue una estructura limpia de Laravel 12 optimizada para el stack TALL/Livewire:

*   `app/Models/`: Modelos de dominio principales (ej. `Vehicle`, `Client`, `Reservation`, `Contract`, `Notification`).
*   `app/Actions/`: Acciones dedicadas a los procesos de autenticación manejados por Fortify.
*   `app/Livewire/Actions/`: Clases de acción exclusivas para componentes de Livewire.
*   `resources/views/pages/`: Componentes full-page basados en Livewire (identificados convencionalmente en las rutas mediante ⚡).
*   `resources/views/components/` y `layouts/`: Componentes modulares Blade y plantillas de diseño.
*   `routes/`: Rutas web y de consola configuradas declarativamente vía middleware en `bootstrap/app.php`.

## 📦 Instalación y Configuración Local

Sigue estos pasos para levantar el entorno de desarrollo local:

1.  **Clonar y preparar entorno:**
    ```bash
    git clone <url-del-repositorio> hpcars
    cd hpcars
    cp .env.example .env
    ```

2.  **Instalar dependencias clave:**
    ```bash
    composer install
    npm install
    ```

3.  **Generar clave de aplicación y migrar la base de datos:**
    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```

4.  **Levantar el servidor de desarrollo:**
    ```bash
    # Inicia el servidor PHP de Laravel, sistema de colas y Vite de forma concurrente.
    composer run dev
    ```
    
    *Nota para entornos de producción:* Usa el comando `npm run build` para compilar estáticamente los recursos del frontend.

## 🧪 Pruebas (Testing)

El sistema está cubierto principalmente por pruebas de características (`Feature Tests`), empleando una base de datos SQLite en memoria (`DB_DATABASE=:memory:`) para asegurar velocidad en la ejecución.

Comandos para la ejecución de pruebas:

```bash
# Ejecutar toda la suite de pruebas
php artisan test --compact

# Ejecutar pruebas apuntando a un archivo específico
php artisan test --compact tests/Feature/ExampleTest.php

# Ejecutar una prueba específica por nombre (muy útil durante el desarrollo)
php artisan test --compact --filter=NombreDeLaPrueba
```

Las validaciones de negocio e interacciones con base de datos se manejan a través de factorías de Laravel propias de cada modelo.

## ✒️ Estándares y Convenciones

*   **Formateo de Código:** Todo el código PHP transita por Laravel Pint. Antes de enviar un *commit*, verifica el formato ejecutando:
    ```bash
    vendor/bin/pint --dirty
    ```
*   **Reglas y mejores prácticas aplicadas en el código:**
    *   Uso constante de *Constructor Property Promotion* incorporado desde PHP 8.
    *   Tipado estricto y explícito: Definición de tipos de variables, propiedades y valores de retorno en todos los métodos.
    *   Nomenclatura y metodologías claras e idiomáticas (ej., evitar `is_ok()`, en beneficio de nombres descriptivos como `isRegisteredForDiscounts()`).
    *   Uso de Enum(s) con `TitleCase` para constantes de negocio (ej. estados de vehículos, roles).
    *   Cargas *Eager Loading* de modelos para evitar incidencias de tipo n+1 query en base de datos.
