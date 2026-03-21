<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'base_sale_price')) return;

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('base_sale_price', 10, 2)->nullable()->after('sale_price');
        });

        // Inicializar base_sale_price = sale_price para productos existentes
        DB::table('products')->whereNull('base_sale_price')->update([
            'base_sale_price' => DB::raw('sale_price'),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'base_sale_price')) return;

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('base_sale_price');
        });
    }
};
