<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyRoach</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</head>
<body class="bg-neutral-950 text-neutral-100 min-h-screen font-sans">

{{-- NAVBAR --}}
<nav class="bg-neutral-900 border-b border-red-900 px-4 h-14 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-2 min-w-0">
        <div class="w-8 h-8 shrink-0 bg-red-800 rounded-md flex items-center justify-center p-1.5">
            <div class="w-full h-full border-2 border-neutral-100 rounded-full flex items-center justify-center">
                <div class="w-2 h-2 bg-neutral-100 rounded-full"></div>
            </div>
        </div>
        <span class="text-sm font-semibold tracking-wide text-neutral-100 truncate hidden sm:block">CyRoach Monitoring Dashboard</span>
        <span class="text-sm font-semibold text-neutral-100 sm:hidden">CyRoach</span>
    </div>
    <div class="flex gap-1 shrink-0">
        <button id="theme-toggle" onclick="toggleTheme()"
            class="text-xs px-2 py-1.5 rounded-md border border-neutral-700 text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors"
            title="Toggle tema">
            <span id="theme-icon">
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </span>
        </button>
        <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1.5 rounded-md border border-red-700 bg-red-900 text-neutral-100 font-medium">Live</a>
        <a href="{{ route('missions.index') }}" class="text-xs px-3 py-1.5 rounded-md border border-transparent text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors">Misi</a>
    </div>
</nav>

{{-- BODY --}}
<div class="flex flex-col lg:grid lg:grid-cols-[1fr_220px] gap-4 p-4">

    {{-- KIRI / UTAMA --}}
    <div>
        {{-- TOP ROW --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-neutral-900 border border-neutral-800 border-l-2 border-l-red-700 rounded-lg p-3">
                    <div class="text-xs text-neutral-500 mb-1">Total kecoa</div>
                    <div class="text-2xl font-semibold" id="stat-total">—</div>
                </div>
                <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-3">
                    <div class="text-xs text-neutral-500 mb-1">Online</div>
                    <div class="text-2xl font-semibold text-emerald-400" id="stat-online">—</div>
                </div>
                <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-3">
                    <div class="text-xs text-neutral-500 mb-1">Korban terdeteksi</div>
                    <div class="text-2xl font-semibold text-red-400" id="stat-deteksi">—</div>
                </div>
                <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-3">
                    <div class="text-xs text-neutral-500 mb-1">Suhu tertinggi</div>
                    <div class="text-2xl font-semibold text-amber-400" id="stat-suhu">—</div>
                </div>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 border-l-2 border-l-red-700 rounded-lg p-4 flex flex-col justify-center">
                <div class="text-xs font-semibold text-neutral-100 mb-2">Cara deteksi korban</div>
                <div class="text-xs text-neutral-400 leading-relaxed">Cyborg kecoa dilengkapi kamera thermal 8×8 yang mendeteksi panas tubuh manusia. Ketika suhu terdeteksi melebihi ambang batas, sistem secara otomatis mencatat waktu dan data sensor sebagai indikasi keberadaan korban.</div>
            </div>
        </div>

        <div class="text-xs text-neutral-500 mb-3 pb-2 border-b border-neutral-800 flex items-center gap-2">
            <span>Kecoa aktif</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block" id="live-indicator"></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="cards-grid"></div>

        <div id="empty-state" class="hidden bg-neutral-900 border border-neutral-800 rounded-lg p-10 text-center">
            <div class="text-neutral-600 text-sm mb-1">Tidak ada kecoa terdaftar</div>
            <div class="text-neutral-700 text-xs">Menunggu data dari ESP32...</div>
        </div>

        <div class="lg:hidden mt-4">
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-3">
                <div class="text-xs font-semibold text-neutral-100 mb-3 pb-2 border-b border-neutral-800">Notifikasi</div>
                <div id="notif-container-mobile">
                    <div class="text-xs text-neutral-600 text-center py-4">Belum ada notifikasi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="hidden lg:block">
        <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-3 sticky top-18">
            <div class="text-xs font-semibold text-neutral-100 mb-3 pb-2 border-b border-neutral-800">Notifikasi</div>
            <div id="notif-container">
                <div class="text-xs text-neutral-600 text-center py-4">Belum ada notifikasi</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="fixed inset-0 bg-black/75 hidden items-center justify-center z-50 p-4" id="modal">
    <div class="bg-neutral-900 border border-red-900 rounded-xl w-full max-w-3xl overflow-hidden shadow-2xl">
        <div class="flex justify-between items-center px-4 py-3 border-b border-neutral-800 bg-neutral-950">
            <span class="text-sm font-semibold" id="modal-title">Detail Kecoa</span>
            <button onclick="closeModal()" class="text-neutral-400 hover:text-neutral-100 text-xl leading-none bg-transparent border-none cursor-pointer">×</button>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 gap-3">

                {{-- KOLOM KIRI: Heatmap + Trajectory --}}
                <div class="flex flex-col gap-3">

                    {{-- FIX MASALAH 5: heatmap modal lebih besar — 240x240 --}}
                    <div class="rounded-lg overflow-hidden border border-neutral-800" style="width:240px;height:240px;">
                        <canvas id="modal-canvas" width="240" height="240" style="display:block;width:240px;height:240px;"></canvas>
                    </div>

                    {{-- FIX MASALAH 5: trajectory square, tidak gepeng --}}
                    <div class="bg-neutral-950 border border-neutral-800 rounded-lg overflow-hidden flex flex-col">
                        <div class="text-xs text-neutral-500 px-3 pt-2 pb-1">Trajectory</div>
                        {{-- wrapper square: lebar mengikuti kolom, tinggi = lebar --}}
                        <div class="w-full border-t border-neutral-800" style="aspect-ratio:1/1; position:relative;">
                            <canvas id="modal-trajectory" style="position:absolute;top:0;left:0;width:100%;height:100%;display:block;"></canvas>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN --}}
                <div class="flex flex-col gap-2">
                    <div class="grid grid-cols-3 gap-1.5">
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Pitch</div>
                            <div class="text-sm font-semibold" id="modal-pitch">—</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Roll</div>
                            <div class="text-sm font-semibold" id="modal-roll">—</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Yaw</div>
                            <div class="text-sm font-semibold" id="modal-yaw">—</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2.5 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Suhu maks</div>
                            <div class="text-lg font-semibold text-red-400" id="modal-suhu-max">—</div>
                        </div>
                        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2.5 text-center">
                            <div class="text-xs text-neutral-500 mb-1">Suhu min</div>
                            <div class="text-lg font-semibold text-blue-400" id="modal-suhu-min">—</div>
                        </div>
                    </div>

                    {{-- Battery --}}
                    <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="6" width="18" height="12" rx="2"/><line x1="23" y1="13" x2="23" y2="11"/>
                                </svg>
                                Battery
                            </div>
                            <div class="text-sm font-semibold" id="modal-battery">—</div>
                        </div>
                        <div class="w-full bg-neutral-700 rounded-full h-1.5">
                            <div id="modal-battery-bar" class="h-1.5 rounded-full transition-all" style="width:0%"></div>
                        </div>
                    </div>

                    {{-- Signal --}}
                    <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="1" y1="6" x2="1" y2="18"/><line x1="6" y1="11" x2="6" y2="18"/>
                                    <line x1="11" y1="7" x2="11" y2="18"/><line x1="16" y1="3" x2="16" y2="18"/>
                                    <line x1="21" y1="1" x2="21" y2="18"/>
                                </svg>
                                Signal Strength
                            </div>
                            <div class="text-sm font-semibold" id="modal-signal">—</div>
                        </div>
                        <div class="w-full bg-neutral-700 rounded-full h-1.5">
                            <div id="modal-signal-bar" class="h-1.5 rounded-full transition-all" style="width:0%"></div>
                        </div>
                    </div>

                    {{-- Distance --}}
                    <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <polyline points="8 6 2 12 8 18"/><polyline points="16 6 22 12 16 18"/>
                            </svg>
                            Jarak Tempuh
                        </div>
                        <div class="text-sm font-semibold text-neutral-100" id="modal-distance">—</div>
                    </div>

                    <div class="mt-auto pt-2 border-t border-neutral-800 flex items-center justify-between">
                        <div class="text-xs text-neutral-500">Timestamp:<br><span id="modal-ts" class="text-neutral-400">—</span></div>
                        <div id="modal-status-badge" class="inline-block text-xs px-3 py-1 rounded-full"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function applyTheme(theme) {
    const sun  = document.getElementById('icon-sun');
    const moon = document.getElementById('icon-moon');
    if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
        if (sun)  sun.style.display  = 'none';
        if (moon) moon.style.display = 'inline';
    } else {
        document.documentElement.classList.remove('light-mode');
        if (sun)  sun.style.display  = 'inline';
        if (moon) moon.style.display = 'none';
    }
}
function toggleTheme() {
    const current = localStorage.getItem('theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', next);
    applyTheme(next);
}
applyTheme(localStorage.getItem('theme') || 'dark');
</script>

</body>
</html>