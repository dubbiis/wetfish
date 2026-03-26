<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplierAlias extends Model
{
    protected $fillable = ['product_id', 'supplier_id', 'supplier_code', 'supplier_name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Busca un producto por alias de proveedor (código o nombre).
     */
    public static function findProduct(?int $supplierId, ?string $code, ?string $name): ?Product
    {
        if (!$supplierId) return null;

        // Primero por código (más fiable)
        if ($code) {
            $alias = static::where('supplier_id', $supplierId)
                ->where('supplier_code', $code)
                ->first();
            if ($alias) return $alias->product;
        }

        // Luego por nombre exacto
        if ($name) {
            $alias = static::where('supplier_id', $supplierId)
                ->where('supplier_name', $name)
                ->first();
            if ($alias) return $alias->product;
        }

        return null;
    }

    /**
     * Guarda un alias si no existe ya.
     */
    public static function saveAlias(int $productId, ?int $supplierId, ?string $code, ?string $name): void
    {
        if (!$supplierId || !$name) return;

        static::updateOrCreate(
            [
                'supplier_id' => $supplierId,
                'supplier_name' => $name,
            ],
            [
                'product_id' => $productId,
                'supplier_code' => $code ?: null,
            ]
        );
    }
}
