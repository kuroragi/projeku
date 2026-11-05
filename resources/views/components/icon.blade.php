@props([
    'name',
    'size' => 'sm',
    'class' => '',
])

@php
$sizeMap = [
    'xs' => 'size-3.5',
    'sm' => 'size-4',
    'md' => 'size-5',
    'lg' => 'size-8',
    12 => 'size-3',
    14 => 'size-3.5',
    16 => 'size-4',
    20 => 'size-5',
    24 => 'size-6',
    32 => 'size-8',
];
$cls = $sizeMap[$size] ?? (is_numeric($size) ? "size-[{$size}px]" : 'size-4');
@endphp

<svg 
    {{ $attributes->class([$cls, 'shrink-0 inline-block', $class]) }} 
    fill="none" 
    stroke="currentColor" 
    viewBox="0 0 24 24" 
    stroke-width="1.5"
    stroke-linecap="round" 
    stroke-linejoin="round" 
    aria-hidden="true"
>
    @switch($name)
        @case('pencil')
        @case('edit')
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            @break
        @case('more-vertical')
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="19" r="1" />
            @break
        @case('check')
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            @break
        @case('x')
        @case('close')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            @break
        @case('alert-circle')
            <circle cx="12" cy="12" r="10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
            @break
        @case('alert-triangle')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
            @break
        @case('arrow-up')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5M5 12l7-7 7 7" />
            @break
        @case('arrow-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M19 12l-7 7-7-7" />
            @break
        @case('plus')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            @break
        @case('trash')
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="8" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
            @break
        @case('chevron-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            @break
        @case('user')
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            @break
        @case('users')
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            @break
        @case('settings')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <circle cx="12" cy="12" r="3" />
            @break
        @case('logout')
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            @break
        @case('folder')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            @break
        @case('board')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            @break
        @case('document')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            @break
        @case('presentation')
        @case('play')
            <rect x="2" y="3" width="20" height="14" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8m-4-4v4" />
            @break
        @case('arrow-left')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            @break
        @case('arrow-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            @break
        @case('grip')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01" />
            @break
        @case('image')
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
            @break
        @case('list')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
            @break
        @case('type')
        @case('heading')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7V4h16v3M9 20h6M12 4v16" />
            @break
        @case('printer')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v4h12v-4" />
            @break
        @case('maximize')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3" />
            @break
        @case('minimize')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 14h6m0 0v6m0-6L3 21m17-7h-6m0 0v6m0-6l7 7M10 4v6m0 0H4m6 0L3 3m10 7h6m-6 0V4m0 6l7-7" />
            @break
        @default
            <circle cx="12" cy="12" r="10" />
    @endswitch
</svg>

