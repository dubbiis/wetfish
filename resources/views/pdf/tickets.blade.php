<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        .ticket { page-break-after: always; padding: 30px; }
        .ticket:last-child { page-break-after: auto; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7c3bed; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #7c3bed; margin-bottom: 4px; }
        .header p { font-size: 10px; color: #666; }
        .ticket-info { display: table; width: 100%; margin-bottom: 15px; }
        .ticket-info .left, .ticket-info .right { display: table-cell; width: 50%; }
        .ticket-info .right { text-align: right; }
        .ticket-info p { margin-bottom: 3px; font-size: 11px; }
        .ticket-info .label { color: #888; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #7c3bed; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        th:last-child { text-align: right; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        td:last-child { text-align: right; }
        .totals { width: 250px; margin-left: auto; }
        .totals .row { display: table; width: 100%; margin-bottom: 4px; }
        .totals .row .label, .totals .row .value { display: table-cell; }
        .totals .row .value { text-align: right; }
        .totals .row.total { border-top: 2px solid #7c3bed; padding-top: 8px; margin-top: 8px; font-size: 14px; font-weight: bold; color: #7c3bed; }
        .totals .row.discount .value { color: #e53e3e; }
        .seller { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 10px; color: #888; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
    @foreach($tickets as $ticket)
    <div class="ticket">
        <div class="header">
            <h1>{{ $business['name'] }}</h1>
            @if($business['cif'])
                <p>CIF: {{ $business['cif'] }}</p>
            @endif
            @if($business['address'])
                <p>{{ $business['address'] }}</p>
            @endif
            @if($business['phone'])
                <p>Tel: {{ $business['phone'] }}</p>
            @endif
        </div>

        <div class="ticket-info">
            <div class="left">
                <p class="label">Ticket</p>
                <p><strong>#{{ $ticket->id }}</strong></p>
            </div>
            <div class="right">
                <p class="label">Fecha</p>
                <p>{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>P. Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ticket->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'Producto eliminado' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>&euro; {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td>&euro; {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="row">
                <span class="label">Subtotal</span>
                <span class="value">&euro; {{ number_format($ticket->subtotal, 2, ',', '.') }}</span>
            </div>
            @if($ticket->discount_value > 0)
            <div class="row discount">
                <span class="label">Descuento</span>
                <span class="value">-&euro; {{ number_format($ticket->discount_value, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">IVA ({{ $ticket->tax_rate }}%)</span>
                <span class="value">&euro; {{ number_format($ticket->tax_amount, 2, ',', '.') }}</span>
            </div>
            <div class="row total">
                <span class="label">TOTAL</span>
                <span class="value">&euro; {{ number_format($ticket->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="seller">
            Vendedor: {{ $ticket->user?->name ?? 'Usuario' }}
        </div>

        <div class="footer">
            Gracias por su compra &bull; {{ $business['name'] }}
        </div>
    </div>
    @endforeach
</body>
</html>
