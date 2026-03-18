# WetFish

TPV (Punto de Venta) y sistema de gestion de stock para tienda de acuariofilia. Aplicacion web mobile-first con interfaz oscura glassmorphism, pensada para usarse desde el movil como PWA.

## Funcionalidades

### Punto de Venta (TPV)
- Busqueda rapida de productos por nombre o codigo
- Filtro por categorias
- Carrito con control de cantidades (+/-)
- Descuentos por porcentaje o importe fijo
- Calculo automatico de IVA configurable
- Generacion de ticket con descuento automatico de stock
- Accesible para admin y empleados

### Dashboard (Admin)
- Ingresos totales por periodo (hoy, semana, mes)
- Beneficio neto (ingresos - compras - servicios)
- Numero de tickets y ticket promedio
- Unidades vendidas
- Alerta de productos con stock critico

### Gestion de Stock (Admin)
- Listado de productos con busqueda
- Filtros por categoria y estado de stock (ok, bajo, critico)
- Indicadores visuales de estado (verde, ambar, rojo)
- Crear/editar productos con foto, precios, stock minimo
- Margen automatico configurable sobre precio de coste

### Historial de Tickets (Admin)
- Listado de ventas por periodo
- Total de ingresos del periodo
- Detalle de cada ticket: productos, cantidades, descuentos, IVA, total
- Busqueda por numero de ticket o vendedor

### Gastos (Admin)
- Facturas de compra (proveedores de producto)
- Facturas de servicio (gastos operativos)
- Filtros por tipo y periodo
- Totales separados compras vs servicios
- Acceso rapido al importador de facturas

### Importador de Facturas
- Subida de archivos Excel (.xlsx, .xls, .csv)
- Proceso en 3 pasos: subir → datos factura → revisar productos
- Matching automatico de productos existentes por codigo o nombre
- Creacion automatica de productos nuevos con margen configurado
- Actualizacion de stock y precios de coste de productos existentes
- Soporte para facturas de compra y de servicio
- Registro de costes extra (transporte, etc.)

### Configuracion (Admin)
- Datos del negocio (nombre, CIF, direccion, telefono, email)
- IVA configurable
- Porcentaje de margen automatico
- Gestion de empleados (crear, eliminar)
- Cierre de sesion

## Roles

| Rol | Acceso |
|-----|--------|
| **admin** | Dashboard, Stock, Tickets, Gastos, Configuracion, TPV, Facturas |
| **employee** | TPV, Importador de facturas |

## Stack Tecnico

| Tecnologia | Version | Uso |
|-----------|---------|-----|
| PHP | 8.4 | Backend |
| Laravel | 12 | Framework |
| Livewire | 4 | Componentes reactivos full-stack |
| Alpine.js | 3 | Interactividad frontend (inyectado por Livewire) |
| Tailwind CSS | 3 | Estilos |
| Vite | 7 | Build de assets |
| PhpSpreadsheet | 5 | Lectura de Excel para importar facturas |
| MySQL | 8 | Base de datos en produccion |
| Docker | PHP 8.4-cli | Deploy en EasyPanel |

## Modelo de Datos

```
User (admin | employee)
   └── Ticket → TicketItem → Product

Category → Product

Supplier → Invoice (purchase | service)
              └── InvoiceItem → Product

Setting (key-value)
```

### Categorias predefinidas
- Peces
- Plantas
- Accesorios
- Peces criadero
- Plantas criadero

## Instalacion Local

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

El proyecto se despliega automaticamente en **EasyPanel** via Dockerfile.

```
git push origin master → GitHub webhook → EasyPanel rebuild → Deploy
```

- **URL:** `https://desarrollos-wetfish.o28eg0.easypanel.host`
- **Document root:** `public_html/` (no `public/`)
- **Servidor:** `php -S` con `router.php` para servir estaticos
- **BD:** MySQL 8 en EasyPanel

## Estructura del Proyecto

```
wetfish/
├── app/
│   ├── Http/
│   │   ├── Controllers/Auth/          # Login con redireccion por rol
│   │   └── Middleware/RoleMiddleware   # Control de acceso por rol
│   ├── Livewire/                      # 8 componentes
│   │   ├── Dashboard.php              # Metricas y stats
│   │   ├── StockList.php              # Listado de productos
│   │   ├── ProductEdit.php            # Crear/editar producto
│   │   ├── TicketHistory.php          # Historial de ventas
│   │   ├── Expenses.php               # Vista de gastos
│   │   ├── Settings.php               # Configuracion del negocio
│   │   ├── PointOfSale.php            # TPV
│   │   └── InvoiceImporter.php        # Importar facturas Excel
│   └── Models/                        # 9 modelos Eloquent
│       ├── User.php                   # Auth + roles
│       ├── Category.php
│       ├── Product.php                # Stock, precios, margen auto
│       ├── Supplier.php
│       ├── Ticket.php                 # Venta
│       ├── TicketItem.php
│       ├── Invoice.php                # Factura (compra/servicio)
│       ├── InvoiceItem.php
│       └── Setting.php               # Key-value config
├── database/
│   ├── migrations/                    # 12 migraciones
│   └── seeders/                       # Admin + categorias + settings
├── resources/
│   ├── css/app.css                    # Tailwind + glassmorphism custom
│   └── views/
│       ├── layouts/                   # app.blade.php + guest.blade.php
│       ├── livewire/                  # 8 vistas de componentes
│       └── auth/login.blade.php       # Login personalizado
├── routes/
│   ├── web.php                        # Rutas protegidas por rol
│   └── auth.php                       # Auth de Breeze
├── public_html/                       # Document root
│   ├── router.php                     # Router para php -S
│   └── build/                         # Assets compilados
├── Dockerfile                         # PHP 8.4 + Node 22
├── start.sh                           # Entrypoint del container
├── vite.config.js
├── tailwind.config.js
└── CLAUDE.md                          # Directrices para desarrollo con IA
```

## Licencia

Proyecto privado. Todos los derechos reservados.
