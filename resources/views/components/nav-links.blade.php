@php
    $baseLink = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition';
    $inactive = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';
    $active = 'bg-indigo-50 text-indigo-700';
@endphp

@auth
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('index') }}"
           class="{{ $baseLink }} {{ request()->routeIs('index') ? $active : $inactive }}">
            Diagnosis
        </a>
        <a href="{{ route('manage.devices') }}"
           class="{{ $baseLink }} {{ request()->routeIs('manage.devices') ? $active : $inactive }}">
            Devices
        </a>
        <a href="{{ route('manage.brands') }}"
           class="{{ $baseLink }} {{ request()->routeIs('manage.brands') ? $active : $inactive }}">
            Brands
        </a>
        <a href="{{ route('manage.models') }}"
           class="{{ $baseLink }} {{ request()->routeIs('manage.models') ? $active : $inactive }}">
            Models
        </a>
        <a href="{{ route('manage.problems') }}"
           class="{{ $baseLink }} {{ request()->routeIs('manage.problems') ? $active : $inactive }}">
            Problems
        </a>
        <a href="{{ route('manage.answers') }}"
           class="{{ $baseLink }} {{ request()->routeIs('manage.answers') ? $active : $inactive }}">
            Answers
        </a>
        <a href="{{ route('admin.legacy.diagnosis') }}"
           class="{{ $baseLink }} {{ request()->routeIs('admin.legacy.diagnosis') ? $active : $inactive }}">
            Legacy Builder
        </a>
    @else
        <a href="{{ route('homepage') }}"
           class="{{ $baseLink }} {{ request()->routeIs('homepage') ? $active : $inactive }}">
            Dashboard
        </a>
    @endif
@endauth

