<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'type', 'supplier_id', 'invoice_number', 'invoice_date',
        'concept', 'total', 'extra_costs', 'subtotal_products',
        'transport_cost', 'discount_amount', 'vat_rate', 'vat_amount', 'file_path',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'total' => 'decimal:2',
            'extra_costs' => 'decimal:2',
            'subtotal_products' => 'decimal:2',
            'transport_cost' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
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
