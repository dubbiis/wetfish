<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_supplier_aliases')) return;

        Schema::create('product_supplier_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_code')->nullable();
            $table->string('supplier_name');
            $table->timestamps();

            $table->index(['supplier_id', 'supplier_code']);
            $table->index(['supplier_id', 'supplier_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier_aliases');
    }
};
