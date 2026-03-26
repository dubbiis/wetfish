<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recurring_expenses')) return;

        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->string('concept');
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('frequency')->default('monthly'); // monthly, quarterly, annual
            $table->boolean('is_active')->default(true);
            $table->date('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
