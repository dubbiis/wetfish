<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['expense_category_id', 'concept', 'amount', 'tax_rate', 'tax_amount', 'date', 'notes', 'recurring_expense_id'];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'tax_rate'   => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'date'       => 'date',
        ];
    }

    /**
     * Total con IVA incluido.
     */
    public function getTotalAttribute(): float
    {
        return round((float) $this->amount + (float) $this->tax_amount, 2);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function recurringExpense()
    {
        return $this->belongsTo(RecurringExpense::class);
    }
}
