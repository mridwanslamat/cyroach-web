<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CYROACH') - CyRoach</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#7f1d1d">
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="/images/logo-cyroach.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="cyroach-body h-full flex overflow-hidden" id="app-body">

{{-- ===================== SIDEBAR ===================== --}}
<aside class="cyroach-sidebar w-52 shrink-0 flex flex-col h-full border-r overflow-y-auto" id="sidebar">

    {{-- Logo --}}
    <div class="px-5 pt-6 pb-4 border-b cyroach-border">
        <div class="flex items-center gap-2 mb-0.5">
            <span class="font-display text-lg font-bold tracking-tight cyroach-logo-text">CYROACH</span>
        </div>
        <div class="text-xs cyroach-muted" style="font-family:var(--font-mono);">Cyborg Cockroach</div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 flex flex-col gap-0.5">
        {{-- Live Dashboard: ikon radar/sinyal --}}
        <a href="{{ route('dashboard') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5.636 5.636a9 9 0 1 0 12.728 12.728"/>
                <path d="M2.343 2.343a16 16 0 0 0 0 22.314"/>
                <path d="M9.172 9.172a4 4 0 1 0 5.656 5.656"/>
                <circle cx="12" cy="12" r="1" fill="currentColor"/>
            </svg>
            Live Dashboard
        </a>
        {{-- Mission History: ikon clipboard/log --}}
        <a href="{{ route('missions.index') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('missions.*') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <path d="M9 12h6M9 16h4"/>
            </svg>
            Mission History
        </a>
        {{-- About: ikon info / shield --}}
        <a href="{{ route('about') }}"
           class="sidebar-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('about') ? 'sidebar-link-active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="10" x2="12" y2="14"/>
                <circle cx="12" cy="8" r="0.5" fill="currentColor"/>
            </svg>
            About Project
        </a>
    </nav>

    {{-- Mission Context (bawah sidebar) --}}
    <div class="px-4 pb-5 pt-3 border-t cyroach-border">
        <div class="text-xs cyroach-muted uppercase tracking-widest mb-3" style="font-family:var(--font-mono);font-size:10px;">Mission Context</div>
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.2);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="cyroach-accent-text">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold cyroach-text" id="viewer-count">— active</div>
                <div class="text-xs cyroach-muted" style="font-family:var(--font-mono);font-size:10px;">viewers online</div>
                <div class="w-full mt-2 rounded-full h-1" style="background-color:var(--bg-hover);">
                    <div id="viewer-bar" class="h-1 rounded-full transition-all" style="width:0%;background-color:rgba(220,38,38,0.6);"></div>
                </div>

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
        </div>
        <div class="flex items-center gap-2">
            <div class="text-xs flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg cyroach-badge-bg border cyroach-border" id="header-mission-info" style="display:none!important;font-family:var(--font-mono);">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span id="header-duration" class="cyroach-muted">—</span>
            </div>
            <button onclick="toggleTheme()" id="theme-toggle"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cyroach-border cyroach-btn transition-all text-xs font-medium"
                title="Toggle tema">
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <span id="theme-label" class="cyroach-muted">Light Mode</span>
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
    const body  = document.getElementById('app-body');
    const sun   = document.getElementById('icon-sun');
    const moon  = document.getElementById('icon-moon');
    const label = document.getElementById('theme-label');
    if (theme === 'light') {
        body.classList.add('light-mode');
        body.classList.remove('dark-mode');
        if (sun)   sun.style.display   = 'none';
        if (moon)  moon.style.display  = 'inline';
        if (label) label.textContent   = 'Dark Mode';
    } else {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        if (sun)   sun.style.display   = 'inline';
        if (moon)  moon.style.display  = 'none';
        if (label) label.textContent   = 'Light Mode';
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

<script>
(function(){
    // Pakai Pusher presence channel untuk hitung viewers real
    // Fallback: polling sederhana via fetch ke endpoint
    let viewerCount = 1;

    function updateViewerUI(count) {
        const el = document.getElementById('viewer-count');
        const bar = document.getElementById('viewer-bar');
        if (el) el.textContent = count + ' active';
        if (bar) {
            // bar max visual = 10 viewers
            const pct = Math.min(count / 10 * 100, 100);
            bar.style.width = pct + '%';
        }
    }

    // Cek apakah Echo/Pusher tersedia (dari app.js)
    function tryPresenceChannel() {
        if (typeof window.Echo === 'undefined') return false;
        try {
            window.Echo.join('viewers')
                .here((users) => {
                    viewerCount = users.length;
                    updateViewerUI(viewerCount);
                })
                .joining(() => {
                    viewerCount++;
                    updateViewerUI(viewerCount);
                })
                .leaving(() => {
                    viewerCount = Math.max(1, viewerCount - 1);
                    updateViewerUI(viewerCount);
                });
            return true;
        } catch(e) {
            return false;
        }
    }

    // Polling fallback — hit endpoint /api/viewers setiap 15 detik
    function startPolling() {
        async function poll() {
            try {
                const res = await fetch('/api/viewers');
                if (res.ok) {
                    const data = await res.json();
                    updateViewerUI(data.count ?? 1);
                }
            } catch(e) {}
        }
        poll();
        setInterval(poll, 15000);
    }

    // Tunggu app.js selesai load, lalu coba presence channel
    window.addEventListener('load', () => {
        const usedPresence = tryPresenceChannel();
        if (!usedPresence) startPolling();
    });

    // Tampil 1 dulu sementara loading
    updateViewerUI(1);
})();
</script>

</body>
</html>
















