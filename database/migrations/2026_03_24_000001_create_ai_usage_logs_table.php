<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_usage_logs')) return;

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');           // invoice, expense
            $table->string('model');           // gpt-4o-mini, etc.
            $table->integer('tokens_input');
            $table->integer('tokens_output');
            $table->decimal('cost_eur', 8, 6); // coste en euros
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
