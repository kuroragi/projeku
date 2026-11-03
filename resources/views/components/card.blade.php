@props([
    'class' => '',
])

<div {{ $attributes->merge([
    'class' => "rounded-[8px] border border-border bg-surface-2 p-4 transition-all duration-150 hover:border-border-strong $class"
]) }}>
    {{ $slot }}
</div>

