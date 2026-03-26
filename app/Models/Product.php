<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'code', 'name', 'cost_price', 'sale_price', 'base_sale_price',
        'stock', 'min_stock', 'auto_margin', 'photo',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'base_sale_price' => 'decimal:2',
            'auto_margin' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ticketItems()
    {
        return $this->hasMany(TicketItem::class);
    }

    public function stockLosses()
    {
        return $this->hasMany(StockLoss::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) return 'critical';
        if ($this->stock <= $this->min_stock) return 'low';
        return 'ok';
    }
}
