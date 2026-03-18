<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketExportController extends Controller
{
    public function export(Request $request)
    {
        try {
            $ids = explode(',', $request->query('ids', ''));
            $ids = array_filter($ids, fn($id) => is_numeric($id));

            if (empty($ids)) {
                abort(404, 'No se han seleccionado tickets');
            }

            $tickets = Ticket::with(['user', 'items.product'])
                ->whereIn('id', $ids)
                ->orderByDesc('created_at')
                ->get();

            if ($tickets->isEmpty()) {
                abort(404, 'No se encontraron tickets');
            }

            $business = [
                'name' => Setting::get('business_name', 'WetFish'),
                'cif' => Setting::get('business_cif', ''),
                'address' => Setting::get('business_address', ''),
                'phone' => Setting::get('business_phone', ''),
            ];

            $pdf = Pdf::loadView('pdf.tickets', [
                'tickets' => $tickets,
                'business' => $business,
            ]);

            $pdf->setPaper('A4', 'portrait');

            $filename = count($tickets) === 1
                ? "ticket-{$tickets->first()->id}.pdf"
                : "tickets-" . now()->format('Y-m-d') . ".pdf";

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Error generando PDF de tickets: ' . $e->getMessage(), [
                'ids' => $request->query('ids'),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Error generando PDF: ' . $e->getMessage());
        }
    }
}
