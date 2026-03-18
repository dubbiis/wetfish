<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 5mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #000;
        }
        .ticket {
            page-break-after: always;
            width: 100%;
        }
        .ticket:last-child { page-break-after: auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        .shop-name {
            font-size: 15px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 3px 12px;
            display: inline-block;
            margin: 5px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header p { font-size: 9px; line-height: 1.4; }
        .sep {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .sep-bold {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            font-size: 10px;
            padding: 2px 0;
            vertical-align: top;
        }
        .total-line td {
            font-size: 14px;
            font-weight: bold;
            padding: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
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
                <td>Ticket #{{ $ticket->id }}</td>
                <td class="right">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <hr class="sep">

        <table>
            @foreach($ticket->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? 'Producto' }} x{{ $item->quantity }}</td>
                <td class="right" style="white-space:nowrap">&euro;{{ number_format($item->subtotal, 2, ',', '.') }}</td>
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
        </table>

        <div class="footer">
            <p>Gracias por su compra</p>
        </div>
    </div>
    @endforeach
</body>
</html>
