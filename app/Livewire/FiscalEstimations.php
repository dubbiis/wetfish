<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Estimaciones Fiscales')]
class FiscalEstimations extends Component
{
    public int $selectedYear;
    public int $selectedQuarter;

    // Tramos IRPF generales 2024-2026 (estatal + autonómica simplificada)
    private const IRPF_BRACKETS = [
        [12450,  0.19],
        [7750,   0.24],  // 20200 - 12450
        [15000,  0.30],  // 35200 - 20200
        [24800,  0.37],  // 60000 - 35200
        [240000, 0.45],  // 300000 - 60000
        [PHP_FLOAT_MAX, 0.47],
    ];

    public function mount(): void
    {
        $this->selectedYear    = (int) now()->year;
        $this->selectedQuarter = (int) ceil(now()->month / 3);
    }

    public function setQuarter(int $quarter): void
    {
        $this->selectedQuarter = $quarter;
    }

    public function setYear(int $year): void
    {
        $this->selectedYear = $year;
    }

    private function getQuarterRange(int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        return [
            'start' => Carbon::create($year, $startMonth, 1)->startOfDay(),
            'end'   => Carbon::create($year, $startMonth, 1)->addMonths(3)->subSecond(),
        ];
    }

    private function getQuarterLabel(int $quarter): string
    {
        return match ($quarter) {
            1 => 'T1 (Ene–Mar)',
            2 => 'T2 (Abr–Jun)',
            3 => 'T3 (Jul–Sep)',
            4 => 'T4 (Oct–Dic)',
        };
    }

    /**
     * Modelo 303 — IVA trimestral
     */
    private function calculateModelo303(int $year, int $quarter): array
    {
        $range = $this->getQuarterRange($year, $quarter);

        $ivaRepercutido = (float) Ticket::whereBetween('created_at', [$range['start'], $range['end']])
            ->sum('tax_amount');

        $ivaSoportadoExpenses = (float) Expense::whereBetween('date', [$range['start'], $range['end']])
            ->sum('tax_amount');

        $ivaSoportadoInvoices = (float) Invoice::whereBetween('invoice_date', [$range['start'], $range['end']])
            ->sum('vat_amount');

        $ivaSoportado = $ivaSoportadoExpenses + $ivaSoportadoInvoices;
        $resultado = round($ivaRepercutido - $ivaSoportado, 2);

        return compact('ivaRepercutido', 'ivaSoportado', 'ivaSoportadoExpenses', 'ivaSoportadoInvoices', 'resultado');
    }

    /**
     * Modelo 130 — Pago fraccionado IRPF (20% del beneficio)
     */
    private function calculateModelo130(int $year, int $quarter): array
    {
        $range = $this->getQuarterRange($year, $quarter);

        $revenueBase = (float) Ticket::whereBetween('created_at', [$range['start'], $range['end']])
            ->sum('subtotal');

        $deductibleExpenses = (float) Expense::whereBetween('date', [$range['start'], $range['end']])
            ->sum('amount');

        $deductibleInvoices = (float) Invoice::whereBetween('invoice_date', [$range['start'], $range['end']])
            ->selectRaw('COALESCE(SUM(COALESCE(subtotal_products, 0) + COALESCE(transport_cost, 0)), 0) as total')
            ->value('total');

        $totalDeductible = $deductibleExpenses + $deductibleInvoices;
        $baseImponible = round($revenueBase - $totalDeductible, 2);
        $cuota = $baseImponible > 0 ? round($baseImponible * 0.20, 2) : 0;

        return compact('revenueBase', 'deductibleExpenses', 'deductibleInvoices', 'totalDeductible', 'baseImponible', 'cuota');
    }

    /**
     * Estimación de Renta anual (IRPF)
     */
    private function calculateRentaAnual(int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd   = Carbon::create($year, 12, 31, 23, 59, 59);

        $totalRevenue = (float) Ticket::whereBetween('created_at', [$yearStart, $yearEnd])
            ->sum('subtotal');

        $totalDeductibleExpenses = (float) Expense::whereBetween('date', [$yearStart, $yearEnd])
            ->sum('amount');

        $totalDeductibleInvoices = (float) Invoice::whereBetween('invoice_date', [$yearStart, $yearEnd])
            ->selectRaw('COALESCE(SUM(COALESCE(subtotal_products, 0) + COALESCE(transport_cost, 0)), 0) as total')
            ->value('total');

        $totalDeductible = $totalDeductibleExpenses + $totalDeductibleInvoices;
        $baseImponible = round($totalRevenue - $totalDeductible, 2);

        // Sumar pagos fraccionados del 130 ya realizados
        $modelo130Paid = 0;
        for ($q = 1; $q <= 4; $q++) {
            $m130 = $this->calculateModelo130($year, $q);
            if ($m130['cuota'] > 0) {
                $modelo130Paid += $m130['cuota'];
            }
        }

        $irpfEstimado = $this->calculateIRPF($baseImponible);
        $restanteRenta = round($irpfEstimado - $modelo130Paid, 2);

        return compact('totalRevenue', 'totalDeductible', 'baseImponible', 'modelo130Paid', 'irpfEstimado', 'restanteRenta');
    }

    /**
     * Calcula IRPF por tramos
     */
    private function calculateIRPF(float $base): float
    {
        if ($base <= 0) return 0;

        $tax = 0;
        $remaining = $base;

        foreach (self::IRPF_BRACKETS as [$limit, $rate]) {
            $taxable = min($remaining, $limit);
            $tax += $taxable * $rate;
            $remaining -= $taxable;
            if ($remaining <= 0) break;
        }

        return round($tax, 2);
    }

    public function render()
    {
        $modelo303 = $this->calculateModelo303($this->selectedYear, $this->selectedQuarter);
        $modelo130 = $this->calculateModelo130($this->selectedYear, $this->selectedQuarter);
        $renta     = $this->calculateRentaAnual($this->selectedYear);
        $quarterLabel = $this->getQuarterLabel($this->selectedQuarter);

        Log::info('FiscalEstimations rendered', [
            'year' => $this->selectedYear,
            'quarter' => $this->selectedQuarter,
            '303_resultado' => $modelo303['resultado'],
            '130_cuota' => $modelo130['cuota'],
        ]);

        return view('livewire.fiscal-estimations', compact('modelo303', 'modelo130', 'renta', 'quarterLabel'));
    }
}
