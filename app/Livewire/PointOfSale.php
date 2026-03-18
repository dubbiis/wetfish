<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Punto de Venta')]
class PointOfSale extends Component
{
    public string $search = '';
    public string $categoryFilter = '';

    // Cart: array of ['product_id', 'name', 'price', 'quantity', 'subtotal']
    public array $cart = [];

    // Discount
    public string $discountType = 'none'; // none, percentage, fixed
    public string $discountValue = '0';

    // Notes
    public string $notes = '';

    // After sale
    public ?int $lastTicketId = null;
    public bool $showSuccess = false;

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $key = array_search($productId, array_column($this->cart, 'product_id'));

        if ($key !== false) {
            $this->cart[$key]['quantity']++;
            $this->cart[$key]['subtotal'] = round($this->cart[$key]['quantity'] * $this->cart[$key]['price'], 2);
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->sale_price,
                'quantity' => 1,
                'subtotal' => (float) $product->sale_price,
            ];
        }

        $this->search = '';
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);
            return;
        }
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = round($quantity * $this->cart[$index]['price'], 2);
    }

    public function incrementQty(int $index): void
    {
        $this->cart[$index]['quantity']++;
        $this->cart[$index]['subtotal'] = round($this->cart[$index]['quantity'] * $this->cart[$index]['price'], 2);
    }

    public function decrementQty(int $index): void
    {
        if ($this->cart[$index]['quantity'] <= 1) {
            $this->removeFromCart($index);
            return;
        }
        $this->cart[$index]['quantity']--;
        $this->cart[$index]['subtotal'] = round($this->cart[$index]['quantity'] * $this->cart[$index]['price'], 2);
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountType = 'none';
        $this->discountValue = '0';
        $this->notes = '';
    }

    public function getSubtotalProperty(): float
    {
        return round(array_sum(array_column($this->cart, 'subtotal')), 2);
    }

    public function getDiscountAmountProperty(): float
    {
        if ($this->discountType === 'percentage' && is_numeric($this->discountValue)) {
            return round($this->subtotal * $this->discountValue / 100, 2);
        }
        if ($this->discountType === 'fixed' && is_numeric($this->discountValue)) {
            return round(min((float) $this->discountValue, $this->subtotal), 2);
        }
        return 0;
    }

    public function getTaxRateProperty(): float
    {
        return (float) Setting::get('tax_rate', 21);
    }

    public function getTaxAmountProperty(): float
    {
        $afterDiscount = $this->subtotal - $this->discountAmount;
        return round($afterDiscount * $this->taxRate / 100, 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal - $this->discountAmount + $this->taxAmount, 2);
    }

    public function checkout(): void
    {
        if (empty($this->cart)) return;

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'subtotal' => $this->subtotal,
            'discount_type' => $this->discountType === 'none' ? null : $this->discountType,
            'discount_value' => $this->discountAmount,
            'tax_rate' => $this->taxRate,
            'tax_amount' => $this->taxAmount,
            'total' => $this->total,
            'notes' => $this->notes ?: null,
        ]);

        foreach ($this->cart as $item) {
            TicketItem::create([
                'ticket_id' => $ticket->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'discount_type' => null,
                'discount_value' => 0,
                'subtotal' => $item['subtotal'],
            ]);

            // Decrease stock
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }

        $this->lastTicketId = $ticket->id;
        $this->showSuccess = true;
        $this->clearCart();
    }

    public function newSale(): void
    {
        $this->showSuccess = false;
        $this->lastTicketId = null;
    }

    public function render()
    {
        $products = collect();
        if ($this->search || $this->categoryFilter) {
            $products = Product::where('stock', '>', 0)
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
                ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        return view('livewire.point-of-sale', [
            'products' => $products,
            'categories' => Category::all(),
        ]);
    }
}
