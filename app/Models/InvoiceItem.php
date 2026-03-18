<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'code', 'name',
        'quantity', 'unit_cost', 'total', 'is_new_product',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'is_new_product' => 'boolean',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
