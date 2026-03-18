<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WetFish - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background-dark font-display text-slate-100 min-h-screen flex items-center justify-center overflow-hidden radial-glow">
    <div class="relative w-full max-w-md px-6 py-12 flex flex-col items-center">
        <!-- Decorative glows -->
        <div class="absolute -bottom-24 -left-24 size-64 bg-primary/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 size-64 bg-primary/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Logo -->
        <div class="mb-10 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="WetFish" class="size-24 mx-auto mb-4 rounded-2xl">
            <h1 class="text-3xl font-bold tracking-tight text-slate-100">WetFish</h1>
            <p class="text-slate-400 mt-2 text-sm font-medium">Sistema de gestion acuariofilia</p>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
