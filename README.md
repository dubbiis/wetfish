# WetFish

TPV (Punto de Venta) y sistema de gestión de stock para tienda de acuariofilia. Aplicación web mobile-first con interfaz oscura glassmorphism, pensada para usarse desde el móvil como PWA.

## Funcionalidades

### Punto de Venta (TPV)
- Búsqueda rápida de productos por nombre o código
- Filtro por categorías
- Carrito con control de cantidades (+/-)
- Descuentos por porcentaje o importe fijo
- Cálculo automático de IVA configurable
- Generación de ticket con descuento automático de stock
- Tras completar venta, redirección automática al historial de tickets
- Accesible para admin y empleados

### Dashboard (Admin)
- Ingresos totales por periodo (hoy, semana, mes)
- Beneficio neto (ingresos - compras - servicios - gastos operativos)
- **Margen real vs margen objetivo** — calcula el coste real por producto (precio de compra + gastos operativos / unidades en stock)
- Barra de progreso visual del margen (verde/ámbar/rojo según proximidad al objetivo)
- **Sugerencia de hora pico** — cuando detecta hora punta y el margen está por debajo del objetivo, sugiere ajustar precios con enlace directo a Configuración
- Número de tickets y ticket promedio
- Top 5 productos por cantidad y por ingresos
- Ventas por categoría con barras visuales
- Costes vs Ingresos desglosados
- Gastos operativos por categoría
- Inventario: valor total, stock crítico, productos sin movimiento
- Actividad: hora punta, mejor día, último ticket

### Gestión de Stock (Admin)
- Listado de productos con búsqueda y paginación
- Filtros por categoría y estado de stock (ok, bajo, crítico)
- Indicadores visuales de estado (verde, ámbar, rojo)
- Crear/editar productos con foto, precios, stock mínimo
- Margen automático configurable sobre precio de coste
- Botón FAB (+) para crear nuevo producto

### Historial de Tickets (Admin)
- Listado de ventas por periodo (hoy, semana, mes, personalizado, todo)
- Total de ingresos y conteo de tickets del periodo
- Detalle de cada ticket en modal: datos empresa, productos, cantidades, descuentos, IVA, total
- Búsqueda por número de ticket o vendedor
- Selección múltiple de tickets (individual o todos)
- Exportar tickets seleccionados a PDF (formato recibo de caja)
- Botón "Descargar PDF" en detalle del ticket individual

### Tareas (Admin a Empleado)
- El admin asigna tareas a empleados concretos desde Configuración (click en empleado)
- Cada tarea tiene título, descripción y fecha límite
- Estados: pendiente, en progreso, completada
- El empleado ve sus tareas y puede cambiar el estado
- Filtros por estado en ambas vistas
- Resumen visual con contadores por estado
- Indicador de tareas vencidas para el empleado

### Gastos Operativos (Admin)
- Gastos manuales por categoría (luz, agua, teléfono, internet, hosting, alquiler, etc.)
- Categorías definidas por el usuario (crear, eliminar desde la sección)
- Filtros por periodo (semana, mes, año, todo)
- Total del periodo visible
- Botón "Entrada de peces" enlaza al importador de facturas
- Los gastos operativos se incluyen en el cálculo de beneficio del dashboard

### Importador de Facturas
- Subida de archivos Excel (.xlsx, .xls, .csv)
- Proceso en 3 pasos: subir archivo, datos factura, revisar productos
- Matching automático de productos existentes por código o nombre
- Creación automática de productos nuevos con margen configurado
- Actualización de stock y precios de coste de productos existentes
- Soporte para facturas de compra y de servicio
- Registro de costes extra (transporte, etc.)

### Configuración (Admin)
- Datos del negocio (nombre, CIF, dirección, teléfono, email)
- IVA configurable
- Porcentaje de margen automático
- **Margen y coste real** — margen objetivo configurable, período de cálculo de gastos (mes, 3 meses, 6 meses), información de coste operativo por unidad
- **Ajuste dinámico de precios** — aplicar un porcentaje global a todos los precios de venta, con vista previa de productos, revertir a precios originales
- Gestión de empleados (crear, eliminar, click para gestionar tareas)
- Cierre de sesión

## Roles

| Rol | Acceso |
|-----|--------|
| **admin** | Dashboard, Stock, Tickets, Gastos, Configuración, Tareas empleados, TPV, Facturas |
| **employee** | TPV, Mis Tareas, Importador de facturas |

## Stack Técnico

| Tecnología | Versión | Uso |
|-----------|---------|-----|
| PHP | 8.4 | Backend |
| Laravel | 12 | Framework |
| Livewire | 4 | Componentes reactivos full-stack |
| Alpine.js | 3 | Interactividad frontend (inyectado por Livewire) |
| Tailwind CSS | 3 | Estilos |
| Vite | 7 | Build de assets |
| Laravel Breeze | 2 | Autenticación |
| PhpSpreadsheet | 5 | Lectura de Excel para importar facturas |
| DomPDF | 3 | Generación de tickets en PDF |
| MySQL | 8 | Base de datos en producción |
| Docker | PHP 8.4-cli | Deploy en EasyPanel |

## Modelo de Datos

```
User (admin | employee)
   ├── Ticket → TicketItem → Product
   └── Task (assigned_to / created_by)

Category → Product

Supplier → Invoice (purchase | service)
              └── InvoiceItem → Product

ExpenseCategory → Expense (gastos operativos manuales)

Setting (key-value: tax_rate, auto_margin_percentage, target_margin_percentage, expense_calculation_period, price_adjustment_*)
```

### Categorías predefinidas
- Peces, Plantas, Accesorios, Peces criadero, Plantas criadero

## Instalación Local

### Requisitos
- PHP 8.4 con extensiones: pdo_mysql, mbstring, zip, gd, bcmath
- Composer
- Node.js 20+
- Git

### Pasos

```bash
git clone https://github.com/dubbiis/wetfish.git
cd wetfish
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Credenciales por defecto
- **Admin:** `admin@wetfish.es` / `password`

### Arrancar desarrollo

```bash
php artisan serve    # Backend en http://localhost:8000
npm run dev          # Vite HMR en paralelo
```

## Deploy

El proyecto se despliega automáticamente en **EasyPanel** vía Dockerfile.

```
git push origin master → GitHub webhook → EasyPanel rebuild → Deploy
```

- **URL:** `https://desarrollos-wetfish.o28eg0.easypanel.host`
- **Document root:** `public_html/` (NO `public/`)
- **Servidor:** `php -S 0.0.0.0:8080` con `router.php`
- **Puerto EasyPanel:** 8080
- **BD:** MySQL 8 en EasyPanel

## Estructura del Proyecto

```
wetfish/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                      # Login con redirección por rol
│   │   │   └── TicketExportController.php # Exportar tickets a PDF
│   │   └── Middleware/RoleMiddleware.php   # Control de acceso por rol
│   ├── Livewire/                          # 10 componentes
│   │   ├── Dashboard.php                  # Métricas y stats por periodo
│   │   ├── StockList.php                  # Listado de productos con filtros
│   │   ├── ProductEdit.php                # Crear/editar producto con foto
│   │   ├── TicketHistory.php              # Historial ventas + multi-select + export PDF
│   │   ├── EmployeeTasks.php              # Admin gestiona tareas de un empleado
│   │   ├── MyTasks.php                    # Empleado ve y actualiza sus tareas
│   │   ├── Expenses.php                   # Vista de gastos con filtros
│   │   ├── Settings.php                   # Config negocio + empleados
│   │   ├── PointOfSale.php                # TPV con carrito y checkout
│   │   └── InvoiceImporter.php            # Importar facturas Excel (3 pasos)
│   ├── Models/                            # 10 modelos Eloquent
│   │   ├── User.php                       # Auth + roles (admin|employee)
│   │   ├── Category.php                   # Categorías de productos
│   │   ├── Product.php                    # Stock, precios, margen auto
│   │   ├── Supplier.php                   # Proveedores
│   │   ├── Ticket.php                     # Venta (cabecera)
│   │   ├── TicketItem.php                 # Líneas de venta
│   │   ├── Invoice.php                    # Factura (compra/servicio)
│   │   ├── InvoiceItem.php                # Líneas de factura
│   │   ├── Setting.php                    # Key-value config
│   │   └── Task.php                       # Tareas asignadas a empleados
│   └── Providers/
│       └── AppServiceProvider.php         # CRÍTICO: bind path.public a public_html
├── config/
│   └── dompdf.php                         # CRÍTICO: public_path a public_html
├── database/
│   ├── migrations/                        # 13 migraciones (todas idempotentes)
│   └── seeders/                           # Admin + categorías + settings + productos
├── resources/
│   ├── css/app.css                        # Tailwind + glassmorphism custom classes
│   └── views/
│       ├── layouts/                       # app.blade.php (nav) + guest.blade.php (login)
│       ├── livewire/                      # 10 vistas de componentes
│       ├── pdf/tickets.blade.php          # Plantilla PDF formato recibo
│       ├── components/nav-item.blade.php  # Componente de navegación
│       └── auth/login.blade.php           # Login personalizado
├── routes/
│   ├── web.php                            # Rutas protegidas por rol
│   └── auth.php                           # Auth de Breeze
├── public_html/                           # Document root (NO public/)
│   ├── router.php                         # Router para php -S
│   ├── images/logo.png                    # Logo de la empresa (30KB)
│   └── build/                             # Assets compilados por Vite
├── bootstrap/app.php                      # trustProxies + middleware role
├── Dockerfile                             # PHP 8.4-cli + Node 22 + Composer
├── start.sh                               # Entrypoint: .env, migrate, seed, php -S
├── vite.config.js                         # publicDirectory: public_html
├── tailwind.config.js
├── CLAUDE.md                              # Directrices para desarrollo con IA
└── README.md                              # Este archivo
```

## Licencia

Proyecto privado. Todos los derechos reservados.
