<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 30px 40px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #171121;
            color: #e2e8f0;
            font-size: 11px;
            line-height: 1.5;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #7c3bed 0%, #5b21b6 100%);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.7); font-size: 11px; }
        .header .subtitle { font-size: 13px; color: rgba(255,255,255,0.9); margin-top: 8px; }

        /* Cards */
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

        /* Titles */
        h2 {
            font-size: 15px;
            font-weight: 700;
            color: #7c3bed;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(124, 59, 237, 0.2);
        }
        h3 { font-size: 12px; font-weight: 700; color: #e2e8f0; margin-bottom: 8px; }

        /* Text */
        p { margin-bottom: 8px; color: #94a3b8; }
        strong { color: #e2e8f0; }
        .highlight { color: #7c3bed; font-weight: 700; }
        .green { color: #34d399; }
        .amber { color: #fbbf24; }
        .rose { color: #fb7185; }

        /* Tables */
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
            padding: 8px 10px;
            font-size: 11px;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        td.num { text-align: right; font-weight: 600; color: #e2e8f0; }

        /* Formula box */
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

        /* Info box */
        .info-box {
            background: rgba(124, 59, 237, 0.08);
            border: 1px solid rgba(124, 59, 237, 0.15);
            border-radius: 8px;
            padding: 10px 14px;
            margin: 10px 0;
        }
        .info-box p { margin: 0; color: #c4b5fd; font-size: 10px; }

        /* Warning box */
        .warning-box {
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 8px;
            padding: 10px 14px;
            margin: 10px 0;
        }
        .warning-box p { margin: 0; color: #fbbf24; font-size: 10px; }

        /* Step list */
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

        /* Color dots */
        .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
        .dot-green { background: #34d399; }
        .dot-amber { background: #fbbf24; }
        .dot-rose { background: #fb7185; }

        /* Page break */
        .page-break { page-break-before: always; }

        /* Footer */
        .footer {
            text-align: center;
            color: rgba(255,255,255,0.2);
            font-size: 9px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        /* Two column */
        .two-col { width: 100%; }
        .two-col td { width: 50%; vertical-align: top; padding: 0 6px; border: none; }
    </style>
</head>
<body>

    <!-- PORTADA -->
    <div class="header">
        <h1>WetFish</h1>
        <p>TPV y Gestión para Acuariofilia</p>
        <div class="subtitle">Sistema de Coste Real y Precio Dinámico</div>
        <p style="margin-top: 16px; font-size: 10px;">Informe técnico-funcional &middot; Marzo 2026</p>
    </div>

    <!-- ÍNDICE -->
    <div class="card">
        <h2>Contenido</h2>
        <table>
            <tr><td>1. &iquest;Qué es el Coste Real?</td><td class="num">Pág. 1</td></tr>
            <tr><td>2. Cálculo del Coste Real — Ejemplo práctico</td><td class="num">Pág. 1</td></tr>
            <tr><td>3. Margen Real vs Margen Objetivo</td><td class="num">Pág. 2</td></tr>
            <tr><td>4. Ajuste Dinámico de Precios</td><td class="num">Pág. 2</td></tr>
            <tr><td>5. Detección de Horas Pico</td><td class="num">Pág. 3</td></tr>
            <tr><td>6. Gestión del IVA en Gastos</td><td class="num">Pág. 3</td></tr>
            <tr><td>7. Resumen Fiscal</td><td class="num">Pág. 4</td></tr>
            <tr><td>8. Doble Vista de Beneficio</td><td class="num">Pág. 4</td></tr>
            <tr><td>9. Flujo Completo de Uso</td><td class="num">Pág. 4</td></tr>
        </table>
    </div>

    <!-- 1. COSTE REAL -->
    <div class="card">
        <h2>1. &iquest;Qué es el Coste Real?</h2>
        <p>Cuando se compra un pez a un proveedor por <strong>0,48 €</strong>, ese no es su coste real. Ese pez necesita <strong>agua tratada, electricidad</strong> (iluminación, filtros, calentadores), <strong>espacio</strong> (alquiler) y otros gastos para mantenerse vivo hasta que se vende.</p>
        <p>El sistema <strong>reparte automáticamente</strong> todos los gastos operativos entre cada unidad en stock:</p>
        <div class="formula">
            Coste operativo por unidad = Total gastos operativos del período &divide; Total unidades en stock
        </div>
        <div class="formula">
            Coste real del producto = Precio de compra + Coste operativo por unidad
        </div>
        <p>El período de cálculo es <strong>configurable</strong>: mes actual, últimos 3 meses o últimos 6 meses.</p>
    </div>

    <!-- 2. EJEMPLO -->
    <div class="card-dark">
        <h2>2. Ejemplo Práctico</h2>
        <div class="info-box">
            <p><strong>Datos del mes:</strong> Gastos operativos = 600 € &middot; Unidades en stock = 300 &middot; <strong>Coste operativo/unidad = 2,00 €</strong></p>
        </div>
        <table>
            <tr>
                <th>Producto</th>
                <th>Coste compra</th>
                <th>+ Coste operativo</th>
                <th>= Coste real</th>
                <th>Precio venta</th>
                <th>Margen real</th>
            </tr>
            <tr>
                <td>Pez Ángel</td>
                <td class="num">0,48 €</td>
                <td class="num">2,00 €</td>
                <td class="num"><strong>2,48 €</strong></td>
                <td class="num">5,20 €</td>
                <td class="num"><span class="green">52,3%</span></td>
            </tr>
            <tr>
                <td>Planta Anubia</td>
                <td class="num">1,50 €</td>
                <td class="num">2,00 €</td>
                <td class="num"><strong>3,50 €</strong></td>
                <td class="num">6,00 €</td>
                <td class="num"><span class="green">41,7%</span></td>
            </tr>
            <tr>
                <td>Filtro externo</td>
                <td class="num">15,00 €</td>
                <td class="num">2,00 €</td>
                <td class="num"><strong>17,00 €</strong></td>
                <td class="num">25,00 €</td>
                <td class="num"><span class="green">32,0%</span></td>
            </tr>
        </table>
        <p>El pez que parece tener un margen del 90% (de 0,48 € a 5,20 €) en realidad tiene un <strong>52,3%</strong> cuando se incluyen los gastos operativos.</p>
    </div>

    <!-- 3. MARGEN REAL VS OBJETIVO -->
    <div class="card">
        <h2>3. Margen Real vs Margen Objetivo</h2>
        <p>El <strong>margen objetivo</strong> es el porcentaje de beneficio deseado. Se configura en Configuración (por defecto 30%).</p>
        <p>El <strong>margen real</strong> se calcula en tiempo real comparando los precios de venta con el coste real (compra + operativo).</p>
        <div class="formula">
            Margen real (%) = ((Precio venta - Coste real) &divide; Precio venta) × 100
        </div>
        <p>El dashboard muestra una barra de progreso con código de colores:</p>
        <table>
            <tr>
                <th>Estado</th>
                <th>Color</th>
                <th>Significado</th>
            </tr>
            <tr>
                <td><span class="dot dot-green"></span> Por encima del objetivo</td>
                <td class="green">Verde</td>
                <td>Todo va bien. No se necesita acción.</td>
            </tr>
            <tr>
                <td><span class="dot dot-amber"></span> Entre 70% y 100% del objetivo</td>
                <td class="amber">Ámbar</td>
                <td>Atención. El margen se está reduciendo.</td>
            </tr>
            <tr>
                <td><span class="dot dot-rose"></span> Por debajo del 70% o negativo</td>
                <td class="rose">Rojo</td>
                <td>Acción necesaria. Se está perdiendo dinero.</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 4. AJUSTE DE PRECIOS -->
    <div class="card">
        <h2>4. Ajuste Dinámico de Precios</h2>

        <h3>&iquest;Cómo funciona?</h3>
        <p>Cuando el margen real cae por debajo del objetivo, el sistema permite <strong>subir todos los precios de venta</strong> aplicando un porcentaje global desde Configuración.</p>

        <ol class="steps">
            <li>Ir a <strong>Configuración &gt; Ajuste de precios</strong> e introducir el porcentaje deseado (ej: +5%)</li>
            <li>El sistema muestra una <strong>vista previa</strong> con productos reales y sus precios antes/después</li>
            <li>Al confirmar, <strong>todos los precios se actualizan</strong> en la base de datos</li>
            <li>El TPV automáticamente cobra los <strong>nuevos precios</strong> en cada venta</li>
        </ol>

        <h3>&iquest;De dónde sale el porcentaje?</h3>
        <p>El porcentaje lo decide el propietario con la información que el sistema le proporciona:</p>
        <table>
            <tr><td>Margen real actual</td><td class="num"><span class="amber">22,0%</span></td></tr>
            <tr><td>Margen objetivo</td><td class="num"><span class="highlight">30,0%</span></td></tr>
            <tr><td>Diferencia</td><td class="num"><span class="rose">-8,0 puntos</span></td></tr>
        </table>
        <p>Con esta información, se decide cuánto subir. No tiene que ser exactamente la diferencia.</p>

        <h3>Seguridad: precios originales</h3>
        <div class="warning-box">
            <p><strong>El sistema guarda los precios originales.</strong> Se puede revertir con un solo botón en cualquier momento. Si se cambia el porcentaje, siempre se calcula desde el precio original, nunca desde el ya ajustado (evita subidas acumuladas).</p>
        </div>

        <h3>Productos nuevos</h3>
        <p>Si se da de alta un producto mientras hay un ajuste activo, el sistema automáticamente:</p>
        <table>
            <tr><td>1. Calcula su precio normal con el margen automático</td></tr>
            <tr><td>2. Aplica el ajuste activo al precio de venta</td></tr>
            <tr><td>3. Guarda el precio base para revertir cuando se desactive</td></tr>
        </table>
    </div>

    <!-- 5. HORAS PICO -->
    <div class="card-dark">
        <h2>5. Detección de Horas Pico</h2>
        <p>El sistema analiza las ventas y detecta la <strong>hora del día con más transacciones</strong>.</p>
        <p>Cuando se cumplen estas condiciones:</p>
        <table>
            <tr><td>Se ha detectado una hora pico significativa</td><td class="num">✓</td></tr>
            <tr><td>El margen real está por debajo del objetivo</td><td class="num">✓</td></tr>
            <tr><td>Hay al menos 3 tickets en el período</td><td class="num">✓</td></tr>
        </table>
        <p>El dashboard muestra una <strong>tarjeta de sugerencia</strong>:</p>
        <div class="info-box">
            <p><strong>Hora pico detectada (14:00h)</strong></p>
            <p>Margen real <span class="amber">22,0%</span> — Objetivo <span class="highlight">30,0%</span> — Subir <strong>+8,0%</strong></p>
            <p style="margin-top: 4px;">[Botón: Ajustar precios → lleva a Configuración]</p>
        </div>
        <div class="warning-box">
            <p><strong>El sistema nunca sube precios automáticamente.</strong> Solo informa y sugiere. La decisión siempre es del propietario.</p>
        </div>
    </div>

    <!-- 6. IVA -->
    <div class="card">
        <h2>6. Gestión del IVA en Gastos</h2>
        <p>Cuando se recibe una factura de luz de <strong>110 €</strong>, en realidad:</p>
        <table>
            <tr><td>Base imponible (coste real)</td><td class="num"><strong>100,00 €</strong></td></tr>
            <tr><td>IVA 10% (deducible)</td><td class="num">10,00 €</td></tr>
            <tr><td>Total factura</td><td class="num">110,00 €</td></tr>
        </table>
        <p>Para calcular el coste real del negocio, solo se usa la <strong>base imponible</strong>. El IVA es deducible y se recupera en la liquidación trimestral.</p>

        <h3>Al introducir un gasto</h3>
        <p>El formulario pide la <strong>base imponible</strong> y el <strong>% de IVA</strong>. El total se calcula automáticamente en tiempo real.</p>

        <h3>Tipos de IVA habituales</h3>
        <table>
            <tr><th>Gasto</th><th>IVA</th></tr>
            <tr><td>Electricidad</td><td class="num">10%</td></tr>
            <tr><td>Agua</td><td class="num">10%</td></tr>
            <tr><td>Teléfono / Internet</td><td class="num">21%</td></tr>
            <tr><td>Alquiler</td><td class="num">21%</td></tr>
            <tr><td>Seguros</td><td class="num">0% (exento)</td></tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 7. RESUMEN FISCAL -->
    <div class="card-dark">
        <h2>7. Resumen Fiscal en el Dashboard</h2>
        <p>El dashboard incluye una sección de resumen fiscal que muestra de un vistazo:</p>
        <table>
            <tr>
                <th>Concepto</th>
                <th>Qué es</th>
                <th>Ejemplo</th>
            </tr>
            <tr>
                <td><span class="green">IVA cobrado (repercutido)</span></td>
                <td>El IVA que se cobra a los clientes en cada venta</td>
                <td class="num">450,00 €</td>
            </tr>
            <tr>
                <td><span class="rose">IVA pagado (soportado)</span></td>
                <td>El IVA pagado en los gastos operativos</td>
                <td class="num">120,00 €</td>
            </tr>
            <tr>
                <td><span class="amber">Balance (a pagar)</span></td>
                <td>La diferencia: lo que se ingresa a Hacienda</td>
                <td class="num">330,00 €</td>
            </tr>
        </table>
    </div>

    <!-- 8. DOBLE BENEFICIO -->
    <div class="card">
        <h2>8. Doble Vista de Beneficio</h2>
        <p>El dashboard muestra el beneficio de dos formas lado a lado:</p>
        <table class="two-col">
            <tr>
                <td>
                    <div class="info-box">
                        <p><strong>Beneficio sin IVA</strong></p>
                        <p>Ingresos - Compras - Servicios - Gastos (solo base)</p>
                        <p style="margin-top: 6px;"><strong>Para tomar decisiones de negocio:</strong> &iquest;subo precios? &iquest;estoy ganando dinero?</p>
                    </div>
                </td>
                <td>
                    <div class="info-box">
                        <p><strong>Beneficio con IVA</strong></p>
                        <p>Ingresos - Compras - Servicios - Gastos (total con IVA)</p>
                        <p style="margin-top: 6px;"><strong>Para saber el flujo de caja:</strong> &iquest;hay dinero en la cuenta hoy?</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 9. FLUJO -->
    <div class="card">
        <h2>9. Flujo Completo de Uso</h2>
        <ol class="steps">
            <li><strong>Registrar gastos:</strong> Cada mes, ir a Gastos y añadir las facturas (luz, agua, alquiler...) con su base imponible y % IVA</li>
            <li><strong>Consultar Dashboard:</strong> Ver el margen real calculado automáticamente, la comparación con el objetivo y el resumen fiscal</li>
            <li><strong>Detectar desviaciones:</strong> Si el margen baja, la barra se pone ámbar/roja. Si hay hora pico, aparece la sugerencia</li>
            <li><strong>Ajustar precios:</strong> Ir a Configuración, poner el % deseado, revisar vista previa y confirmar</li>
            <li><strong>Monitorizar:</strong> Seguir consultando el dashboard para ver cómo evoluciona el margen con los nuevos precios</li>
            <li><strong>Revertir:</strong> Cuando se desee, restaurar todos los precios originales con un solo clic</li>
        </ol>
    </div>

    <div class="footer">
        <p>WetFish — Sistema de Coste Real y Precio Dinámico &middot; Documento generado en marzo 2026</p>
        <p>Todos los cálculos se realizan en tiempo real sobre los datos reales del sistema.</p>
    </div>

</body>
</html>
