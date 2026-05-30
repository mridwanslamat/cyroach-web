<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CYROACH') — Cyborg Cockroach</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#7f1d1d">
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="cyroach-body h-full flex overflow-hidden" id="app-body">

{{-- ===================== SIDEBAR ===================== --}}
<aside class="cyroach-sidebar w-52 shrink-0 flex flex-col h-full border-r overflow-y-auto" id="sidebar">

    {{-- Logo --}}
    <div class="px-5 pt-6 pb-4 border-b cyroach-border">
        <div class="flex items-center gap-2 mb-0.5">
            <div class="w-6 h-6 rounded cyroach-logo-bg flex items-center justify-center shrink-0">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <circle cx="7" cy="7" r="5.5" stroke="#fff" stroke-width="1.5"/>
                    <circle cx="7" cy="7" r="2" fill="#fff"/>
                </svg>
            </div>
            <span class="font-display text-lg font-bold tracking-tight cyroach-logo-text">CYROACH</span>
        </div>
        <div class="text-xs cyroach-muted ml-8">Cyborg Cockroach</div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 flex flex-col gap-0.5">
        <a href="{{ route('dashboard') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="2"/><path d="M12 2a10 10 0 0 1 0 20 10 10 0 0 1 0-20"/>
                <path d="M12 6v2M12 16v2M6 12H4M20 12h-2"/>
            </svg>
            Live Dashboard
        </a>
        <a href="{{ route('missions.index') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('missions.*') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
            </svg>
            Mission History
        </a>
        <a href="{{ route('about') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('about') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            About
        </a>
    </nav>

    {{-- Mission Context (bawah sidebar) --}}
    <div class="px-4 pb-5 pt-3 border-t cyroach-border">
        <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-3">Mission Context</div>
        <div class="flex items-center gap-2 mb-2">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="cyroach-accent-text shrink-0">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <div>
                <div class="text-xs font-mono font-semibold cyroach-text uppercase tracking-wide">Viewer</div>
                <div class="text-xs cyroach-muted" id="viewer-count">— active</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 mt-0.5"></div>
            <div>
                <div class="text-xs font-mono cyroach-muted uppercase tracking-wide">System</div>
                <div class="text-xs cyroach-accent-text font-mono">Encrypted</div>
            </div>
        </div>
    </div>
</aside>

{{-- ===================== MAIN AREA ===================== --}}
<div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

    {{-- Header Bar --}}
    <header class="cyroach-header flex items-center justify-between px-6 h-14 shrink-0 border-b cyroach-border">
        <div class="flex items-center gap-3">
            <h1 class="font-display text-base font-bold cyroach-logo-text">@yield('page-title', 'Dashboard')</h1>
            @hasSection('live-badge')
                <span class="flex items-center gap-1.5 text-xs font-mono px-2.5 py-1 rounded-full cyroach-live-badge">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                    @yield('live-badge')
                </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <div class="text-xs font-mono cyroach-muted flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg cyroach-badge-bg border cyroach-border" id="header-mission-info" style="display:none!important">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span id="header-duration">—</span>
            </div>
            <button onclick="toggleTheme()" id="theme-toggle"
                class="w-8 h-8 rounded-lg border cyroach-border cyroach-btn flex items-center justify-center transition-all"
                title="Toggle tema">
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="flex-1 overflow-y-auto">
        @yield('content')
    </main>
</div>

{{-- ===================== THEME SCRIPT ===================== --}}
<script>
function applyTheme(theme) {
    const body = document.getElementById('app-body');
    const sun  = document.getElementById('icon-sun');
    const moon = document.getElementById('icon-moon');
    if (theme === 'light') {
        body.classList.add('light-mode');
        body.classList.remove('dark-mode');
        if (sun)  sun.style.display  = 'none';
        if (moon) moon.style.display = 'inline';
    } else {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        if (sun)  sun.style.display  = 'inline';
        if (moon) moon.style.display = 'none';
    }
}
function toggleTheme() {
    const current = localStorage.getItem('cyroach-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('cyroach-theme', next);
    applyTheme(next);
}
applyTheme(localStorage.getItem('cyroach-theme') || 'dark');
</script>

@stack('scripts')
</body>
</html>