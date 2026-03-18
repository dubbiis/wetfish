<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans Mono, monospace; font-size: 10px; color: #000; width: 100%; }
        .ticket { padding: 10px 5px; page-break-after: always; }
        .ticket:last-child { page-break-after: auto; }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .shop-name { font-size: 16px; font-weight: bold; border: 2px solid #000; padding: 6px 12px; display: inline-block; margin: 8px 0; }

        .header { text-align: center; margin-bottom: 10px; }
        .header p { font-size: 9px; margin-bottom: 1px; }

        .separator { border-top: 1px dashed #000; margin: 8px 0; }
        .separator-double { border-top: 2px solid #000; margin: 8px 0; }

        .info-row { width: 100%; margin-bottom: 2px; }
        .info-row td { font-size: 10px; }
        .info-row .label { text-align: left; }
        .info-row .value { text-align: right; }

        .items { width: 100%; border-collapse: collapse; }
        .items td { padding: 3px 0; font-size: 10px; vertical-align: top; }
        .items .code { width: 50px; }
        .items .desc { }
        .items .price { text-align: right; width: 70px; }

        .totals { width: 100%; border-collapse: collapse; }
        .totals td { padding: 2px 0; font-size: 10px; }
        .totals .total-row td { font-size: 13px; font-weight: bold; padding-top: 6px; }

        .footer { text-align: center; margin-top: 12px; font-size: 8px; color: #555; }
    </style>
</head>
<body>
    @foreach($tickets as $ticket)
    <div class="ticket">
        {{-- Cabecera empresa --}}
        <div class="header">
            @if($business['address'])
                <p>{{ $business['address'] }}</p>
            @endif
            @if($business['phone'])
                <p>Tel: {{ $business['phone'] }}</p>
            @endif

            <div class="shop-name">{{ $business['name'] }}</div>

            @if($business['cif'])
                <p>CIF: {{ $business['cif'] }}</p>
            @endif
        </div>

        {{-- Fecha y ticket --}}
        <table class="info-row" style="width:100%">
            <tr>
                <td class="label">{{ $ticket->created_at->format('D') }}&nbsp;&nbsp;{{ $ticket->created_at->format('d/m/Y') }}&nbsp;&nbsp;{{ $ticket->created_at->format('H:i') }}</td>
            </tr>
        </table>

        <div class="separator"></div>

        {{-- Productos --}}
        <table class="items">
            @foreach($ticket->items as $item)
            <tr>
                <td class="code">{{ $item->product?->code ?? '---' }}</td>
                <td class="desc">{{ $item->product?->name ?? 'Producto' }} x {{ $item->quantity }}</td>
                <td class="price">&euro;{{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>

        <div class="separator"></div>

        {{-- Totales --}}
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="right">&euro;{{ number_format($ticket->subtotal, 2, ',', '.') }}</td>
            </tr>
            @if($ticket->discount_value > 0)
            <tr>
                <td>Descuento</td>
                <td class="right">-&euro;{{ number_format($ticket->discount_value, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td>IVA ({{ $ticket->tax_rate }}%)</td>
                <td class="right">&euro;{{ number_format($ticket->tax_amount, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="separator-double"></div>

        <table class="totals">
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="right">&euro;{{ number_format($ticket->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="separator"></div>

        {{-- Info adicional --}}
        <table class="info-row" style="width:100%">
            <tr>
                <td class="label">Vendedor:</td>
                <td class="value">{{ $ticket->user?->name ?? 'Usuario' }}</td>
            </tr>
            <tr>
                <td class="label">Ticket #</td>
                <td class="value">{{ $ticket->id }}</td>
            </tr>
        </table>

        <div class="separator"></div>

        {{-- Pie --}}
        <div class="footer">
            <p>Gracias por su compra</p>
            <p>{{ $business['name'] }}</p>
        </div>
    </div>
    @endforeach
</body>
</html>
