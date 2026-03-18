<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'type', 'supplier_id', 'invoice_number', 'invoice_date',
        'concept', 'total', 'extra_costs', 'file_path',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'total' => 'decimal:2',
            'extra_costs' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }
}
