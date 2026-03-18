<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            color: #000;
            width: 80mm;
            padding: 4mm;
        }
        .ticket {
            page-break-after: always;
            width: 100%;
        }
        .ticket:last-child { page-break-after: auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .shop-name {
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 4px 10px;
            display: inline-block;
            margin: 4px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .header p { font-size: 8px; line-height: 1.3; }
        .sep {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        .sep-bold {
            border: none;
            border-top: 2px solid #000;
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td { font-size: 9px; padding: 1px 0; vertical-align: top; }
        .total-line td {
            font-size: 12px;
            font-weight: bold;
            padding-top: 4px;
            padding-bottom: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 8px;
            color: #333;
        }
    </style>
</head>
<body>
    @foreach($tickets as $ticket)
    <div class="ticket">
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

        <table>
            <tr>
                <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                <td class="right">{{ $ticket->created_at->format('H:i') }}</td>
            </tr>
        </table>

        <hr class="sep">

        <table>
            @foreach($ticket->items as $item)
            <tr>
                <td style="width:45px">{{ $item->product?->code ?? '---' }}</td>
                <td>{{ $item->product?->name ?? 'Producto' }} x {{ $item->quantity }}</td>
                <td class="right" style="width:50px">&euro;{{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>

        <hr class="sep">

        <table>
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

        <hr class="sep-bold">

        <table>
            <tr class="total-line">
                <td>TOTAL:</td>
                <td class="right">&euro;{{ number_format($ticket->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        <hr class="sep">

        <table>
            <tr>
                <td>Vendedor:</td>
                <td class="right">{{ $ticket->user?->name ?? 'Usuario' }}</td>
            </tr>
            <tr>
                <td>Ticket:</td>
                <td class="right">#{{ $ticket->id }}</td>
            </tr>
        </table>

        <hr class="sep">

        <div class="footer">
            <p>Gracias por su compra</p>
            <p>{{ $business['name'] }}</p>
        </div>
    </div>
    @endforeach
</body>
</html>
