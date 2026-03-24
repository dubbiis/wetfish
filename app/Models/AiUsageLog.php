<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'type', 'model', 'tokens_input', 'tokens_output', 'cost_eur',
    ];

    protected function casts(): array
    {
        return [
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'cost_eur' => 'decimal:6',
        ];
    }

    /**
     * Calcula el coste en EUR según el modelo y los tokens usados.
     * Precios por 1M tokens (USD, convertido a EUR con factor 0.92).
     */
    public static function calculateCost(string $model, int $tokensIn, int $tokensOut): float
    {
        // Precios por 1M tokens en USD (marzo 2026)
        $pricing = [
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
            'gpt-4o'      => ['input' => 2.50, 'output' => 10.00],
            'gpt-4.1'     => ['input' => 2.00, 'output' => 8.00],
            'gpt-4.1-mini'=> ['input' => 0.40, 'output' => 1.60],
        ];

        $usdToEur = 0.92;
        $rates = $pricing[$model] ?? $pricing['gpt-4o-mini'];

        $costUsd = ($tokensIn / 1_000_000 * $rates['input'])
                 + ($tokensOut / 1_000_000 * $rates['output']);

        return round($costUsd * $usdToEur, 6);
    }
}
