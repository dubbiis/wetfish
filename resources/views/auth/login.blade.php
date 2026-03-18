<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="w-full space-y-5">
        @csrf

        <!-- Email -->
        <div class="space-y-2">
            <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Email</label>
            <input
                id="email" name="email" type="email" value="{{ old('email') }}"
                required autofocus autocomplete="username"
                class="w-full h-14 bg-black/40 border border-white/10 rounded-xl px-4 text-slate-100 placeholder:text-slate-600 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all"
                placeholder="admin@wetfish.es"
            >
            @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Contraseña</label>
            <input
                id="password" name="password" type="password"
                required autocomplete="current-password"
                class="w-full h-14 bg-black/40 border border-white/10 rounded-xl px-4 text-slate-100 placeholder:text-slate-600 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all"
                placeholder="••••••••"
            >
            @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember me -->
        <div class="flex items-center justify-between px-1">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="remember" class="rounded border-white/10 bg-black/40 text-primary focus:ring-primary/40 size-4">
                <span class="text-xs text-slate-400 group-hover:text-slate-300 transition-colors">Recordarme</span>
            </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full h-[52px] bg-primary hover:bg-primary/90 text-white font-bold rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98] mt-4">
            Iniciar sesión
        </button>
    </form>
</x-guest-layout>
