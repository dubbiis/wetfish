<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Smalot\PdfParser\Parser as PdfParser;

class InvoiceVisionService
{
    private const INVOICE_PROMPT = <<<'PROMPT'
Eres un sistema de extracción de datos de facturas de proveedores de acuariofilia (plantas acuáticas, peces, accesorios). Analiza el documento proporcionado y extrae ABSOLUTAMENTE TODOS los productos/artículos listados.

Devuelve EXCLUSIVAMENTE un JSON válido con esta estructura exacta, sin texto adicional:

{
  "invoice_number": "string o null",
  "invoice_date": "YYYY-MM-DD o null",
  "supplier_name": "nombre de la empresa proveedora o null",
  "summary": {
    "subtotal_products": 0.00,
    "discount_percentage": 0,
    "discount_amount": 0.00,
    "transport_cost": 0.00,
    "transport_detail": "descripción del transporte (ej: 2 cajas x 14.50€)",
    "other_costs": 0.00,
    "vat_rate": 0,
    "vat_amount": 0.00,
    "total": 0.00
  },
  "items": [
    {
      "code": "código/referencia del producto, cadena vacía si no hay",
      "name": "nombre completo del producto/planta/pez",
      "quantity": 1,
      "unit_cost": 0.00
    }
  ]
}

Reglas CRÍTICAS:
- Extrae ABSOLUTAMENTE TODAS las líneas de productos. NO omitas ninguna, aunque haya 30, 50 o más líneas.
- Incluye CADA fila de la tabla de productos, incluso si tiene cantidad 0.
- Los precios deben ser unitarios y SIN IVA (neto). Usa el precio de la columna "E.-Preis" o "Price" o "Precio unitario".
- Si un producto tiene descuento (ej: "+Discount 20%"), usa el precio DESPUÉS del descuento: unit_cost = precio_original × (1 - descuento/100).
- Los códigos pueden tener formato "11 3112", "22 1104", etc. Inclúyelos tal cual aparecen.
- quantity debe ser un entero.
- unit_cost debe ser un decimal con 2 decimales (usar punto como separador decimal, no coma).
- NO incluyas líneas de transporte, embalaje (tray, etiquetas), totales o resúmenes como items de producto.
- El supplier_name es la empresa que EMITE la factura (ej: "TIVAMO UG"), NO el cliente.
- En "summary" extrae TODOS los datos del resumen de la factura: subtotal de productos, descuento global, transporte (con detalle de cajas/bultos), otros costes, IVA (porcentaje y cantidad), y total final.
- Responde SOLO con el JSON, sin bloques de código markdown ni texto adicional.
PROMPT;

    private const EXPENSE_PROMPT = <<<'PROMPT'
Eres un sistema de extracción de datos de facturas de gastos/servicios (luz, agua, teléfono, internet, alquiler, etc.). Analiza el documento proporcionado y extrae los datos del gasto.

Devuelve EXCLUSIVAMENTE un JSON válido con esta estructura exacta, sin texto adicional:

{
  "concept": "descripción breve del gasto (ej: Factura electricidad marzo 2026)",
  "supplier_name": "nombre de la empresa que factura o null",
  "base_amount": 0.00,
  "tax_rate": 21,
  "date": "YYYY-MM-DD o null",
  "category_hint": "luz"
}

Reglas:
- base_amount es el importe SIN IVA (base imponible). Si solo aparece el total, calcula la base dividiendo entre (1 + tax_rate/100).
- tax_rate es el porcentaje de IVA (número entero: 21, 10, 4, 0). Electricidad y agua suelen tener 10%.
- category_hint debe ser una de: luz, agua, telefono, internet, alquiler, hosting, seguros, mantenimiento, otros.
- date en formato YYYY-MM-DD. Si hay fecha de emisión y de vencimiento, usa la de emisión.
- Responde SOLO con el JSON, sin bloques de código markdown ni texto adicional.
PROMPT;

    /**
     * Extrae datos de una factura de productos (proveedor).
     */
    public function extractInvoice(string $filePath, string $mimeType): array
    {
        $content = $this->buildContent(self::INVOICE_PROMPT, $filePath, $mimeType);

        $response = $this->callOpenAI($content, 'invoice');
        $data = $this->parseJson($response);

        Log::info('InvoiceVision: factura extraída', [
            'items' => count($data['items'] ?? []),
            'supplier' => $data['supplier_name'] ?? 'desconocido',
        ]);

        return $data;
    }

    /**
     * Extrae datos de una factura de gasto operativo (luz, agua, etc.).
     */
    public function extractExpense(string $filePath, string $mimeType): array
    {
        $content = $this->buildContent(self::EXPENSE_PROMPT, $filePath, $mimeType);

        $response = $this->callOpenAI($content, 'expense');
        $data = $this->parseJson($response);

        Log::info('InvoiceVision: gasto extraído', [
            'concept' => $data['concept'] ?? 'desconocido',
            'base_amount' => $data['base_amount'] ?? 0,
        ]);

        return $data;
    }

    /**
     * Construye el array de contenido para la API según el tipo de archivo.
     */
    private function buildContent(string $prompt, string $filePath, string $mimeType): array
    {
        $base64 = base64_encode(file_get_contents($filePath));

        if (str_contains($mimeType, 'pdf')) {
            // Enviar PDF directamente como file (OpenAI soporta PDFs nativamente)
            $dataUrl = "data:application/pdf;base64,{$base64}";
            return [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'file', 'file' => ['filename' => 'factura.pdf', 'file_data' => $dataUrl]],
            ];
        }

        // Imagen: enviar como base64
        $dataUrl = "data:{$mimeType};base64,{$base64}";

        return [
            ['type' => 'text', 'text' => $prompt],
            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl, 'detail' => 'high']],
        ];
    }

    /**
     * Llama a la API de OpenAI.
     */
    private function callOpenAI(array $content, string $type = 'invoice'): string
    {
        try {
            $model = env('OPENAI_MODEL', 'gpt-4o-mini');

            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'max_tokens' => 16384,
                'temperature' => 0.1,
            ]);

            // Registrar uso de tokens
            $usage = $response->usage;
            if ($usage) {
                AiUsageLog::create([
                    'type' => $type,
                    'model' => $model,
                    'tokens_input' => $usage->promptTokens,
                    'tokens_output' => $usage->completionTokens,
                    'cost_eur' => AiUsageLog::calculateCost($model, $usage->promptTokens, $usage->completionTokens),
                ]);
            }

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('InvoiceVision: error API OpenAI', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Error al conectar con el servicio de IA: ' . $e->getMessage());
        }
    }

    /**
     * Parsea la respuesta JSON de OpenAI, limpiando bloques markdown.
     */
    private function parseJson(string $raw): array
    {
        $cleaned = trim($raw);
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/m', '', $cleaned);
        $cleaned = trim($cleaned);

        $data = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('InvoiceVision: JSON inválido', ['raw' => substr($raw, 0, 500)]);
            throw new \RuntimeException('La IA devolvió una respuesta no válida. Intenta de nuevo con una imagen más clara.');
        }

        return $data;
    }
}
