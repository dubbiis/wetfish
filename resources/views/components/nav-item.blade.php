@props(['href', 'icon', 'label', 'active' => false])

<a href="{{ $href }}" class="flex flex-col items-center gap-1 group {{ $active ? 'text-primary' : 'text-white/40' }}">
    <span class="material-symbols-outlined {{ $active ? 'fill-1' : 'group-hover:text-primary transition-colors' }}">{{ $icon }}</span>
    <span class="text-[9px] font-bold uppercase tracking-widest {{ $active ? '' : 'group-hover:text-primary' }}">{{ $label }}</span>
</a>
