<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
#[Title('Configuración')]
class Settings extends Component
{
    // Business
    public string $business_name = '';
    public string $business_cif = '';
    public string $business_address = '';
    public string $business_phone = '';
    public string $business_email = '';

    // Tax & Margin
    public string $tax_rate = '21';
    public string $auto_margin_percentage = '30';

    // Margen y coste real
    public string $target_margin_percentage = '30';
    public string $expense_calculation_period = 'month';
    public string $price_adjustment_percentage = '0';
    public bool $price_adjustment_active = false;

    // Employee management
    public string $newEmployeeName = '';
    public string $newEmployeeEmail = '';
    public string $newEmployeePassword = '';

    public function mount(): void
    {
        $this->business_name = Setting::get('business_name', '');
        $this->business_cif = Setting::get('business_cif', '');
        $this->business_address = Setting::get('business_address', '');
        $this->business_phone = Setting::get('business_phone', '');
        $this->business_email = Setting::get('business_email', '');
        $this->tax_rate = Setting::get('tax_rate', '21');
        $this->auto_margin_percentage = Setting::get('auto_margin_percentage', '30');
        $this->target_margin_percentage = Setting::get('target_margin_percentage', '30');
        $this->expense_calculation_period = Setting::get('expense_calculation_period', 'month');
        $this->price_adjustment_percentage = Setting::get('price_adjustment_percentage', '0');
        $this->price_adjustment_active = Setting::get('price_adjustment_active', '0') === '1';
    }

    public function saveBusiness(): void
    {
        $this->validate([
            'business_name' => 'required|string|max:255',
            'business_cif' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:20',
            'business_email' => 'nullable|email|max:255',
        ]);

        Setting::set('business_name', $this->business_name);
        Setting::set('business_cif', $this->business_cif);
        Setting::set('business_address', $this->business_address);
        Setting::set('business_phone', $this->business_phone);
        Setting::set('business_email', $this->business_email);

        session()->flash('business_saved', true);
    }

    public function savePricing(): void
    {
        $this->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'auto_margin_percentage' => 'required|numeric|min:0|max:500',
        ]);

        Setting::set('tax_rate', $this->tax_rate);
        Setting::set('auto_margin_percentage', $this->auto_margin_percentage);

        session()->flash('pricing_saved', true);
    }

    public function createEmployee(): void
    {
        $this->validate([
            'newEmployeeName' => 'required|string|max:255',
            'newEmployeeEmail' => 'required|email|unique:users,email',
            'newEmployeePassword' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $this->newEmployeeName,
            'email' => $this->newEmployeeEmail,
            'password' => Hash::make($this->newEmployeePassword),
            'role' => 'employee',
        ]);

        $this->reset('newEmployeeName', 'newEmployeeEmail', 'newEmployeePassword');
        session()->flash('employee_created', true);
    }

    public function deleteEmployee(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->role === 'employee') {
            $user->delete();
        }
    }

    public function saveMarginSettings(): void
    {
        $this->validate([
            'target_margin_percentage' => 'required|numeric|min:0|max:500',
            'expense_calculation_period' => 'required|in:month,3months,6months',
        ]);

        Setting::set('target_margin_percentage', $this->target_margin_percentage);
        Setting::set('expense_calculation_period', $this->expense_calculation_period);

        Log::info('Margin settings saved', [
            'target' => $this->target_margin_percentage,
            'period' => $this->expense_calculation_period,
        ]);

        session()->flash('margin_saved', true);
    }

    public function applyPriceAdjustment(): void
    {
        $this->validate([
            'price_adjustment_percentage' => 'required|numeric|min:-50|max:200',
        ]);

        $pct = (float) $this->price_adjustment_percentage;

        DB::transaction(function () use ($pct) {
            // Guardar base_sale_price donde no exista (safety net)
            Product::whereNull('base_sale_price')
                ->update(['base_sale_price' => DB::raw('sale_price')]);

            // Recalcular todos los sale_price desde base_sale_price
            $factor = 1 + $pct / 100;
            Product::query()->update([
                'sale_price' => DB::raw("ROUND(base_sale_price * {$factor}, 2)"),
            ]);

            Setting::set('price_adjustment_active', '1');
            Setting::set('price_adjustment_percentage', (string) $pct);
        });

        $this->price_adjustment_active = true;

        Log::info('Price adjustment applied', ['percentage' => $pct]);
        session()->flash('pricing_saved', true);
    }

    public function revertPrices(): void
    {
        DB::transaction(function () {
            Product::whereNotNull('base_sale_price')
                ->update(['sale_price' => DB::raw('base_sale_price')]);

            Setting::set('price_adjustment_active', '0');
            Setting::set('price_adjustment_percentage', '0');
        });

        $this->price_adjustment_percentage = '0';
        $this->price_adjustment_active = false;

        Log::info('Prices reverted to base');
        session()->flash('prices_reverted', true);
    }

    public function getRealCostInfoProperty(): array
    {
        $period = $this->expense_calculation_period ?: 'month';
        $range = match ($period) {
            '3months' => [Carbon::now()->subMonths(3), Carbon::now()],
            '6months' => [Carbon::now()->subMonths(6), Carbon::now()],
            default   => [Carbon::now()->startOfMonth(), Carbon::now()],
        };

        $totalExpenses = Expense::whereBetween('date', $range)->sum('amount');
        $totalUnits = Product::where('stock', '>', 0)->sum('stock');
        $costPerUnit = $totalUnits > 0 ? round($totalExpenses / $totalUnits, 4) : 0;

        return compact('totalExpenses', 'totalUnits', 'costPerUnit');
    }

    public function getPreviewProductsProperty(): array
    {
        return Product::whereNotNull('base_sale_price')
            ->where('stock', '>', 0)
            ->limit(3)
            ->get()
            ->map(fn($p) => [
                'name' => $p->name,
                'current' => $p->sale_price,
                'base' => $p->base_sale_price,
                'adjusted' => round($p->base_sale_price * (1 + (float) $this->price_adjustment_percentage / 100), 2),
            ])
            ->toArray();
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings', [
            'employees' => User::where('role', 'employee')->get(),
        ]);
    }
}
