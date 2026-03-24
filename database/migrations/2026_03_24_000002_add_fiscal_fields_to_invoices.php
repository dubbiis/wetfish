<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('invoices', 'transport_cost')) return;

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal_products', 10, 2)->default(0)->after('extra_costs');
            $table->decimal('transport_cost', 10, 2)->default(0)->after('subtotal_products');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('transport_cost');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal_products', 'transport_cost', 'discount_amount', 'vat_rate', 'vat_amount']);
        });
    }
};
