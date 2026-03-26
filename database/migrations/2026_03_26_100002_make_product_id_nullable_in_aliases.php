<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_supplier_aliases')) return;

        Schema::table('product_supplier_aliases', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->default(0)->change();
        });
    }

    public function down(): void
    {
        // No revertir
    }
};
