@extends('layouts.app')

@section('title', 'Mission History')
@section('page-title', 'Mission History')

@section('content')
<div class="p-6 max-w-5xl mx-auto">

    {{-- STAT SUMMARY --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="cy-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(255,255,255,0.05);border:1px solid var(--border);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="cyroach-muted">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
            </div>
            <div>
                <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Total Misi</div>
                <div class="text-2xl font-display font-bold cyroach-text leading-none" id="stat-total-misi">—</div>
            </div>
        </div>
        <div class="cy-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.6">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Total Korban Terdeteksi</div>
                <div class="flex items-baseline gap-1.5">
                    <div class="text-2xl font-bold text-red-400 leading-none" id="stat-total-korban" style="font-variant-numeric: normal; font-feature-settings: normal;">—</div>
                </div>
            </div>
        </div>
        <div class="cy-card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="1.6">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Total Jam Beroperasi</div>
                <div class="text-2xl font-display font-bold cyroach-text leading-none" id="stat-hours">—</div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="cy-card p-3 mb-4 flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-2 flex-1 min-w-48" style="background-color: var(--bg-raised); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0 0.75rem;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="cyroach-muted shrink-0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="search-input" type="text" placeholder="Cari Mission ID (misal. #004)"
                class="text-xs py-2 flex-1 min-w-0 bg-transparent border-none outline-none cyroach-text placeholder:cyroach-muted font-mono">
        </div>
        <div class="flex items-center gap-1.5">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="cyroach-muted">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <input id="filter-date-start" type="date"
                class="text-xs py-1.5 px-2 rounded-lg border cyroach-border bg-transparent cyroach-text outline-none font-mono"
                style="background-color: var(--bg-raised);">
            <span class="text-xs cyroach-muted">sampai</span>
            <input id="filter-date-end" type="date"
                class="text-xs py-1.5 px-2 rounded-lg border cyroach-border bg-transparent cyroach-text outline-none font-mono"
                style="background-color: var(--bg-raised);">
        </div>
        <select id="filter-status"
            class="text-xs py-1.5 px-3 rounded-lg border cyroach-border cyroach-text outline-none font-mono"
            style="background-color: var(--bg-raised);">
            <option value="all">Semua Status</option>
            <option value="berlangsung">Berlangsung</option>
            <option value="selesai">Selesai</option>
        </select>
        <button onclick="filterAndRender()"
            class="text-xs px-4 py-1.5 rounded-lg font-mono font-semibold transition-all"
            style="background-color: var(--accent); color: white;">
            Filter
        </button>
    </div>

    {{-- TABLE --}}
    <div class="cy-card overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b cyroach-border" style="background-color: var(--bg-raised);">
                    <th class="text-left px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Mission ID</th>
                    <th class="text-left px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Waktu & Tanggal</th>
                    <th class="text-left px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Durasi</th>
                    <th class="text-center px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Korban</th>
                    <th class="text-left px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Suhu Maks</th>
                    <th class="text-left px-4 py-3 font-mono cyroach-muted uppercase tracking-widest font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody id="missions-list">
                <tr>
                    <td colspan="7" class="text-center py-10 cyroach-muted font-mono text-xs">Memuat data misi...</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 flex items-center justify-between" id="pagination-area" style="display:none!important">
        <div class="text-xs font-mono cyroach-muted" id="pagination-info"></div>
        <div class="flex items-center gap-1" id="pagination-buttons"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let allMissions = [];
const PER_PAGE = 5;
let currentPage = 1;
let filtered = [];

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

function pad(n) { return String(n).padStart(3, '0'); }

function renderStats(missions) {
    const total = missions.length;
    const korban = missions.reduce((s, m) => s + parseInt(m.detections_count ?? 0), 0);
    let totalSec = 0;
    missions.forEach(m => {
        if (m.started_at && m.ended_at) {
            totalSec += Math.floor((new Date(m.ended_at) - new Date(m.started_at)) / 1000);
        }
    });
    const hours = Math.floor(totalSec / 3600);

    const el = id => document.getElementById(id);
    if (el('stat-total-misi')) el('stat-total-misi').textContent = total || '—';
    if (el('stat-total-korban')) el('stat-total-korban').textContent = parseInt(korban);
    if (el('stat-hours')) el('stat-hours').textContent = hours.toLocaleString('id-ID');
}

function renderTable(missions) {
    const tbody = document.getElementById('missions-list');
    if (!tbody) return;

    if (missions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-10 font-mono text-xs cyroach-muted">
                    Belum ada misi tercatat
                </td>
            </tr>`;
        return;
    }

    const start = (currentPage - 1) * PER_PAGE;
    const pageData = missions.slice(start, start + PER_PAGE);

    tbody.innerHTML = pageData.map((m, i) => {
        const isSelesai = m.status === 'selesai';
        const korban = m.korban_count ?? 0;
        const panas = m.panas_count ?? 0;
        const suhuMax = m.max_temperature ? parseFloat(m.max_temperature).toFixed(1) + '°C' : '—';
        const rowBg = i % 2 === 0 ? '' : 'style="background-color: var(--bg-raised);"';
        return `
        <tr class="border-b cyroach-border hover:opacity-80 transition-opacity cursor-pointer" ${rowBg}
            onclick="window.location='/missions/${m.id}'">
            <td class="px-4 py-3">
                <span class="font-mono font-semibold cyroach-accent-text">#M${pad(m.mission_number)}</span>
            </td>
            <td class="px-4 py-3">
                <div class="font-mono cyroach-text">${formatTanggal(m.started_at)}</div>
                <div class="font-mono cyroach-muted" style="font-size:10px;">${formatJam(m.started_at)}</div>
            </td>
            <td class="px-4 py-3 font-mono cyroach-text">${formatDurasi(m.started_at, m.ended_at)}</td>
            <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold"
                    style="${korban > 0
                        ? 'background-color:var(--accent);color:#fff;'
                        : 'background-color:var(--bg-raised);border:1px solid var(--border);color:var(--text-muted);'}">
                    ${korban > 0 ? korban : (panas > 0 ? "!" : 0)}
                </span>
            </td>
            <td class="px-4 py-3 font-mono cyroach-text">${suhuMax}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-full font-medium"
                    style="${isSelesai
                        ? 'background-color:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.35);'
                        : 'background-color:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);'}">
                    <span class="w-1.5 h-1.5 rounded-full inline-block shrink-0" style="background-color:${isSelesai ? '#34d399' : '#fbbf24'};"></span>
                    ${isSelesai ? 'Selesai' : 'Berlangsung'}
                </span>
            </td>

            <td class="px-4 py-3 text-right">
                <span class="cyroach-muted group-hover:cyroach-accent-text font-mono">→</span>
            </td>
        </tr>`;
    }).join('');

    renderPagination(missions.length);
}

function renderPagination(total) {
    const area = document.getElementById('pagination-area');
    const info = document.getElementById('pagination-info');
    const btns = document.getElementById('pagination-buttons');
    if (!area) return;

    const totalPages = Math.ceil(total / PER_PAGE);
    const start = (currentPage - 1) * PER_PAGE + 1;
    const end = Math.min(currentPage * PER_PAGE, total);

    area.style.display = total > 0 ? 'flex' : 'none';
    if (info) info.textContent = `Menampilkan ${start}–${end} dari ${total} misi`;

    if (!btns) return;
    let html = '';

    // Prev
    html += `<button onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}
        class="w-7 h-7 rounded font-mono text-xs cyroach-muted border cyroach-border flex items-center justify-center
        ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : 'hover:cyroach-text cursor-pointer'}"
        style="background-color: var(--bg-raised);">‹</button>`;

    // Pages
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages > 7 && p > 3 && p < totalPages - 1 && Math.abs(p - currentPage) > 1) {
            if (p === 4) html += `<span class="w-7 h-7 flex items-center justify-center cyroach-muted text-xs">…</span>`;
            continue;
        }
        html += `<button onclick="goPage(${p})"
            class="w-7 h-7 rounded font-mono text-xs border flex items-center justify-center cursor-pointer transition-all
            ${p === currentPage
                ? 'border-red-700 text-red-400 font-bold'
                : 'cyroach-muted cyroach-border hover:cyroach-text'}"
            style="background-color: ${p === currentPage ? 'rgba(127,29,29,0.3)' : 'var(--bg-raised)'};">${p}</button>`;
    }

    // Next
    html += `<button onclick="goPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}
        class="w-7 h-7 rounded font-mono text-xs cyroach-muted border cyroach-border flex items-center justify-center
        ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:cyroach-text cursor-pointer'}"
        style="background-color: var(--bg-raised);">›</button>`;

    btns.innerHTML = html;
}

function goPage(p) {
    const totalPages = Math.ceil(filtered.length / PER_PAGE);
    if (p < 1 || p > totalPages) return;
    currentPage = p;
    renderTable(filtered);
}

function filterAndRender() {
    const status = document.getElementById('filter-status').value;
    const search = document.getElementById('search-input').value.toLowerCase().trim();
    const dateStart = document.getElementById('filter-date-start').value;
    const dateEnd = document.getElementById('filter-date-end').value;

    filtered = allMissions;
    if (status !== 'all') filtered = filtered.filter(m => m.status === status);
    if (search) filtered = filtered.filter(m =>
        `#m${pad(m.mission_number)}`.includes(search.replace('#','')) ||
        formatTanggal(m.started_at).toLowerCase().includes(search)
    );
    if (dateStart) filtered = filtered.filter(m => m.started_at && m.started_at >= dateStart);
    if (dateEnd)   filtered = filtered.filter(m => m.started_at && m.started_at <= dateEnd + 'T23:59:59');

    currentPage = 1;
    renderTable(filtered);
}

fetch('/api/missions')
    .then(r => r.json())
    .then(data => {
        allMissions = data;
        filtered = data;
        renderStats(data);
        renderTable(data);
    })
    .catch(() => {
        document.getElementById('missions-list').innerHTML =
            '<tr><td colspan="7" class="text-center py-10 text-red-400 font-mono text-xs">Gagal memuat data misi</td></tr>';
    });

document.getElementById('filter-status').addEventListener('change', filterAndRender);
document.getElementById('search-input').addEventListener('input', filterAndRender);
</script>
@endpush
















