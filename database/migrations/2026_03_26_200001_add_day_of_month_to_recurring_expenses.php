<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('recurring_expenses', 'day_of_month')) return;

        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_month')->default(1)->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->dropColumn('day_of_month');
        });
    }
};
