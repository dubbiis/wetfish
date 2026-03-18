# WetFish - Directrices del Proyecto

## Reglas Obligatorias

### README siempre actualizado
**Cada vez que se añada, modifique o elimine funcionalidad, rutas, modelos, componentes o configuración, actualizar `README.md` en el mismo commit.** El README es la fuente de verdad del estado actual del proyecto.

### Logs y depuración
**Siempre dejar logs informativos** en scripts, procesos de arranque y puntos críticos del código para poder identificar errores rápidamente. En `start.sh` y similares, usar `echo` para indicar cada paso. En código PHP, usar `Log::info()` o `Log::error()` en puntos clave (importaciones, checkout, seeders, etc.).

### Idioma
- Commits, comentarios en código, nombres de variables/métodos: en **inglés** o en **español** según el contexto existente (actualmente el código mezcla ambos, seguir la convención del archivo que se esté editando).
- Mensajes de commit: **en español**, descriptivos, sin prefijo estricto.
- UI visible al usuario: **en español**.

### Git y Deploy
- **Rama principal:** `master`
- **Remote:** `https://github.com/dubbiis/wetfish.git`
- **Deploy automático:** push a `master` → GitHub webhook → EasyPanel reconstruye Dockerfile
- **URL producción:** `https://desarrollos-wetfish.o28eg0.easypanel.host`
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
- **admin**: acceso completo (dashboard, stock, tickets, gastos, ajustes, TPV, facturas)
- **employee**: solo TPV (`/pos`) e importador de facturas (`/invoices/import`)

### Middleware de roles
Definido en `app/Http/Middleware/RoleMiddleware.php`, registrado en `bootstrap/app.php` como alias `role`.

---

## Reglas de Desarrollo

### Document root
El document root es **`public_html/`**, NO `public/`. Configurado en:
- `vite.config.js` → `publicDirectory: 'public_html'`
- `start.sh` → `php -S 0.0.0.0:8080 -t public_html public_html/router.php`

### Vite
- El manifest se genera como `manifest.json` (no `.vite/manifest.json`)
- Configurado en `vite.config.js` con `manifest: 'manifest.json'`

### Alpine.js
**NUNCA importar Alpine.js manualmente.** Livewire 4 lo inyecta internamente. Importarlo duplica Alpine y causa errores de `x-data`.

### Proxy y HTTPS
`trustProxies(at: '*')` en `bootstrap/app.php` para que HTTPS funcione detrás del reverse proxy de EasyPanel.

### Imágenes estáticas
Van en `public_html/images/`, NUNCA en `public_html/build/assets/` (se borran en cada `npm run build`).

### Storage para uploads
Los uploads de productos (fotos) se guardan en `storage/app/public/products/`. Requiere `php artisan storage:link` apuntando a `public_html/storage`.

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

---

## Rutas

| URI | Componente | Rol | Nombre |
|-----|-----------|-----|--------|
| `/` | → redirect login | - | - |
| `/dashboard` | Dashboard | admin | dashboard |
| `/stock` | StockList | admin | stock |
| `/stock/{product}/edit` | ProductEdit | admin | stock.edit |
| `/tickets` | TicketHistory | admin | tickets |
| `/expenses` | Expenses | admin | expenses |
| `/settings` | Settings | admin | settings |
| `/employee/{employee}/tasks` | EmployeeTasks | admin | employee.tasks |
| `/tickets/export` | TicketExportController | admin | tickets.export |
| `/pos` | PointOfSale | todos | pos |
| `/my-tasks` | MyTasks | todos | my-tasks |
| `/invoices/import` | InvoiceImporter | todos | invoices.import |

---

## Flujo de Factura (Import)

1. El usuario recibe PDF del proveedor
2. Lo pasa por **ChatGPT** manualmente y le pide un Excel con columnas: código, nombre, cantidad, precio unitario
3. Sube el Excel al **InvoiceImporter**
4. El sistema parsea, intenta matchear productos existentes por código/nombre
5. Productos nuevos se crean automáticamente con margen auto
6. Productos existentes se actualizan (stock + coste)
7. Se registra la factura con sus items

**No hay integración con API de IA para parseo.** Es un proceso manual asistido.

---

## Comandos Frecuentes

```bash
# Desarrollo
php artisan serve       # Backend localhost:8000
npm run dev             # Vite HMR

# Base de datos
php artisan migrate
php artisan db:seed     # Ejecuta AdminUserSeeder + CategorySeeder + SettingSeeder

# Build
npm run build

# Deploy
git add . && git commit -m "mensaje" && git push origin master

# Crear componente Livewire
php artisan make:livewire NombreComponente
# NOTA: verificar que crea los archivos en las rutas correctas (app/Livewire/ y resources/views/livewire/)

# Linting
./vendor/bin/pint
```

---

## Variables de Entorno (Producción)

```env
APP_NAME=WetFish
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://desarrollos-wetfish.o28eg0.easypanel.host
DB_CONNECTION=mysql
DB_HOST=wetfish
DB_PORT=3306
DB_DATABASE=wetfish_bd
DB_USERNAME=wetfish
DB_PASSWORD=***
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
```

---

## Credenciales de Desarrollo

- **Admin:** `admin@wetfish.es` / `password`
- Creado por `AdminUserSeeder`
