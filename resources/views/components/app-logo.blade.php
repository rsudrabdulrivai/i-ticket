@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="iTicket" {{ $attributes }}>
        <x-slot name="logo" class="h-10">
            <!-- Ganti SVG dengan IMG -->
            <img src="{{ asset('logo.png') }}" class="h-10 rounded-md" alt="Logo">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="iTicket" {{ $attributes }}>
        <x-slot name="logo" class="h-10">
            <!-- Ganti SVG dengan IMG -->
            <img src="{{ asset('logo.png') }}" class="h-10 rounded-md" alt="Logo">
        </x-slot>
    </flux:brand>
@endif