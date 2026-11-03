@props([
    'variant' => 'primary',
    'size' => 'default',
    'type' => 'button',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 active:scale-[0.98] select-none cursor-pointer';

$sizeClasses = [
    'default' => 'h-9 px-3.5 text-sm rounded-[8px] gap-2',
    'sm' => 'h-8 px-2.5 text-xs rounded-[8px] gap-1.5',
    'icon' => 'h-8 w-8 rounded-[8px]',
][$size] ?? 'h-9 px-3.5 text-sm rounded-[8px] gap-2';

$variantClasses = [
    'primary' => 'bg-accent text-on-accent hover:bg-accent-hover focus-visible:ring-accent',
    'secondary' => 'border border-border bg-surface-3 text-text hover:border-border-strong hover:bg-surface-3/80 focus-visible:ring-accent',
    'ghost' => 'text-text-muted hover:bg-surface-3 hover:text-text focus-visible:ring-accent',
    'danger-soft' => 'bg-danger-soft text-danger hover:bg-danger-soft/80 hover:border-danger/40 focus-visible:ring-danger',
    'danger' => 'bg-danger text-white hover:bg-danger/90 focus-visible:ring-danger',
][$variant] ?? 'bg-accent text-on-accent hover:bg-accent-hover focus-visible:ring-accent';
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}
>
    {{ $slot }}
</button>

