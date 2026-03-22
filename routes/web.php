<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
        Route::get('/stock', \App\Livewire\StockList::class)->name('stock');
        Route::get('/stock/{productId}/edit', \App\Livewire\ProductEdit::class)->name('stock.edit');
        Route::get('/tickets', \App\Livewire\TicketHistory::class)->name('tickets');
        Route::get('/expenses', \App\Livewire\Expenses::class)->name('expenses');
        Route::get('/settings', \App\Livewire\Settings::class)->name('settings');
        Route::get('/employee/{employee}/tasks', \App\Livewire\EmployeeTasks::class)->name('employee.tasks');
        Route::get('/tickets/export', [TicketExportController::class, 'export'])->name('tickets.export');
        Route::get('/reports/coste-real', function () {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.informe-coste-real');
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download('WetFish-Informe-Coste-Real.pdf');
        })->name('reports.coste-real');
    });

    // Shared routes (admin + employee)
    Route::get('/pos', \App\Livewire\PointOfSale::class)->name('pos');
    Route::get('/my-tasks', \App\Livewire\MyTasks::class)->name('my-tasks');
    Route::get('/invoices/import', \App\Livewire\InvoiceImporter::class)->name('invoices.import');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
