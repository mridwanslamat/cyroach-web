@extends('layouts.app')

@section('title', 'Live Dashboard')
@section('page-title', 'Live Dashboard')

@section('content')
<div class="flex h-full">

    {{-- ===== KONTEN UTAMA ===== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto p-6 gap-5">

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="cy-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(255,255,255,0.05);border:1px solid var(--border);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="cyroach-muted">
                        <path d="M12 2c-1.5 0-2.5 1-2.5 2.5 0 .8.3 1.5.8 2C8 7 6 9.5 6 12v2c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2v-2c0-2.5-2-5-4.3-5.5.5-.5.8-1.2.8-2C14.5 3 13.5 2 12 2z"/>
                        <path d="M9 14v3M15 14v3"/>
                        <path d="M6 10l-3-2M18 10l3-2"/>
                        <path d="M6 13l-3 1M18 13l3 1"/>
                        <path d="M9 6.5l-2-2M15 6.5l2-2"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Total Kecoa</div>
                    <div class="text-2xl font-display font-bold cyroach-text leading-none" id="stat-total">—</div>
                </div>
            </div>
            <div class="cy-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.6">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Online</div>
                    <div class="text-2xl font-display font-bold text-emerald-400 leading-none" id="stat-online">—</div>
                </div>
            </div>
            <div class="cy-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.6">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Korban Terdeteksi</div>
                    <div class="text-2xl font-display font-bold text-red-400 leading-none" id="stat-deteksi">—</div>
                </div>
            </div>
            <div class="cy-card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background-color:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.6">
                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest mb-0.5" style="font-family:var(--font-mono);font-size:10px;">Suhu Tertinggi</div>
                    <div class="text-2xl font-display font-bold text-amber-400 leading-none" id="stat-suhu">—</div>
                </div>
            </div>
        </div>

        {{-- INFO BANNER --}}
        <div class="cy-card p-4 flex gap-4 items-start">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background-color:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.25);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                    <line x1="12" y1="2" x2="12" y2="5"/>
                    <line x1="12" y1="19" x2="12" y2="22"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold cyroach-text mb-1.5">Cara Deteksi Korban</div>
                <div class="text-sm cyroach-muted leading-relaxed" style="max-width:680px;">
                    Cyborg kecoa dilengkapi kamera thermal 8×8 untuk mendeteksi panas tubuh manusia.
                    Ketika suhu terdeteksi melebihi ambang batas <span class="text-red-400 font-medium">37.5°C</span>, sistem secara otomatis mencatat waktu dan data sensor.
                    Indikasi keberadaan korban ditentukan berdasarkan pola distribusi panas yang terdeteksi oleh grid sensor.
                </div>
            </div>
        </div>

        {{-- SECTION HEADER --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold cyroach-text">Kecoa Aktif</span>
                <span class="text-xs cyroach-muted" style="font-family:var(--font-mono);">(Thermal Feed)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs cyroach-muted" style="font-family:var(--font-mono);">LIVE</span>
            </div>
        </div>

        {{-- CARDS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" id="cards-grid"></div>

        {{-- EMPTY STATE --}}
        <div id="empty-state" class="hidden cy-card p-12 text-center">
            <div class="text-sm cyroach-muted mb-1">Tidak ada misi yang sedang berlangsung</div>
            <div class="text-xs cyroach-sub">Misi akan dimulai otomatis saat kecoa mulai mengirim data</div>
        </div>

    </div>

    {{-- ===== PANEL NOTIFIKASI KANAN ===== --}}
    <aside class="hidden lg:flex flex-col w-56 shrink-0 border-l cyroach-border h-full overflow-hidden">
        <div class="px-4 py-3 border-b cyroach-border">
            <div class="text-xs cyroach-muted uppercase tracking-widest" style="font-family:var(--font-mono);font-size:10px;">Notifikasi Terbaru</div>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-3" id="notif-container">
            <div class="text-xs cyroach-sub text-center py-6">Belum ada notifikasi</div>
        </div>
    </aside>

</div>

{{-- ===== MODAL DETAIL KECOA ===== --}}
<div class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4" id="modal">
    <div class="cy-card w-full overflow-hidden shadow-2xl" style="border-color:var(--border-accent);max-width:1400px;">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b cyroach-border" style="background-color:var(--bg-raised);">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400" id="modal-status-dot"></span>
                <span class="text-sm font-semibold cyroach-text font-display" id="modal-title">Detail Kecoa</span>
                <span class="text-xs px-2 py-0.5 rounded cyroach-live-badge" style="font-family:var(--font-mono);" id="modal-status-badge">ONLINE</span>
            </div>
            <button onclick="closeModal()" class="cyroach-muted text-xl leading-none w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-700 transition-all">×</button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 flex gap-5 items-start">

            {{-- KOLOM 1: Thermal 480x480 fixed --}}
            <div class="flex flex-col gap-2 shrink-0">
                <div class="text-xs cyroach-muted uppercase tracking-widest" style="font-family:var(--font-mono);font-size:10px;">Kamera Thermal</div>
                <div class="rounded-lg overflow-hidden border cyroach-border" style="width:480px;height:480px;">
                    <canvas id="modal-canvas" width="480" height="480" style="display:block;width:480px;height:480px;"></canvas>
                </div>
            </div>

            {{-- KOLOM 2: Sensor Data --}}
            <div class="flex flex-col gap-3 flex-1 min-w-0">

                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mb-2" style="font-family:var(--font-mono);font-size:10px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        Sensor
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="cy-card-raised p-3 text-center">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">SUHU MAKS</div>
                            <div class="text-xl font-bold text-red-400 font-display" id="modal-suhu-max">—</div>
                        </div>
                        <div class="cy-card-raised p-3 text-center">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">SUHU MIN</div>
                            <div class="text-xl font-bold text-blue-400 font-display" id="modal-suhu-min">—</div>
                        </div>
                        <div class="cy-card-raised p-3 text-center" style="grid-column:span 2;">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">JARAK</div>
                            <div class="text-base font-semibold cyroach-text font-display" id="modal-distance">—</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mb-2" style="font-family:var(--font-mono);font-size:10px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4M12 8v8"/></svg>
                        Orientasi
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="cy-card-raised p-3 text-center">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">PITCH</div>
                            <div class="text-sm font-semibold cyroach-text" style="font-family:var(--font-mono);" id="modal-pitch">—</div>
                        </div>
                        <div class="cy-card-raised p-3 text-center">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">ROLL</div>
                            <div class="text-sm font-semibold cyroach-text" style="font-family:var(--font-mono);" id="modal-roll">—</div>
                        </div>
                        <div class="cy-card-raised p-3 text-center">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">YAW</div>
                            <div class="text-sm font-semibold cyroach-text" style="font-family:var(--font-mono);" id="modal-yaw">—</div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="text-xs cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mb-2" style="font-family:var(--font-mono);font-size:10px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Status Deteksi
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="cy-card-raised p-3">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">TIMESTAMP</div>
                            <div class="text-sm cyroach-text" style="font-family:var(--font-mono);" id="modal-ts">—</div>
                        </div>
                        <div class="cy-card-raised p-3">
                            <div class="text-xs cyroach-muted mb-0.5" style="font-size:9px;">DETEKSI KORBAN</div>
                            <div class="text-sm" style="font-family:var(--font-mono);" id="modal-deteksi-status">—</div>
                        </div>
                    </div>
                </div>

                <div class="cy-card-raised p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5 text-xs cyroach-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="18" height="12" rx="2"/><line x1="23" y1="13" x2="23" y2="11"/></svg>
                            Battery
                        </div>
                        <div class="text-xs font-semibold" style="font-family:var(--font-mono);" id="modal-battery">—</div>
                    </div>
                    <div class="w-full rounded-full h-1.5" style="background-color:var(--bg-hover);">
                        <div id="modal-battery-bar" class="h-1.5 rounded-full transition-all" style="width:0%;background-color:#16a34a;"></div>
                    </div>
                </div>

                <div class="cy-card-raised p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5 text-xs cyroach-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                                <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                                <line x1="12" y1="20" x2="12.01" y2="20"/>
                            </svg>
                            Signal
                        </div>
                        <div class="text-xs font-semibold" style="font-family:var(--font-mono);" id="modal-signal">—</div>
                    </div>
                    <div class="w-full rounded-full h-1.5" style="background-color:var(--bg-hover);">
                        <div id="modal-signal-bar" class="h-1.5 rounded-full transition-all" style="width:0%;background-color:#2563eb;"></div>
                    </div>
                </div>

            </div>

            {{-- KOLOM 3: Trajectory 480x480 fixed --}}
            <div class="flex flex-col gap-2 shrink-0">
                <div class="text-xs cyroach-muted uppercase tracking-widest" style="font-family:var(--font-mono);font-size:10px;">Trajectory Map</div>
                <div class="rounded-lg border cyroach-border overflow-hidden" style="width:480px;height:480px;position:relative;">
                    <canvas id="modal-trajectory" width="480" height="480" style="position:absolute;top:0;left:0;width:480px;height:480px;display:block;"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const notifMobile = null;

function syncModalCanvasHeight() {
    const sensorCol = document.getElementById('modal-sensor-col');
    const thermalCanvas = document.getElementById('modal-canvas');
    const trajectoryWrap = document.getElementById('modal-trajectory-wrap');
    const trajectoryCanvas = document.getElementById('modal-trajectory');
    if (!sensorCol || !thermalCanvas || !trajectoryWrap) return;

    const h = sensorCol.offsetHeight;
    if (h < 100) return;

    const size = Math.min(h, 400); // max 400px, square

    const grid = document.getElementById('modal-body-grid');
    if (grid) grid.style.gridTemplateColumns = `${size}px 1fr ${size}px`;


    // Update thermal
    const thermalWrap = thermalCanvas.parentElement;
    thermalWrap.style.height = size + 'px';
    thermalWrap.style.width = size + 'px';
    thermalCanvas.width = size;
    thermalCanvas.height = size;
    thermalCanvas.style.width = size + 'px';
    thermalCanvas.style.height = size + 'px';

    // Update trajectory
    trajectoryWrap.style.height = size + 'px';
    trajectoryWrap.style.width = size + 'px';
    if (trajectoryCanvas) {
        trajectoryCanvas.width = size;
        trajectoryCanvas.height = size;
        trajectoryCanvas.style.width = size + 'px';
        trajectoryCanvas.style.height = size + 'px';
    }
}

// Override openModal untuk sync ukuran setelah modal muncul
const _origOpenModal = window.openModal;
window.openModal = function(deviceId) {
    _origOpenModal(deviceId);
    // Dua frame untuk pastikan DOM sudah render
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            syncModalCanvasHeight();
        });
    });
};
</script>
@endpush