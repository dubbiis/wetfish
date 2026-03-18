<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
