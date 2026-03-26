<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLoss extends Model
{
    protected $fillable = [
        'product_id', 'quantity', 'reason', 'notes',
        'unit_cost', 'total_cost', 'date',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost'  => 'decimal:2',
            'total_cost' => 'decimal:2',
            'date'       => 'date',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public const REASONS = [
        'muerto'    => 'Muerto',
        'enfermo'   => 'Enfermo',
        'devuelto'  => 'Devuelto',
        'danado'    => 'Dañado',
        'otro'      => 'Otro',
    ];
}
