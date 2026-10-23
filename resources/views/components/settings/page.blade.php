@props(['title', 'subtitle' => null, 'back' => '/settings', 'backLabel' => null, 'wide' => false])
{{-- Layout of the settings pages: back link, title, optional actions, flash messages --}}
<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <div class="set-page {{ $wide ? 'set-page-wide' : '' }}">
            @if ($back)
                <a href="{{ $back }}" class="set-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    {{ $backLabel ?? __('Settings') }}
                </a>
            @endif
            <header class="set-head">
                <div>
                    <h1>{{ $title }}</h1>
                    @if ($subtitle)
                        <p>{{ $subtitle }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="set-head-actions">{{ $actions }}</div>
                @endisset
            </header>
            @if (session('success'))
                <div class="alert alert-success set-flash" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger set-flash" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger set-flash" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            {{ $slot }}
        </div>
    </main>
</x-app-layout>
