<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'WetFish' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-background-dark font-display text-slate-100 min-h-screen pb-24">

    <!-- Header -->
    <header class="sticky top-0 z-50 glass-nav border-b border-white/5 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="WetFish" class="size-8 rounded-lg">
            <h1 class="text-xl font-bold tracking-tight">{{ $header ?? 'WetFish' }}</h1>
        </div>
        <div class="flex items-center gap-3">
            @auth
            @if(auth()->user()->isAdmin())
            <a href="{{ route('stock') }}" class="relative p-2 hover:bg-white/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-slate-300">notifications</span>
                @php $alertCount = \App\Models\Product::whereColumn('stock', '<=', 'min_stock')->count(); @endphp
                @if($alertCount > 0)
                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600 text-[8px] font-bold items-center justify-center">{{ $alertCount }}</span>
                </span>
                @endif
            </a>
            @endif
            <div class="h-9 w-9 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-sm">person</span>
            </div>
            @endauth
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto p-4">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation -->
    @auth
    <nav class="fixed bottom-0 left-0 right-0 glass-nav border-t border-white/5 px-4 pt-3 pb-6 z-50">
        <div class="flex justify-between items-center max-w-lg mx-auto">
            @if(auth()->user()->isAdmin())
                <x-nav-item href="{{ route('dashboard') }}" icon="home" label="Home" :active="request()->routeIs('dashboard')" />
                <x-nav-item href="{{ route('stock') }}" icon="inventory_2" label="Stock" :active="request()->routeIs('stock*')" />
                <x-nav-item href="{{ route('pos') }}" icon="point_of_sale" label="Venta" :active="request()->routeIs('pos')" />
                <x-nav-item href="{{ route('expenses') }}" icon="receipt_long" label="Gastos" :active="request()->routeIs('expenses*')" />
                <x-nav-item href="{{ route('settings') }}" icon="settings" label="Config" :active="request()->routeIs('settings')" />
            @else
                <x-nav-item href="{{ route('pos') }}" icon="point_of_sale" label="Venta" :active="request()->routeIs('pos')" />
                <x-nav-item href="{{ route('invoices.import') }}" icon="description" label="Facturas" :active="request()->routeIs('invoices*')" />
            @endif
        </div>
    </nav>
    @endauth

    @livewireScripts
</body>
</html>
