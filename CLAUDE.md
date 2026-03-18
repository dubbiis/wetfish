# WetFish - Directrices del Proyecto

> TPV + gestión de stock + dashboard para tienda de acuariofilia en Córdoba.
> El dueño (Dubi) usa la app desde el móvil como PWA. Interfaz dark glassmorphism, mobile-first.

---

## Reglas Obligatorias

### README siempre actualizado
**Cada vez que se añada, modifique o elimine funcionalidad, rutas, modelos, componentes o configuración, actualizar `README.md` en el mismo commit.** El README es la fuente de verdad del estado actual del proyecto.

### Logs y depuración
**Siempre dejar logs informativos** en scripts, procesos de arranque y puntos críticos del código. En `start.sh` usar `echo`. En PHP usar `Log::info()` o `Log::error()` en puntos clave.

### Idioma
- Código: inglés o español según convención del archivo existente (mezcla actual)
- Commits: **español**, descriptivos
- UI visible al usuario: **español**
- El usuario habla español, responder siempre en español

### Git y Deploy
- **Rama principal:** `master`
- **Remote:** `https://github.com/dubbiis/wetfish.git`
- **Deploy automático:** push a `master` → GitHub webhook → EasyPanel reconstruye Dockerfile
- **URL producción:** `https://desarrollos-wetfish.o28eg0.easypanel.host`
- **Puerto en EasyPanel:** 8080 (CRÍTICO: si se cambia, da 502)
- No hay CI/CD con GitHub Actions. El único pipeline es el webhook.
- No hacer force push a master.

---

## Stack Técnico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | PHP | 8.4 |
| Framework | Laravel | ^12.0 |
| Componentes reactivos | Livewire | ^4.1 |
| JS reactivo | Alpine.js | Inyectado por Livewire (**NO importar manualmente**) |
| CSS | Tailwind CSS | ^3.1.0 |
| Build | Vite | ^7.0.7 |
| Auth | Laravel Breeze | ^2.3 |
| Excel import | PhpSpreadsheet | ^5.5 |
| PDF | barryvdh/laravel-dompdf | ^3.1 |
| Node.js | Node | 22.x (Dockerfile) |
| BD producción | MySQL | 8.x |
| BD local | SQLite | (por defecto) |

---

## Arquitectura

**Full-stack Livewire** — No hay API REST. Toda la lógica vive en:

1. **Componentes Livewire** (`app/Livewire/`) — Estado, validación, acciones
2. **Modelos Eloquent** (`app/Models/`) — Relaciones y lógica de dominio
3. **Vistas Blade** (`resources/views/livewire/`) — UI con directivas `wire:` y `x-`

### Roles
- **admin**: dashboard, stock, tickets, gastos, ajustes, tareas empleados, TPV, facturas
- **employee**: TPV (`/pos`), mis tareas (`/my-tasks`), importador de facturas (`/invoices/import`)

### Middleware de roles
Definido en `app/Http/Middleware/RoleMiddleware.php`, registrado en `bootstrap/app.php` como alias `role`.

---

## Trampas Conocidas (LEER ANTES DE TOCAR)

### 1. Document root es `public_html/`, NO `public/`
Configurado en CUATRO sitios. Si falta alguno, la app no funciona:
- **`app/Providers/AppServiceProvider.php`** → `bind('path.public')` + `usePublicPath()`
- **`vite.config.js`** → `publicDirectory: 'public_html'` + `outDir: 'public_html/build'`
- **`start.sh`** → `php -S ... -t /app/public_html /app/public_html/router.php`
- **`config/dompdf.php`** → `'public_path' => base_path('public_html')`

### 2. Alpine.js
**NUNCA importar Alpine.js manualmente.** Livewire 4 lo inyecta. Importarlo duplica Alpine y rompe `x-data`.

### 3. Vite manifest
Se genera como `manifest.json` (no `.vite/manifest.json`). Configurado en `vite.config.js` con `manifest: 'manifest.json'`.

### 4. Model binding en rutas Livewire
Si un parámetro de ruta se llama igual que un modelo Eloquent (ej: `{product}` + modelo `Product`), Laravel hace model binding automático y puede dar 404. Solución: renombrar el parámetro (ej: `{productId}`). Ejemplo actual: `/stock/{productId}/edit`.

### 5. Migraciones idempotentes
Todas las migraciones custom usan `if (Schema::hasTable('...')) return;` porque la BD en producción puede tener las tablas ya creadas sin registro en `migrations`.

### 6. storage:link NO funciona en Docker
No usar `php artisan storage:link` en `start.sh`. Si se necesitan uploads accesibles, servir desde storage directamente o montar volumen.

### 7. Proxy HTTPS
`trustProxies(at: '*')` en `bootstrap/app.php` para que HTTPS funcione detrás del reverse proxy de EasyPanel. Sin esto, CSRF falla con 419.

### 8. APP_URL sin trailing slash
La variable `APP_URL` NO debe llevar `/` al final. Con trailing slash, CSRF da 419 Page Expired.

### 9. Vistas cacheadas en producción
Tras deploy, las vistas pueden estar cacheadas. `start.sh` ejecuta `config:clear` y `cache:clear`. Si un cambio de vista no se refleja, puede necesitar reinicio del contenedor en EasyPanel.

### 10. SQL compatible MySQL + SQLite
Usar `CASE WHEN` en vez de `FIELD()` para order by custom. `FIELD()` es solo MySQL, `CASE WHEN` funciona en ambos.

---

## Reglas de Desarrollo

### Imágenes estáticas
Van en `public_html/images/`, NUNCA en `public_html/build/assets/` (se borran en cada `npm run build`).

### Proxy y HTTPS
`trustProxies(at: '*')` en `bootstrap/app.php`.

### Storage para uploads
Los uploads de productos (fotos) se guardan en `storage/app/public/products/`.

---

## Diseño UI

### Sistema de diseño: Dark Glassmorphism
- **Color primario:** `#7c3bed` (violet)
- **Fondo:** `#171121` (background-dark)
- **Fuente:** Inter (Google Fonts)
- **Iconos:** Material Symbols Outlined (Google Fonts)
- **Mobile-first**, diseñado para uso desde móvil como PWA

### Clases CSS personalizadas (`resources/css/app.css`)
```
.glass       → fondo sutil con blur, para métricas principales
.glass-card  → tarjetas con tinte primary, para listados y cards
.glass-pill  → pills de filtro
.glass-nav   → header y bottom nav (fondo oscuro con blur)
.glass-dark  → secciones oscuras con borde primary
.radial-glow → fondo del login
.fill-1      → Material Symbols rellenos (icono activo)
.pb-safe     → padding para safe area en móvil
```

### Convenciones de componentes
- **Inputs:** `h-12 px-4 bg-white/5 border border-white/5 rounded-xl focus:ring-1 focus:ring-primary/50 text-slate-100`
- **Botón primario:** `h-12 rounded-xl bg-primary text-white font-semibold shadow-lg shadow-primary/30`
- **Cards:** `glass-card rounded-2xl p-4` o `p-5`
- **Labels:** `text-xs font-bold uppercase tracking-widest text-white/40`
- **Filter pills:** `rounded-full px-5 py-2` con estados activo/inactivo
- **FAB:** `fixed right-6 bottom-28 size-14 rounded-full bg-primary`
- **Modal bottom-sheet:** `fixed inset-0 z-50`, backdrop `bg-black/60 backdrop-blur-sm`, panel `rounded-t-3xl bg-background-dark`

---

## Modelo de Datos

```
User (role: admin|employee)
    ├── Ticket (venta)
    │      └── TicketItem → Product
    └── Task (assigned_to / created_by)

Category → Product (stock, precios, margen auto)

Supplier → Invoice (purchase|service)
              └── InvoiceItem → Product

Setting (key-value: tax_rate, auto_margin_percentage, business_*)
```

### Tablas y campos clave
- **users**: id, name, email, role (enum: admin|employee), password
- **products**: id, code, name, category_id, cost_price, sale_price, stock, min_stock, auto_margin, photo
- **tickets**: id, user_id, subtotal, discount_type, discount_value, tax_rate, tax_amount, total, notes
- **ticket_items**: id, ticket_id, product_id, quantity, unit_price, discount_type, discount_value, subtotal
- **tasks**: id, assigned_to (FK users), created_by (FK users), title, description, due_date, status (enum: pending|in_progress|completed)
- **categories**: id, name, slug, icon
- **suppliers**: id, name, cif, phone, email, address
- **invoices**: id, supplier_id, type (purchase|service), invoice_number, date, subtotal, tax_amount, extra_costs, total, notes
- **invoice_items**: id, invoice_id, product_id, quantity, unit_price, subtotal
- **settings**: id, key (unique), value

---

## Rutas

| URI | Componente/Controller | Rol | Nombre |
|-----|----------------------|-----|--------|
| `/` | → redirect login | - | - |
| `/dashboard` | Dashboard | admin | dashboard |
| `/stock` | StockList | admin | stock |
| `/stock/{productId}/edit` | ProductEdit | admin | stock.edit |
| `/tickets` | TicketHistory | admin | tickets |
| `/tickets/export?ids=1,2,3` | TicketExportController | admin | tickets.export |
| `/expenses` | Expenses | admin | expenses |
| `/settings` | Settings | admin | settings |
| `/employee/{employee}/tasks` | EmployeeTasks | admin | employee.tasks |
| `/pos` | PointOfSale | todos | pos |
| `/my-tasks` | MyTasks | todos | my-tasks |
| `/invoices/import` | InvoiceImporter | todos | invoices.import |

### Navegación inferior
- **Admin:** Home → Stock → Venta → Gastos → Config
- **Empleado:** Venta → Tareas → Facturas

---

## Flujos de la Aplicación

### Flujo de venta (TPV)
1. Buscar productos por nombre/código o filtrar por categoría
2. Añadir al carrito, ajustar cantidades
3. Opcionalmente aplicar descuento (% o fijo)
4. Pulsar "Cobrar" → confirma con wire:confirm
5. Se crea Ticket + TicketItems, se descuenta stock
6. **Redirect automático a `/tickets`** (historial)

### Flujo de tickets
1. Admin ve historial con filtros: hoy, semana, mes, personalizado, todo
2. Buscar por # ticket o vendedor
3. Click en ticket → modal bottom-sheet con detalle completo (datos empresa, items, totales)
4. Selección múltiple → botón PDF descarga todos los seleccionados
5. El PDF tiene formato de ticket/recibo (100mm x 250mm, fuente sans-serif)

### Flujo de tareas
1. Admin va a Config → click en un empleado → ve/crea/edita/elimina sus tareas
2. Empleado ve "Mis Tareas" en su nav → puede cambiar estado (pendiente/en progreso/completada)
3. Tareas vencidas se marcan en rojo para el empleado

### Flujo de factura (import)
1. El usuario recibe PDF del proveedor
2. Lo pasa por **ChatGPT** manualmente → pide Excel con columnas: código, nombre, cantidad, precio unitario
3. Sube el Excel al **InvoiceImporter** (3 pasos: subir → datos factura → revisar productos)
4. Matching automático por código/nombre, creación automática de nuevos con margen
5. **No hay integración con API de IA para parseo.** Proceso manual asistido.

---

## Archivos Clave de Configuración

| Archivo | Qué hace | Por qué importa |
|---------|----------|-----------------|
| `app/Providers/AppServiceProvider.php` | Bind `path.public` a `public_html` | Sin esto, Laravel no encuentra assets |
| `config/dompdf.php` | `public_path => base_path('public_html')` | Sin esto, DomPDF da "Cannot resolve public path" |
| `vite.config.js` | `publicDirectory: 'public_html'`, `manifest: 'manifest.json'` | Build de assets |
| `bootstrap/app.php` | `trustProxies(at: '*')`, middleware `role` | HTTPS + roles |
| `start.sh` | Genera .env, migra, seedea, arranca php -S en 8080 | Entrypoint Docker |
| `Dockerfile` | PHP 8.4-cli + Node 22 + Composer | Build de imagen |
| `public_html/router.php` | Router para `php -S` (sirve estáticos o index.php) | Sin esto, assets no cargan |

---

## Comandos Frecuentes

```bash
# Desarrollo local
php artisan serve       # Backend localhost:8000
npm run dev             # Vite HMR en paralelo

# Base de datos
php artisan migrate
php artisan db:seed     # AdminUserSeeder + CategorySeeder + SettingSeeder + ProductSeeder

# Build
npm run build

# Deploy
git add -A && git commit -m "mensaje" && git push origin master

# Crear componente Livewire
php artisan make:livewire NombreComponente
```

---

## Variables de Entorno (Producción)

```env
APP_NAME=WetFish
APP_ENV=production
APP_KEY=base64:MDhjAzGSkYvEd2yz6P0Ibj0oLb/Zo19KNZJatUND8eY=
APP_DEBUG=false
APP_URL=https://desarrollos-wetfish.o28eg0.easypanel.host
DB_CONNECTION=mysql
DB_HOST=wetfish_bd
DB_PORT=3306
DB_DATABASE=wetfish_bd
DB_USERNAME=wetfish
DB_PASSWORD=3jOCP3KZ9hr4
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
```

---

## Credenciales

- **Admin:** `admin@wetfish.es` / `password` (creado por `AdminUserSeeder`)
- **Producción DB:** ver variables de entorno arriba

---

## Seeders

Ejecutados en orden por `DatabaseSeeder`:
1. `AdminUserSeeder` — crea admin@wetfish.es si no existe
2. `CategorySeeder` — 5 categorías: Peces, Plantas, Accesorios, Peces criadero, Plantas criadero
3. `SettingSeeder` — tax_rate=21, auto_margin_percentage=30, datos empresa
4. `ProductSeeder` — 10 productos de prueba (Pez 1-10) con precios y stock
