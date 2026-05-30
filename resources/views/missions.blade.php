<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Misi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#991b1b">
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');</script>
</head>
<body class="bg-neutral-950 text-neutral-100 min-h-screen font-sans">

{{-- NAVBAR (identik dengan dashboard) --}}
<nav class="bg-neutral-900 border-b border-red-900 px-4 h-14 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-2 min-w-0">
        <div class="w-8 h-8 shrink-0 bg-red-800 rounded-md flex items-center justify-center p-1.5">
            <div class="w-full h-full border-2 border-neutral-100 rounded-full flex items-center justify-center">
                <div class="w-2 h-2 bg-neutral-100 rounded-full"></div>
            </div>
        </div>
        <span class="text-sm font-semibold tracking-wide text-neutral-100 hidden sm:block">CyRoach Monitoring Dashboard</span>
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
        <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1.5 rounded-md border border-transparent text-neutral-400 hover:text-neutral-100 hover:bg-neutral-800 transition-colors">Live</a>
        <a href="{{ route('missions.index') }}" class="text-xs px-3 py-1.5 rounded-md border border-red-700 bg-red-900 text-neutral-100 font-medium">Misi</a>
    </div>
</nav>

{{-- BODY --}}
<div class="p-4 sm:p-6 max-w-4xl mx-auto">

    {{-- HEADER --}}
    <div class="mb-6 pb-4 border-b border-neutral-800">
        <h1 class="text-base font-semibold text-neutral-100">Riwayat Misi</h1>
        <p class="text-xs text-neutral-500 mt-1">Daftar semua sesi operasi yang pernah direkam secara otomatis</p>
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <select id="filter-status" class="text-xs bg-neutral-900 border border-neutral-800 text-neutral-300 rounded-md px-3 py-2 cursor-pointer focus:outline-none focus:border-red-700">
            <option value="all">Semua status</option>
            <option value="berlangsung">Berlangsung</option>
            <option value="selesai">Selesai</option>
        </select>
        <input id="search-input" type="text" placeholder="Cari misi..." class="text-xs bg-neutral-900 border border-neutral-800 text-neutral-300 rounded-md px-3 py-2 flex-1 min-w-0 placeholder-neutral-600 focus:outline-none focus:border-red-700">
    </div>

    {{-- LIST MISI --}}
    <div id="missions-list">
        <div class="text-xs text-neutral-500 text-center py-8">Memuat data misi...</div>
    </div>

</div>

<script>
// =====================
// THEME TOGGLE
// =====================
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

// =====================
// MISSIONS
// =====================
let allMissions = [];

function formatTanggal(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatJam(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatDurasi(start, end) {
    if (!start) return '—';
    const diff = Math.floor((new Date(end || Date.now()) - new Date(start)) / 1000);
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    return h > 0 ? `${h}j ${m}m` : `${m}m`;
}

function formatMissionNumber(num) {
    return String(num).padStart(3, '0');
}

function renderMissions(missions) {
    const container = document.getElementById('missions-list');
    if (!container) return;

    if (missions.length === 0) {
        container.innerHTML = `
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl p-10 text-center">
                <div class="text-neutral-600 text-sm mb-1">Belum ada misi tercatat</div>
                <div class="text-neutral-700 text-xs">Misi akan muncul otomatis saat kecoa mulai mengirim data</div>
            </div>`;
        return;
    }

    container.innerHTML = missions.map(m => `
        <a href="/missions/${m.id}" class="block bg-neutral-900 border border-neutral-800 rounded-xl px-4 py-3 mb-3 hover:border-red-800 transition-colors group">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-neutral-100 mb-1">
                        Misi #${formatMissionNumber(m.mission_number)}
                        <span class="text-neutral-500 font-normal">· ${formatTanggal(m.started_at)}</span>
                    </div>
                    <div class="text-xs text-neutral-500 flex flex-wrap gap-x-2">
                        <span>${formatJam(m.started_at)} – ${m.ended_at ? formatJam(m.ended_at) : 'sekarang'}</span>
                        <span>· Durasi: ${formatDurasi(m.started_at, m.ended_at)}</span>
                        <span class="${(m.detections_count ?? 0) > 0 ? 'text-red-400' : ''}">
                            · ${m.detections_count ?? 0} korban terdeteksi
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap ${m.status === 'berlangsung'
                        ? 'bg-amber-900 text-amber-400 border border-amber-700'
                        : 'bg-neutral-800 text-neutral-400 border border-neutral-700'}">
                        ${m.status === 'berlangsung' ? '● Berlangsung' : '✓ Selesai'}
                    </span>
                    <span class="text-neutral-700 group-hover:text-red-700 transition-colors">›</span>
                </div>
            </div>
        </a>
    `).join('');
}

function filterAndRender() {
    const status = document.getElementById('filter-status').value;
    const search = document.getElementById('search-input').value.toLowerCase();
    let filtered = allMissions;
    if (status !== 'all') filtered = filtered.filter(m => m.status === status);
    if (search) filtered = filtered.filter(m =>
        `misi #${formatMissionNumber(m.mission_number)}`.includes(search) ||
        formatTanggal(m.started_at).toLowerCase().includes(search)
    );
    renderMissions(filtered);
}

fetch('/api/missions')
    .then(r => r.json())
    .then(data => { allMissions = data; renderMissions(allMissions); })
    .catch(() => {
        document.getElementById('missions-list').innerHTML =
            '<div class="text-xs text-red-400 text-center py-8">Gagal memuat data misi</div>';
    });

document.getElementById('filter-status').addEventListener('change', filterAndRender);
document.getElementById('search-input').addEventListener('input', filterAndRender);
</script>

</body>
</html>