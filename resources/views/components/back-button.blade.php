@props([
    'label' => __('Back'),
    'class' => 'btn btn-outline-secondary btn-sm',
    // Fallback when there is no previous URL (direct loads, first page, etc.)
    'fallback' => route('dashboard'),
])

@php
    $previous = url()->previous();
    $current = url()->current();
    $hasPrev = $previous && $previous !== $current;
@endphp

@if($hasPrev)
    <a href="{{ $previous }}" class="{{ $class }}" title="{{ __('Go back') }}"
       onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
        <i class="fas fa-arrow-left me-1"></i> {{ $label }}
    </a>
@else
    <a href="{{ $fallback }}" class="{{ $class }}" title="{{ __('Go back') }}">
        <i class="fas fa-arrow-left me-1"></i> {{ $label }}
    </a>
@endif

