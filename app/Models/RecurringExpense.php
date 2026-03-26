<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RecurringExpense extends Model
{
    protected $fillable = [
        'expense_category_id', 'concept', 'amount', 'tax_rate',
        'frequency', 'is_active', 'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'last_generated_at' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getTaxAmountAttribute(): float
    {
        return round((float) $this->amount * (float) $this->tax_rate / 100, 2);
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->amount + $this->tax_amount, 2);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'monthly'   => 'Mensual',
            'quarterly' => 'Trimestral',
            'annual'    => 'Anual',
            default     => $this->frequency,
        };
    }

    /**
     * Genera gastos pendientes para todos los gastos fijos activos.
     * Throttled: solo se ejecuta 1 vez al día.
     */
    public static function generatePendingExpenses(): int
    {
        $lastCheck = Setting::get('recurring_expenses_last_check');
        if ($lastCheck === now()->toDateString()) {
            return 0;
        }

        $count = 0;
        $actives = static::where('is_active', true)->get();

        foreach ($actives as $recurring) {
            $dates = $recurring->getMissingDates();

            foreach ($dates as $date) {
                Expense::create([
                    'expense_category_id' => $recurring->expense_category_id,
                    'concept'             => $recurring->concept,
                    'amount'              => $recurring->amount,
                    'tax_rate'            => $recurring->tax_rate,
                    'tax_amount'          => $recurring->tax_amount,
                    'date'                => $date,
                    'notes'               => 'Generado automáticamente (gasto fijo)',
                    'recurring_expense_id' => $recurring->id,
                ]);
                $count++;
            }

            if (!empty($dates)) {
                $recurring->update(['last_generated_at' => now()->toDateString()]);
            }
        }

        Setting::set('recurring_expenses_last_check', now()->toDateString());

        if ($count > 0) {
            Log::info("RecurringExpense: generados {$count} gastos automáticos");
        }

        return $count;
    }

    /**
     * Calcula las fechas de gastos que faltan por generar.
     */
    public function getMissingDates(): array
    {
        $startDate = $this->last_generated_at
            ? $this->last_generated_at->copy()
            : ($this->created_at ? $this->created_at->copy()->startOfMonth() : now()->startOfMonth());

        $today = now();
        $dates = [];

        switch ($this->frequency) {
            case 'monthly':
                // Avanzar al siguiente mes desde la última generación
                $current = $startDate->copy()->startOfMonth();
                if ($this->last_generated_at) {
                    $current->addMonth();
                }
                while ($current->lte($today->copy()->startOfMonth())) {
                    if (!$this->hasExpenseForDate($current)) {
                        $dates[] = $current->copy()->toDateString();
                    }
                    $current->addMonth();
                }
                break;

            case 'quarterly':
                // Trimestres: Ene, Abr, Jul, Oct
                $current = $startDate->copy()->firstOfQuarter();
                if ($this->last_generated_at) {
                    $current->addMonths(3);
                }
                while ($current->lte($today->copy()->firstOfQuarter())) {
                    if (!$this->hasExpenseForDate($current)) {
                        $dates[] = $current->copy()->toDateString();
                    }
                    $current->addMonths(3);
                }
                break;

            case 'annual':
                $current = $startDate->copy()->startOfYear();
                if ($this->last_generated_at) {
                    $current->addYear();
                }
                while ($current->lte($today->copy()->startOfYear())) {
                    if (!$this->hasExpenseForDate($current)) {
                        $dates[] = $current->copy()->toDateString();
                    }
                    $current->addYear();
                }
                break;
        }

        return $dates;
    }

    /**
     * Comprueba si ya existe un gasto generado para esta fecha.
     */
    private function hasExpenseForDate(Carbon $date): bool
    {
        return Expense::where('recurring_expense_id', $this->id)
            ->where('date', $date->toDateString())
            ->exists();
    }
}
