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
     * Busca un alias por proveedor (código o nombre).
     * Devuelve el alias o null. Si product_id = 0, es un item excluido (desechable).
     */
    public static function findAlias(?int $supplierId, ?string $code, ?string $name): ?self
    {
        if (!$supplierId) return null;

        if ($code) {
            $alias = static::where('supplier_id', $supplierId)
                ->where('supplier_code', $code)
                ->first();
            if ($alias) return $alias;
        }

        if ($name) {
            $alias = static::where('supplier_id', $supplierId)
                ->where('supplier_name', $name)
                ->first();
            if ($alias) return $alias;
        }

        return null;
    }

    /**
     * Busca un producto por alias. Devuelve null si no hay alias o si es excluido.
     */
    public static function findProduct(?int $supplierId, ?string $code, ?string $name): ?Product
    {
        $alias = static::findAlias($supplierId, $code, $name);
        if (!$alias || $alias->product_id === 0) return null;
        return $alias->product;
    }

    /**
     * Verifica si un item está marcado como excluido (desechable).
     */
    public static function isExcluded(?int $supplierId, ?string $code, ?string $name): bool
    {
        $alias = static::findAlias($supplierId, $code, $name);
        return $alias && $alias->product_id === 0;
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
