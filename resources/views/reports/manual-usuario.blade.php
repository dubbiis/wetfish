<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 25px 35px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #171121;
            color: #e2e8f0;
            font-size: 11px;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #7c3bed 0%, #5b21b6 100%);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 { font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.7); font-size: 11px; }
        .header .subtitle { font-size: 14px; color: rgba(255,255,255,0.9); margin-top: 8px; }
        .card {
            background: rgba(124, 59, 237, 0.05);
            border: 1px solid rgba(124, 59, 237, 0.1);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .card-dark {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        h2 {
            font-size: 15px;
            font-weight: 700;
            color: #7c3bed;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(124, 59, 237, 0.2);
        }
        h3 { font-size: 12px; font-weight: 700; color: #e2e8f0; margin-bottom: 8px; margin-top: 10px; }
        p { margin-bottom: 8px; color: #94a3b8; }
        strong { color: #e2e8f0; }
        .highlight { color: #7c3bed; font-weight: 700; }
        .green { color: #34d399; }
        .amber { color: #fbbf24; }
        .rose { color: #fb7185; }
        .blue { color: #60a5fa; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th {
            text-align: left;
            padding: 8px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        td {
            padding: 7px 10px;
            font-size: 11px;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        td.num { text-align: right; font-weight: 600; color: #e2e8f0; }
        .formula {
            background: rgba(255,255,255,0.05);
            border-left: 3px solid #7c3bed;
            padding: 10px 14px;
            border-radius: 0 8px 8px 0;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #c4b5fd;
            margin: 10px 0;
        }
        .info-box {
            background: rgba(124, 59, 237, 0.08);
            border: 1px solid rgba(124, 59, 237, 0.15);
            border-radius: 8px;
            padding: 10px 14px;
            margin: 10px 0;
        }
        .info-box p { margin: 0; color: #c4b5fd; font-size: 10px; }
        .warning-box {
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 8px;
            padding: 10px 14px;
            margin: 10px 0;
        }
        .warning-box p { margin: 0; color: #fbbf24; font-size: 10px; }
        .success-box {
            background: rgba(52, 211, 153, 0.08);
            border: 1px solid rgba(52, 211, 153, 0.2);
            border-radius: 8px;
            padding: 10px 14px;
            margin: 10px 0;
        }
        .success-box p { margin: 0; color: #34d399; font-size: 10px; }
        .steps { counter-reset: step; padding-left: 0; list-style: none; }
        .steps li {
            counter-increment: step;
            padding: 8px 0 8px 36px;
            position: relative;
            color: #94a3b8;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: 7px;
            width: 22px;
            height: 22px;
            background: #7c3bed;
            border-radius: 50%;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            line-height: 22px;
        }
        .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
        .dot-green { background: #34d399; }
        .dot-amber { background: #fbbf24; }
        .dot-rose { background: #fb7185; }
        .dot-blue { background: #60a5fa; }
        .dot-violet { background: #7c3bed; }
        .page-break { page-break-before: always; }
        .footer {
            text-align: center;
            color: rgba(255,255,255,0.2);
            font-size: 9px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .screen-mock {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 12px;
            margin: 10px 0;
        }
        .screen-mock .mock-header {
            background: rgba(124, 59, 237, 0.15);
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-weight: 700;
            color: #c4b5fd;
            font-size: 10px;
        }
        .screen-mock .mock-row {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 4px;
            font-size: 10px;
            color: #94a3b8;
        }
        .nav-mock {
            display: table;
            width: 100%;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 6px;
            margin: 8px 0;
        }
        .nav-mock span {
            display: table-cell;
            text-align: center;
            padding: 4px 8px;
            font-size: 9px;
            color: #94a3b8;
        }
        .nav-mock span.active {
            background: #7c3bed;
            border-radius: 6px;
            color: #fff;
            font-weight: 700;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-green { background: rgba(52,211,153,0.15); color: #34d399; }
        .badge-amber { background: rgba(251,191,36,0.15); color: #fbbf24; }
        .badge-rose { background: rgba(251,113,133,0.15); color: #fb7185; }
        .badge-blue { background: rgba(96,165,250,0.15); color: #60a5fa; }
        .badge-violet { background: rgba(124,59,237,0.15); color: #c4b5fd; }
    </style>
</head>
<body>

    <!-- PORTADA -->
    <div class="header">
        <h1>WetFish</h1>
        <p>Sistema de Gesti&oacute;n para Acuariofilia</p>
        <div class="subtitle">Manual de Usuario &mdash; Documentaci&oacute;n Completa</div>
        <p style="margin-top: 16px; font-size: 10px;">Versi&oacute;n 1.0 &middot; Marzo 2026</p>
    </div>

    <!-- ÍNDICE -->
    <div class="card">
        <h2>&Iacute;ndice</h2>
        <table>
            <tr><td><strong>1.</strong> &iquest;Qu&eacute; es WetFish?</td><td class="num">2</td></tr>
            <tr><td><strong>2.</strong> Acceso y roles de usuario</td><td class="num">2</td></tr>
            <tr><td><strong>3.</strong> Panel Principal (Dashboard)</td><td class="num">3</td></tr>
            <tr><td><strong>4.</strong> Punto de Venta (TPV)</td><td class="num">4</td></tr>
            <tr><td><strong>5.</strong> Gesti&oacute;n de Stock</td><td class="num">5</td></tr>
            <tr><td><strong>6.</strong> Historial de Ventas (Tickets)</td><td class="num">6</td></tr>
            <tr><td><strong>7.</strong> Gastos Operativos</td><td class="num">7</td></tr>
            <tr><td><strong>8.</strong> Importador de Facturas con IA</td><td class="num">8</td></tr>
            <tr><td><strong>9.</strong> Sistema de Coste Real y Margen</td><td class="num">9</td></tr>
            <tr><td><strong>10.</strong> Ajuste Din&aacute;mico de Precios</td><td class="num">10</td></tr>
            <tr><td><strong>11.</strong> Gesti&oacute;n Fiscal (IVA)</td><td class="num">11</td></tr>
            <tr><td><strong>12.</strong> Sistema de Tareas</td><td class="num">12</td></tr>
            <tr><td><strong>13.</strong> Configuraci&oacute;n</td><td class="num">12</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 1. QUÉ ES WETFISH -->
    <div class="card">
        <h2>1. &iquest;Qu&eacute; es WetFish?</h2>
        <p>WetFish es un sistema de gesti&oacute;n integral dise&ntilde;ado espec&iacute;ficamente para tiendas de acuariofilia. Incluye:</p>
        <table>
            <tr><td><span class="dot dot-violet"></span> <strong>Punto de Venta (TPV)</strong></td><td>Cobrar ventas desde el m&oacute;vil</td></tr>
            <tr><td><span class="dot dot-green"></span> <strong>Control de Stock</strong></td><td>Inventario con alertas de stock bajo</td></tr>
            <tr><td><span class="dot dot-amber"></span> <strong>Gastos y Facturas</strong></td><td>Control de gastos con escaneo IA</td></tr>
            <tr><td><span class="dot dot-blue"></span> <strong>Estad&iacute;sticas</strong></td><td>Dashboard con m&eacute;tricas en tiempo real</td></tr>
            <tr><td><span class="dot dot-rose"></span> <strong>Margen Inteligente</strong></td><td>C&aacute;lculo de coste real y ajuste de precios</td></tr>
        </table>
        <p>La aplicaci&oacute;n funciona desde el navegador del m&oacute;vil como una app (PWA). No necesita descargarse de ninguna tienda.</p>
    </div>

    <!-- 2. ACCESO Y ROLES -->
    <div class="card-dark">
        <h2>2. Acceso y Roles de Usuario</h2>
        <p>WetFish tiene dos tipos de usuario con acceso diferente:</p>

        <h3>Administrador</h3>
        <p>Acceso completo a todas las funciones. Su navegaci&oacute;n inferior tiene 5 opciones:</p>
        <div class="nav-mock">
            <span class="active">Inicio</span>
            <span>Stock</span>
            <span>Venta</span>
            <span>Gastos</span>
            <span>Config</span>
        </div>

        <h3>Empleado</h3>
        <p>Acceso limitado: puede vender, ver sus tareas e importar facturas. Su navegaci&oacute;n tiene 3 opciones:</p>
        <div class="nav-mock">
            <span class="active">Venta</span>
            <span>Tareas</span>
            <span>Facturas</span>
        </div>

        <div class="info-box">
            <p><strong>Seguridad:</strong> Los empleados no pueden ver estad&iacute;sticas, precios de coste, m&aacute;rgenes ni configuraci&oacute;n del negocio.</p>
        </div>
    </div>

    <!-- 3. DASHBOARD -->
    <div class="card">
        <h2>3. Panel Principal (Dashboard)</h2>
        <p>El Dashboard es la pantalla de inicio del administrador. Muestra toda la informaci&oacute;n del negocio de un vistazo.</p>

        <h3>Filtro de per&iacute;odo</h3>
        <p>En la parte superior hay filtros para ver los datos de <strong>Hoy</strong>, <strong>Semana</strong> o <strong>Mes</strong>. Todos los datos de la pantalla cambian seg&uacute;n el per&iacute;odo seleccionado.</p>

        <h3>M&eacute;tricas principales</h3>
        <table>
            <tr><th>M&eacute;trica</th><th>Qu&eacute; muestra</th></tr>
            <tr><td><strong>Ingresos</strong></td><td>Total facturado en el per&iacute;odo (dinero que ha entrado)</td></tr>
            <tr><td><strong>Beneficio neto</strong></td><td>Ingresos menos todos los costes (compras + servicios + gastos)</td></tr>
            <tr><td><strong>Tickets</strong></td><td>N&uacute;mero de ventas realizadas</td></tr>
            <tr><td><strong>Ticket medio</strong></td><td>Valor promedio de cada venta</td></tr>
            <tr><td><strong>Unidades vendidas</strong></td><td>Total de productos vendidos</td></tr>
            <tr><td><strong>Venta m&aacute;xima</strong></td><td>El ticket m&aacute;s alto del per&iacute;odo</td></tr>
        </table>

        <h3>Margen Real vs Objetivo</h3>
        <p>Una barra de progreso con colores muestra si el margen de beneficio est&aacute; donde debe estar:</p>
        <table>
            <tr><td><span class="dot dot-green"></span> <strong>Verde</strong></td><td>El margen est&aacute; por encima del objetivo. Todo bien.</td></tr>
            <tr><td><span class="dot dot-amber"></span> <strong>&Aacute;mbar</strong></td><td>El margen est&aacute; cerca del objetivo. Atenci&oacute;n.</td></tr>
            <tr><td><span class="dot dot-rose"></span> <strong>Rojo</strong></td><td>El margen est&aacute; por debajo. Acci&oacute;n necesaria.</td></tr>
        </table>

        <h3>Top productos</h3>
        <p>Muestra los 5 productos m&aacute;s vendidos, con opci&oacute;n de ver por <strong>cantidad</strong> (unidades vendidas) o por <strong>facturaci&oacute;n</strong> (euros generados).</p>

        <h3>Costes vs Ingresos</h3>
        <p>Desglose completo de d&oacute;nde viene el dinero y d&oacute;nde se va:</p>
        <table>
            <tr><td><span class="green">Ingresos por ventas</span></td><td>Lo que entra por el TPV</td></tr>
            <tr><td><span class="rose">Compras de producto</span></td><td>Lo gastado en mercanc&iacute;a (peces, plantas, etc.)</td></tr>
            <tr><td><span class="amber">Servicios</span></td><td>Facturas de servicios (mantenimiento, etc.)</td></tr>
            <tr><td><span class="amber">Gastos operativos</span></td><td>Luz, agua, alquiler, internet, etc.</td></tr>
        </table>
        <p>Se muestra el beneficio de <strong>dos formas</strong>:</p>
        <table>
            <tr><td><strong>Sin IVA</strong> (base)</td><td>Para tomar decisiones de negocio: &iquest;estoy ganando dinero de verdad?</td></tr>
            <tr><td><strong>Con IVA</strong> (total)</td><td>Para saber el flujo de caja: &iquest;hay dinero en la cuenta hoy?</td></tr>
        </table>

        <h3>Actividad</h3>
        <p>Muestra la <strong>hora pico</strong> (cu&aacute;ndo se vende m&aacute;s), el <strong>mejor d&iacute;a</strong> de la semana y el <strong>&uacute;ltimo ticket</strong> registrado.</p>

        <h3>Gastos por categor&iacute;a</h3>
        <p>Barras que muestran cu&aacute;nto se gasta en cada categor&iacute;a (luz, agua, alquiler...) con el desglose base + IVA.</p>
    </div>

    <div class="page-break"></div>

    <!-- 4. TPV -->
    <div class="card">
        <h2>4. Punto de Venta (TPV)</h2>
        <p>La pantalla de venta es donde se cobran los productos a los clientes. Est&aacute; dise&ntilde;ada para usarse desde el m&oacute;vil de forma r&aacute;pida.</p>

        <h3>&iquest;C&oacute;mo se realiza una venta?</h3>
        <ol class="steps">
            <li><strong>Buscar producto:</strong> Escribir el nombre o c&oacute;digo en la barra de b&uacute;squeda, o filtrar por categor&iacute;a (Peces, Plantas, Accesorios...)</li>
            <li><strong>A&ntilde;adir al carrito:</strong> Pulsar sobre el producto deseado. Aparece en el carrito con cantidad 1.</li>
            <li><strong>Ajustar cantidades:</strong> Usar los botones + y - junto a cada producto en el carrito.</li>
            <li><strong>Aplicar descuento</strong> (opcional): Elegir entre descuento en porcentaje (%) o en euros fijos (&euro;).</li>
            <li><strong>Cobrar:</strong> Pulsar el bot&oacute;n &laquo;Cobrar&raquo;. El sistema pide confirmaci&oacute;n antes de procesar.</li>
            <li><strong>Listo:</strong> El stock se descuenta autom&aacute;ticamente y se crea un ticket de venta.</li>
        </ol>

        <h3>&iquest;Qu&eacute; se ve en pantalla?</h3>
        <table>
            <tr><td><strong>Barra de b&uacute;squeda</strong></td><td>Busca en tiempo real mientras se escribe</td></tr>
            <tr><td><strong>Filtros de categor&iacute;a</strong></td><td>Bot&oacute;nes con iconos para filtrar r&aacute;pido</td></tr>
            <tr><td><strong>Lista de resultados</strong></td><td>Foto del producto, nombre, stock disponible y precio</td></tr>
            <tr><td><strong>Carrito</strong></td><td>Productos a&ntilde;adidos con cantidades y subtotales</td></tr>
            <tr><td><strong>Resumen</strong></td><td>Subtotal, descuento (si hay), IVA y total final</td></tr>
        </table>

        <div class="info-box">
            <p><strong>IVA autom&aacute;tico:</strong> El IVA (21% por defecto) se calcula y muestra autom&aacute;ticamente. Se puede cambiar el porcentaje en Configuraci&oacute;n.</p>
        </div>

        <div class="warning-box">
            <p><strong>Control de stock:</strong> Si un producto tiene stock 0, a&uacute;n se puede vender (no se bloquea), pero el stock quedar&aacute; en negativo como aviso.</p>
        </div>
    </div>

    <!-- 5. STOCK -->
    <div class="card-dark">
        <h2>5. Gesti&oacute;n de Stock</h2>

        <h3>Listado de productos</h3>
        <p>La pantalla de Stock muestra todos los productos del inventario con un indicador de color:</p>
        <table>
            <tr><td><span class="dot dot-green"></span> <strong>Verde</strong></td><td>Stock por encima del m&iacute;nimo. Sin problemas.</td></tr>
            <tr><td><span class="dot dot-amber"></span> <strong>&Aacute;mbar</strong></td><td>Stock igual o por debajo del m&iacute;nimo. Hay que reponer.</td></tr>
            <tr><td><span class="dot dot-rose"></span> <strong>Rojo</strong></td><td>Stock agotado (0 o menos). Cr&iacute;tico.</td></tr>
        </table>
        <p>Se puede buscar por nombre o c&oacute;digo, y filtrar por categor&iacute;a.</p>

        <h3>Crear o editar un producto</h3>
        <p>Al crear o editar un producto se rellena:</p>
        <table>
            <tr><th>Campo</th><th>Descripci&oacute;n</th></tr>
            <tr><td><strong>Foto</strong></td><td>Imagen del producto (opcional)</td></tr>
            <tr><td><strong>Nombre</strong></td><td>Nombre del producto (obligatorio)</td></tr>
            <tr><td><strong>C&oacute;digo</strong></td><td>C&oacute;digo SKU del proveedor (opcional)</td></tr>
            <tr><td><strong>Categor&iacute;a</strong></td><td>Peces, Plantas, Accesorios, etc.</td></tr>
            <tr><td><strong>Precio de coste</strong></td><td>Lo que cuesta comprarlo al proveedor</td></tr>
            <tr><td><strong>Precio de venta</strong></td><td>Lo que se cobra al cliente</td></tr>
            <tr><td><strong>Margen autom&aacute;tico</strong></td><td>Si est&aacute; activo, el precio de venta se calcula solo (ver secci&oacute;n 9)</td></tr>
            <tr><td><strong>Stock actual</strong></td><td>Unidades disponibles</td></tr>
            <tr><td><strong>Stock m&iacute;nimo</strong></td><td>Por debajo de este n&uacute;mero, se activa la alerta</td></tr>
        </table>

        <div class="success-box">
            <p><strong>Alerta en el dashboard:</strong> Cuando hay productos con stock cr&iacute;tico, aparece un icono rojo parpadeante en la cabecera de la app.</p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- 6. TICKETS -->
    <div class="card">
        <h2>6. Historial de Ventas (Tickets)</h2>
        <p>Aqu&iacute; se consultan todas las ventas realizadas. Solo accesible para el administrador.</p>

        <h3>Filtros disponibles</h3>
        <table>
            <tr><td><strong>Hoy</strong></td><td>Ventas de hoy</td></tr>
            <tr><td><strong>Semana</strong></td><td>Ventas de la semana en curso</td></tr>
            <tr><td><strong>Mes</strong></td><td>Ventas del mes actual</td></tr>
            <tr><td><strong>Personalizado</strong></td><td>Elegir fecha de inicio y fin</td></tr>
            <tr><td><strong>Todo</strong></td><td>Todas las ventas desde siempre</td></tr>
        </table>

        <h3>Detalle de ticket</h3>
        <p>Al pulsar un ticket se abre una ventana con toda la informaci&oacute;n:</p>
        <table>
            <tr><td>Datos de la empresa (nombre, CIF, direcci&oacute;n)</td></tr>
            <tr><td>Lista de productos vendidos con cantidades y precios</td></tr>
            <tr><td>Subtotal, descuento (si lo hubo), IVA y total</td></tr>
            <tr><td>Nombre del vendedor que realiz&oacute; la venta</td></tr>
        </table>

        <h3>Exportar a PDF</h3>
        <p>Se pueden seleccionar varios tickets a la vez y descargarlos todos como un PDF con formato de recibo.</p>
    </div>

    <!-- 7. GASTOS -->
    <div class="card-dark">
        <h2>7. Gastos Operativos</h2>
        <p>Secci&oacute;n para registrar los gastos fijos del negocio: luz, agua, alquiler, internet, hosting, etc. <strong>No incluye las compras de mercanc&iacute;a</strong> (peces, plantas), que se gestionan con el importador de facturas.</p>

        <h3>&iquest;C&oacute;mo a&ntilde;adir un gasto?</h3>
        <ol class="steps">
            <li>Pulsar el bot&oacute;n <strong>+ A&ntilde;adir gasto</strong></li>
            <li>Seleccionar la <strong>categor&iacute;a</strong> (Luz, Agua, Alquiler, etc.)</li>
            <li>Escribir el <strong>concepto</strong> (ej: &laquo;Factura luz febrero&raquo;)</li>
            <li>Introducir el <strong>importe base</strong> (sin IVA) y el <strong>% de IVA</strong></li>
            <li>El <strong>total con IVA</strong> se calcula autom&aacute;ticamente</li>
            <li>Seleccionar la <strong>fecha</strong> e introducir notas opcionales</li>
            <li>Pulsar <strong>A&ntilde;adir gasto</strong></li>
        </ol>

        <h3>Escaneo con IA</h3>
        <p>Tambi&eacute;n se puede subir una <strong>foto o PDF de un recibo</strong> y la inteligencia artificial rellena los datos autom&aacute;ticamente. Se revisan antes de guardar.</p>

        <h3>Categor&iacute;as personalizables</h3>
        <p>Las categor&iacute;as de gastos se crean y gestionan desde el bot&oacute;n de ajustes (icono de tuerca) dentro de Gastos. Se puede elegir un nombre y un icono para cada una.</p>

        <h3>&iquest;Para qu&eacute; sirven los gastos?</h3>
        <div class="info-box">
            <p>Los gastos operativos son <strong>fundamentales</strong> para el c&aacute;lculo del coste real de cada producto. Se reparten proporcionalmente entre todas las unidades en stock. Ver secci&oacute;n 9.</p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- 8. IMPORTADOR -->
    <div class="card">
        <h2>8. Importador de Facturas con IA</h2>
        <p>El importador permite dar de alta mercanc&iacute;a (peces, plantas, accesorios) subiendo directamente la factura del proveedor. La inteligencia artificial extrae toda la informaci&oacute;n autom&aacute;ticamente.</p>

        <h3>Paso 1 &mdash; Subir la factura</h3>
        <p>Se acepta un <strong>PDF</strong> o una <strong>foto</strong> (JPG, PNG) de la factura del proveedor. Tambi&eacute;n se puede hacer una foto directamente desde la c&aacute;mara del m&oacute;vil.</p>
        <p>La IA analiza el documento y extrae: proveedor, n&uacute;mero de factura, fecha, lista de productos con cantidades y precios, transporte, IVA y total.</p>

        <h3>Paso 2 &mdash; Revisar datos de la factura</h3>
        <p>Se muestra un formulario con los datos extra&iacute;dos ya rellenados:</p>
        <table>
            <tr><th>Campo</th><th>Descripci&oacute;n</th></tr>
            <tr><td><strong>Tipo</strong></td><td>Compra (mercanc&iacute;a) o Servicio</td></tr>
            <tr><td><strong>Proveedor</strong></td><td>Seleccionar uno existente o crear nuevo</td></tr>
            <tr><td><strong>N&ordm; factura</strong></td><td>N&uacute;mero de factura del proveedor</td></tr>
            <tr><td><strong>Fecha</strong></td><td>Fecha de la factura</td></tr>
            <tr><td><strong>Concepto</strong></td><td>Descripci&oacute;n de la factura</td></tr>
            <tr><td><strong>Costes extra</strong></td><td>Transporte, embalaje, etc.</td></tr>
        </table>
        <p>Tambi&eacute;n se muestra un <strong>resumen fiscal</strong> extra&iacute;do de la factura: subtotal de productos, transporte, IVA y total.</p>

        <h3>Paso 3 &mdash; Revisar productos</h3>
        <p>Se muestra cada producto detectado con su c&oacute;digo, cantidad, precio unitario y total. El sistema:</p>
        <table>
            <tr><td><span class="badge badge-green">Producto existente</span></td><td>Si el c&oacute;digo coincide con uno ya registrado, se <strong>suma el stock</strong> y se actualiza el precio de coste</td></tr>
            <tr><td><span class="badge badge-blue">Producto nuevo</span></td><td>Si no existe, se <strong>crea autom&aacute;ticamente</strong> con el margen configurado</td></tr>
        </table>
        <p>Se puede <strong>eliminar</strong> cualquier producto de la lista pulsando la <strong>X</strong> si no se quiere importar.</p>

        <h3>Resumen fiscal de la factura</h3>
        <p>Al importar, se guardan tambi&eacute;n los datos fiscales extra&iacute;dos por la IA:</p>
        <table>
            <tr><td>Subtotal de productos (neto)</td></tr>
            <tr><td>Porcentaje y cantidad de descuento (si aplica)</td></tr>
            <tr><td>Coste de transporte</td></tr>
            <tr><td>Tipo y cantidad de IVA</td></tr>
            <tr><td>Total de la factura</td></tr>
        </table>

        <div class="warning-box">
            <p><strong>Importante:</strong> Siempre revisar los datos extra&iacute;dos por la IA antes de confirmar. Aunque es muy precisa, conviene verificar cantidades y precios con la factura original.</p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- 9. COSTE REAL -->
    <div class="card-dark">
        <h2>9. Sistema de Coste Real y Margen</h2>
        <p>Esta es la funcionalidad central de WetFish. Permite conocer el <strong>coste real</strong> de cada producto, no solo lo que se pag&oacute; al proveedor.</p>

        <h3>&iquest;Qu&eacute; es el coste real?</h3>
        <p>Cuando se compra un pez por <strong>0,48 &euro;</strong>, ese no es su coste real. El pez necesita agua tratada, electricidad (iluminaci&oacute;n, filtros, calentadores), espacio (alquiler) y otros recursos para mantenerse vivo hasta que se vende.</p>

        <h3>&iquest;C&oacute;mo se calcula?</h3>
        <div class="formula">
            Coste operativo por unidad = Total gastos operativos &divide; Total unidades en stock
        </div>
        <div class="formula">
            Coste real = Precio de compra + Coste operativo por unidad
        </div>

        <h3>Ejemplo pr&aacute;ctico</h3>
        <div class="info-box">
            <p><strong>Datos del mes:</strong> Gastos operativos = 600 &euro; (luz, agua, alquiler...) &middot; Unidades en stock = 300</p>
            <p><strong>Coste operativo por unidad = 600 &divide; 300 = 2,00 &euro;</strong></p>
        </div>
        <table>
            <tr><th>Producto</th><th>Coste compra</th><th>+ Operativo</th><th>= Coste real</th><th>P. Venta</th><th>Margen</th></tr>
            <tr>
                <td>Pez &Aacute;ngel</td>
                <td class="num">0,48 &euro;</td>
                <td class="num">2,00 &euro;</td>
                <td class="num"><strong>2,48 &euro;</strong></td>
                <td class="num">5,20 &euro;</td>
                <td class="num"><span class="green">52%</span></td>
            </tr>
            <tr>
                <td>Planta Anubia</td>
                <td class="num">3,13 &euro;</td>
                <td class="num">2,00 &euro;</td>
                <td class="num"><strong>5,13 &euro;</strong></td>
                <td class="num">8,00 &euro;</td>
                <td class="num"><span class="green">36%</span></td>
            </tr>
            <tr>
                <td>Filtro externo</td>
                <td class="num">15,00 &euro;</td>
                <td class="num">2,00 &euro;</td>
                <td class="num"><strong>17,00 &euro;</strong></td>
                <td class="num">19,00 &euro;</td>
                <td class="num"><span class="rose">10%</span></td>
            </tr>
        </table>
        <p>El pez que parece costar 0,48 &euro; en realidad cuesta <strong>2,48 &euro;</strong>. Si se vende a 2,00 &euro; pensando que hay beneficio, en realidad se estar&iacute;a <strong>perdiendo dinero</strong>.</p>

        <h3>Margen autom&aacute;tico</h3>
        <p>Cuando se crea un producto, se puede activar el <strong>margen autom&aacute;tico</strong>. El sistema calcula el precio de venta autom&aacute;ticamente:</p>
        <div class="formula">
            Precio venta = Precio de coste &times; (1 + Margen% &divide; 100)
        </div>
        <p>Ejemplo: Coste 3,13 &euro; con margen 30% &rarr; Venta = 3,13 &times; 1,30 = <strong>4,07 &euro;</strong></p>
        <p>El porcentaje de margen se configura en Configuraci&oacute;n (por defecto 30%).</p>

        <h3>Margen objetivo vs Margen real</h3>
        <p>El <strong>margen objetivo</strong> es el porcentaje de beneficio que se desea obtener. El <strong>margen real</strong> es el que se tiene realmente cuando se incluyen todos los gastos.</p>
        <div class="formula">
            Margen real (%) = ((Precio venta - Coste real) &divide; Precio venta) &times; 100
        </div>
        <p>El dashboard compara ambos y avisa si el margen real est&aacute; por debajo del objetivo.</p>

        <h3>Per&iacute;odo configurable</h3>
        <p>Los gastos operativos se pueden calcular sobre diferentes per&iacute;odos para suavizar picos:</p>
        <table>
            <tr><td><strong>Mes actual</strong></td><td>Solo los gastos del mes en curso. M&aacute;s reactivo.</td></tr>
            <tr><td><strong>&Uacute;ltimos 3 meses</strong></td><td>Promedio de 3 meses. M&aacute;s estable.</td></tr>
            <tr><td><strong>&Uacute;ltimos 6 meses</strong></td><td>Promedio semestral. Muy estable.</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 10. AJUSTE PRECIOS -->
    <div class="card">
        <h2>10. Ajuste Din&aacute;mico de Precios</h2>
        <p>Cuando el margen real cae por debajo del objetivo, el sistema permite <strong>subir todos los precios</strong> de forma controlada.</p>

        <h3>&iquest;C&oacute;mo funciona?</h3>
        <ol class="steps">
            <li>El dashboard muestra que el margen real est&aacute; por debajo del objetivo</li>
            <li>Ir a <strong>Configuraci&oacute;n &gt; Ajuste de precios</strong></li>
            <li>Introducir el porcentaje de subida deseado (ej: +5%)</li>
            <li>El sistema muestra una <strong>vista previa</strong> con ejemplos de c&oacute;mo quedar&aacute;n los precios</li>
            <li>Al confirmar, <strong>todos los precios se actualizan</strong> autom&aacute;ticamente</li>
            <li>El TPV cobra los nuevos precios desde ese momento</li>
        </ol>

        <h3>Ejemplo de ajuste del +5%</h3>
        <table>
            <tr><th>Producto</th><th>Precio original</th><th>Precio ajustado</th><th>Diferencia</th></tr>
            <tr><td>Pez &Aacute;ngel</td><td class="num">5,20 &euro;</td><td class="num"><strong>5,46 &euro;</strong></td><td class="num"><span class="green">+0,26 &euro;</span></td></tr>
            <tr><td>Planta Anubia</td><td class="num">8,00 &euro;</td><td class="num"><strong>8,40 &euro;</strong></td><td class="num"><span class="green">+0,40 &euro;</span></td></tr>
            <tr><td>Filtro externo</td><td class="num">19,00 &euro;</td><td class="num"><strong>19,95 &euro;</strong></td><td class="num"><span class="green">+0,95 &euro;</span></td></tr>
        </table>

        <h3>Seguridad: precios originales</h3>
        <div class="success-box">
            <p><strong>Los precios originales siempre se guardan.</strong> Se puede volver a los precios anteriores pulsando &laquo;Revertir precios&raquo; en cualquier momento. El sistema nunca pierde el precio original.</p>
        </div>

        <h3>Sugerencias del sistema</h3>
        <p>Cuando el sistema detecta que hay <strong>horas de m&aacute;xima venta</strong> y el margen est&aacute; bajo, muestra una notificaci&oacute;n en el dashboard sugiriendo subir los precios con el porcentaje necesario.</p>
        <div class="warning-box">
            <p><strong>El sistema nunca cambia precios autom&aacute;ticamente.</strong> Solo informa y sugiere. La decisi&oacute;n siempre es del propietario.</p>
        </div>
    </div>

    <!-- 11. IVA -->
    <div class="card-dark">
        <h2>11. Gesti&oacute;n Fiscal (IVA)</h2>
        <p>WetFish lleva un control completo del IVA para facilitar las liquidaciones trimestrales.</p>

        <h3>&iquest;Qu&eacute; controla el sistema?</h3>
        <table>
            <tr><th>Concepto</th><th>Qu&eacute; es</th><th>Ejemplo</th></tr>
            <tr>
                <td><span class="green">IVA repercutido</span> (cobrado)</td>
                <td>El IVA que se cobra a los clientes en cada venta</td>
                <td class="num">450 &euro;</td>
            </tr>
            <tr>
                <td><span class="rose">IVA soportado</span> (pagado)</td>
                <td>El IVA pagado en los gastos operativos y compras</td>
                <td class="num">120 &euro;</td>
            </tr>
            <tr>
                <td><span class="amber">Balance</span></td>
                <td>La diferencia: lo que hay que ingresar a Hacienda</td>
                <td class="num">330 &euro;</td>
            </tr>
        </table>

        <h3>&iquest;Por qu&eacute; importa el IVA en los gastos?</h3>
        <p>Cuando llega una factura de luz de <strong>110 &euro;</strong>:</p>
        <table>
            <tr><td>Base imponible (coste real para el negocio)</td><td class="num"><strong>100,00 &euro;</strong></td></tr>
            <tr><td>IVA 10% (recuperable trimestralmente)</td><td class="num">10,00 &euro;</td></tr>
            <tr><td>Total de la factura</td><td class="num">110,00 &euro;</td></tr>
        </table>
        <p>Para calcular el coste real, solo se usa la <strong>base imponible</strong> (100 &euro;), porque el IVA se recupera. Por eso el sistema pide separar base e IVA al introducir gastos.</p>

        <h3>Tipos de IVA habituales</h3>
        <table>
            <tr><td>Electricidad</td><td class="num">10%</td></tr>
            <tr><td>Agua</td><td class="num">10%</td></tr>
            <tr><td>Tel&eacute;fono / Internet</td><td class="num">21%</td></tr>
            <tr><td>Alquiler local</td><td class="num">21%</td></tr>
            <tr><td>Seguros</td><td class="num">0% (exento)</td></tr>
            <tr><td>Productos (ventas)</td><td class="num">21%</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 12. TAREAS -->
    <div class="card">
        <h2>12. Sistema de Tareas</h2>
        <p>El administrador puede asignar tareas a los empleados y seguir su progreso.</p>

        <h3>Para el administrador</h3>
        <p>Desde <strong>Configuraci&oacute;n</strong>, pulsar sobre un empleado para ver y gestionar sus tareas:</p>
        <table>
            <tr><td>Crear tareas con t&iacute;tulo, descripci&oacute;n y fecha l&iacute;mite</td></tr>
            <tr><td>Editar o eliminar tareas existentes</td></tr>
            <tr><td>Ver el estado de cada tarea</td></tr>
        </table>

        <h3>Para el empleado</h3>
        <p>En la pesta&ntilde;a <strong>Tareas</strong>, el empleado ve sus tareas asignadas y puede cambiar su estado:</p>
        <table>
            <tr><td><span class="badge badge-amber">Pendiente</span></td><td>Tarea a&uacute;n no empezada</td></tr>
            <tr><td><span class="badge badge-blue">En progreso</span></td><td>Tarea en la que se est&aacute; trabajando</td></tr>
            <tr><td><span class="badge badge-green">Completada</span></td><td>Tarea terminada</td></tr>
        </table>
        <p>Las tareas con fecha l&iacute;mite vencida aparecen marcadas en <span class="rose">rojo</span>.</p>
    </div>

    <!-- 13. CONFIGURACIÓN -->
    <div class="card-dark">
        <h2>13. Configuraci&oacute;n</h2>
        <p>Desde esta pantalla se gestiona todo el sistema. Solo accesible para el administrador.</p>

        <h3>Datos del negocio</h3>
        <p>Nombre, CIF, direcci&oacute;n, tel&eacute;fono y email. Aparecen en los tickets y PDFs de venta.</p>

        <h3>Impuestos y m&aacute;rgenes</h3>
        <table>
            <tr><td><strong>% IVA</strong></td><td>Porcentaje de IVA aplicado en ventas (por defecto 21%)</td></tr>
            <tr><td><strong>% Margen autom&aacute;tico</strong></td><td>Margen aplicado a productos nuevos (por defecto 30%)</td></tr>
        </table>

        <h3>Margen y coste real</h3>
        <table>
            <tr><td><strong>% Margen objetivo</strong></td><td>El beneficio deseado (referencia para las alertas)</td></tr>
            <tr><td><strong>Per&iacute;odo de c&aacute;lculo</strong></td><td>Mes actual, 3 meses o 6 meses para los gastos operativos</td></tr>
        </table>
        <p>Muestra en tiempo real: total gastos operativos, unidades en stock y coste por unidad.</p>

        <h3>Ajuste de precios</h3>
        <p>Herramienta para subir o bajar todos los precios a la vez. Ver secci&oacute;n 10.</p>

        <h3>Empleados</h3>
        <p>Crear nuevos empleados (nombre, email, contrase&ntilde;a), eliminar empleados existentes y acceder a la gesti&oacute;n de tareas de cada uno.</p>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>WetFish &mdash; Sistema de Gesti&oacute;n para Acuariofilia &middot; Manual de Usuario v1.0 &middot; Marzo 2026</p>
        <p>Desarrollado por Dubi &middot; Todos los datos se procesan en tiempo real.</p>
    </div>

</body>
</html>
