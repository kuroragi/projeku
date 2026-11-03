@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'error' => null,
    'help' => null,
    'required' => false,
])

@php
$id = $id ?? $name;
$hasError = !empty($error);
$inputBorderClass = $hasError 
    ? 'border-danger focus:border-danger focus:ring-danger/40' 
    : 'border-border focus:border-accent focus:ring-accent/40';
@endphp

<div class="flex flex-col gap-1.5 w-full">
    @if($label)
        <label for="{{ $id }}" class="text-xs font-medium text-text">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <input 
        type="{{ $type }}" 
        id="{{ $id }}" 
        name="{{ $name }}" 
        @if($required) required @endif
        {{ $attributes->merge([
            'class' => "h-9 w-full rounded-[8px] border bg-surface-3 px-3 text-sm text-text placeholder-text-faint transition focus:outline-none focus:ring-2 $inputBorderClass"
        ]) }}
    />

    @if($hasError)
        <div class="flex items-center gap-1.5 text-xs text-danger">
            <x-icon name="alert-circle" :size="14" class="shrink-0 stroke-[2]" />
            <span>{{ $error }}</span>
        </div>
    @elseif($help)
        <p class="text-[11px] text-text-faint">{{ $help }}</p>
    @endif
</div>

