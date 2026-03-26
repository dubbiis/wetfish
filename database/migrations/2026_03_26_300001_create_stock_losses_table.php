<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_losses')) return;

        Schema::create('stock_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason'); // muerto, enfermo, devuelto, dañado, otro
            $table->text('notes')->nullable();
            $table->decimal('unit_cost', 10, 2); // coste unitario en el momento de la merma
            $table->decimal('total_cost', 10, 2); // quantity × unit_cost
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_losses');
    }
};
