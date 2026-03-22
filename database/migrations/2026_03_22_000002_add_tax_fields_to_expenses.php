<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('expenses', 'tax_rate')) return;

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(21)->after('amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            // amount pasa a ser la base imponible (sin IVA)
            // total = amount + tax_amount
        });

        // Para gastos existentes: asumir que amount YA incluye IVA al 21%
        // base = amount / 1.21, tax_amount = amount - base
        DB::table('expenses')->update([
            'tax_rate' => 21,
            'tax_amount' => DB::raw('ROUND(amount - amount / 1.21, 2)'),
            'amount' => DB::raw('ROUND(amount / 1.21, 2)'),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('expenses', 'tax_rate')) return;

        // Restaurar amount como total (base + IVA)
        DB::table('expenses')->update([
            'amount' => DB::raw('ROUND(amount + tax_amount, 2)'),
        ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'tax_amount']);
        });
    }
};
