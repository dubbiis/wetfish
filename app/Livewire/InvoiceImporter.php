<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Setting;
use App\Services\InvoiceVisionService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Importar Factura')]
class InvoiceImporter extends Component
{
    use WithFileUploads;

    // Step 1: Upload
    public $invoiceFile;
    public int $step = 1;
    public bool $processing = false;

    // Step 2: Invoice header
    public string $invoiceType = 'purchase';
    public ?int $supplier_id = null;
    public string $newSupplierName = '';
    public string $invoiceNumber = '';
    public string $invoiceDate = '';
    public string $concept = '';
    public string $extraCosts = '0';

    // Step 3: Parsed items
    public array $items = [];
    public array $invoiceSummary = [];

    // For matching
    public array $existingProducts = [];

    public function updatedInvoiceFile(): void
    {
        $this->validate(['invoiceFile' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240']);
        $this->parseWithVision();
    }

    private function parseWithVision(): void
    {
        $this->processing = true;

        try {
            $path = $this->invoiceFile->getRealPath();
            $mimeType = $this->invoiceFile->getMimeType();

            $service = app(InvoiceVisionService::class);
            $data = $service->extractInvoice($path, $mimeType);

            Log::info('InvoiceImporter: respuesta IA', ['data' => $data]);

            // Auto-fill header from AI response
            $supplierName = is_string($data['supplier_name'] ?? null) ? trim($data['supplier_name']) : '';
            $invoiceNum   = is_string($data['invoice_number'] ?? null) ? trim($data['invoice_number']) : '';
            $invoiceDate  = is_string($data['invoice_date'] ?? null) ? trim($data['invoice_date']) : '';

            $this->invoiceNumber = $invoiceNum;
            $this->invoiceDate   = $invoiceDate ?: now()->format('Y-m-d');

            if ($supplierName !== '') {
                $supplier = Supplier::where('name', 'like', '%' . $supplierName . '%')->first();
                if ($supplier) {
                    $this->supplier_id = $supplier->id;
                } else {
                    $this->newSupplierName = $supplierName;
                }
            }

            // Auto-generar concepto
            $this->concept = $supplierName
                ? 'Factura ' . $supplierName . ($invoiceNum ? ' #' . $invoiceNum : '')
                : '';

            // Auto-rellenar costes extra del summary (transporte, etc.)
            $summary = $data['summary'] ?? [];
            $transportCost = (float) ($summary['transport_cost'] ?? 0);
            $otherCosts = (float) ($summary['other_costs'] ?? 0);
            $this->extraCosts = (string) round($transportCost + $otherCosts, 2);
            $this->invoiceSummary = $summary;

            // Parse items
            $this->items = [];
            foreach ($data['items'] ?? [] as $aiItem) {
                $item = [
                    'code' => $aiItem['code'] ?? '',
                    'name' => $aiItem['name'] ?? '',
                    'quantity' => (int) ($aiItem['quantity'] ?? 1),
                    'unit_cost' => round((float) ($aiItem['unit_cost'] ?? 0), 2),
                    'total' => 0,
                    'matched_product_id' => null,
                    'is_new' => true,
                ];
                $item['total'] = round($item['quantity'] * $item['unit_cost'], 2);

                // Try to match existing product by code or name
                if (!empty($item['code'])) {
                    $match = Product::where('code', $item['code'])->first();
                }
                if (!isset($match) || !$match) {
                    $match = Product::where('name', 'like', '%' . $item['name'] . '%')->first();
                }

                if ($match) {
                    $item['matched_product_id'] = $match->id;
                    $item['is_new'] = false;
                }

                if (!empty($item['name'])) {
                    $this->items[] = $item;
                }

                $match = null;
            }

            if (empty($this->items)) {
                session()->flash('error', 'No se detectaron productos en el documento. Intenta con una imagen más clara.');
                $this->processing = false;
                return;
            }

            Log::info('InvoiceImporter: factura parseada con IA', ['items' => count($this->items)]);
            $this->step = 2;
        } catch (\Exception $e) {
            Log::error('InvoiceImporter: error al parsear', ['error' => $e->getMessage()]);
            session()->flash('error', $e->getMessage());
        } finally {
            $this->processing = false;
        }
    }

    public function goToStep3(): void
    {
        $this->validate([
            'invoiceType' => 'required|in:purchase,service',
            'invoiceDate' => 'required|date',
        ]);

        if ($this->invoiceType === 'service') {
            $this->importServiceInvoice();
            return;
        }

        $this->step = 3;
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function toggleNewProduct(int $index): void
    {
        $this->items[$index]['is_new'] = !$this->items[$index]['is_new'];
        if ($this->items[$index]['is_new']) {
            $this->items[$index]['matched_product_id'] = null;
        }
    }

    public function importInvoice(): void
    {
        // Resolve supplier
        $supplierId = $this->supplier_id;
        if (!$supplierId && $this->newSupplierName) {
            $supplier = Supplier::create(['name' => $this->newSupplierName]);
            $supplierId = $supplier->id;
        }

        $itemsTotal = array_sum(array_column($this->items, 'total'));
        $summary = $this->invoiceSummary;
        $transportCost = (float) ($summary['transport_cost'] ?? 0);
        $discountAmount = (float) ($summary['discount_amount'] ?? 0);
        $vatRate = (float) ($summary['vat_rate'] ?? 0);
        $vatAmount = (float) ($summary['vat_amount'] ?? 0);
        $invoiceTotal = (float) ($summary['total'] ?? 0);

        // Si la IA no devolvió total, calculamos
        if ($invoiceTotal <= 0) {
            $invoiceTotal = $itemsTotal + (float) $this->extraCosts;
        }

        $invoice = Invoice::create([
            'type' => $this->invoiceType,
            'supplier_id' => $supplierId,
            'invoice_number' => $this->invoiceNumber ?: null,
            'invoice_date' => $this->invoiceDate,
            'concept' => $this->concept ?: null,
            'total' => $invoiceTotal,
            'extra_costs' => (float) $this->extraCosts,
            'subtotal_products' => $itemsTotal,
            'transport_cost' => $transportCost,
            'discount_amount' => $discountAmount,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
        ]);

        $marginPct = (float) Setting::get('auto_margin_percentage', 30);

        foreach ($this->items as $item) {
            $productId = $item['matched_product_id'];

            $adjustmentActive = Setting::get('price_adjustment_active', '0') === '1';
            $adjPct = $adjustmentActive ? (float) Setting::get('price_adjustment_percentage', 0) : 0;

            if ($item['is_new'] && !$productId) {
                $baseSalePrice = round($item['unit_cost'] * (1 + $marginPct / 100), 2);
                $finalSalePrice = $adjustmentActive
                    ? round($baseSalePrice * (1 + $adjPct / 100), 2)
                    : $baseSalePrice;
                $product = Product::create([
                    'code' => $item['code'] ?: null,
                    'name' => $item['name'],
                    'cost_price' => $item['unit_cost'],
                    'sale_price' => $finalSalePrice,
                    'base_sale_price' => $baseSalePrice,
                    'stock' => $item['quantity'],
                    'min_stock' => 5,
                    'auto_margin' => true,
                ]);
                $productId = $product->id;
            } elseif ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $product->stock += $item['quantity'];
                    $product->cost_price = $item['unit_cost'];
                    if ($product->auto_margin) {
                        $baseSalePrice = round($item['unit_cost'] * (1 + $marginPct / 100), 2);
                        $product->base_sale_price = $baseSalePrice;
                        $product->sale_price = $adjustmentActive
                            ? round($baseSalePrice * (1 + $adjPct / 100), 2)
                            : $baseSalePrice;
                    }
                    $product->save();
                }
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $productId,
                'code' => $item['code'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'total' => $item['total'],
                'is_new_product' => $item['is_new'],
            ]);
        }

        session()->flash('message', 'Factura importada correctamente. ' . count($this->items) . ' productos procesados.');
        $this->reset();
        $this->step = 1;
    }

    private function importServiceInvoice(): void
    {
        $supplierId = $this->supplier_id;
        if (!$supplierId && $this->newSupplierName) {
            $supplier = Supplier::create(['name' => $this->newSupplierName]);
            $supplierId = $supplier->id;
        }

        $total = array_sum(array_column($this->items, 'total')) + (float) $this->extraCosts;

        Invoice::create([
            'type' => 'service',
            'supplier_id' => $supplierId,
            'invoice_number' => $this->invoiceNumber ?: null,
            'invoice_date' => $this->invoiceDate,
            'concept' => $this->concept ?: 'Factura de servicio',
            'total' => $total,
            'extra_costs' => (float) $this->extraCosts,
        ]);

        session()->flash('message', 'Factura de servicio registrada correctamente.');
        $this->reset();
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.invoice-importer', [
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
