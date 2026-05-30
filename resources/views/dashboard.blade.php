@extends('layouts.app')

@section('title', 'Live Dashboard')
@section('page-title', 'Live Dashboard')
@section('live-badge', 'LIVE TRANSMISSION')

@section('content')
<div class="flex h-full">

    {{-- ===== KONTEN UTAMA ===== --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto p-6 gap-5">

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Total Kecoa</div>
                <div class="text-3xl font-display font-bold cyroach-text" id="stat-total">—</div>
                <div class="mt-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="cyroach-muted">
                        <path d="M12 2C8 2 4 5 4 9c0 5.25 8 13 8 13s8-7.75 8-13c0-4-4-7-8-7z"/>
                        <circle cx="12" cy="9" r="2.5"/>
                    </svg>
                </div>
            </div>
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Online</div>
                <div class="text-3xl font-display font-bold text-emerald-400" id="stat-online">—</div>
                <div class="mt-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-emerald-600">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>
                    </svg>
                </div>
            </div>
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Korban Terdeteksi</div>
                <div class="text-3xl font-display font-bold text-red-400" id="stat-deteksi">—</div>
                <div class="mt-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-red-700">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
            <div class="cy-card p-4">
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-2">Suhu Tertinggi</div>
                <div class="text-3xl font-display font-bold text-amber-400" id="stat-suhu">—</div>
                <div class="mt-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-amber-600">
                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- INFO BANNER --}}
        <div class="cy-card p-4 flex gap-4 items-start">
            <div class="w-8 h-8 rounded-lg cyroach-logo-bg flex items-center justify-center shrink-0 mt-0.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-semibold cyroach-text mb-1">Cara Deteksi Korban</div>
                <div class="text-xs cyroach-muted leading-relaxed">Cyborg kecoa dilengkapi kamera thermal 8×8 untuk mendeteksi panas tubuh manusia. Ketika suhu terdeteksi melebihi ambang batas, sistem secara otomatis mencatat waktu dan data sensor sebagai indikasi keberadaan korban.</div>
            </div>
        </div>

        {{-- SECTION HEADER --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold cyroach-text">Kecoa Aktif</span>
                <span class="text-xs font-mono cyroach-muted">(Thermal Feed)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-mono cyroach-muted">LIVE</span>
            </div>
        </div>

        {{-- CARDS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3" id="cards-grid"></div>

        {{-- EMPTY STATE --}}
        <div id="empty-state" class="hidden cy-card p-12 text-center">
            <div class="text-sm cyroach-muted mb-1">Tidak ada misi yang sedang berlangsung</div>
            <div class="text-xs cyroach-sub">Misi akan dimulai otomatis saat kecoa mulai mengirim data</div>
        </div>

    </div>

    {{-- ===== PANEL NOTIFIKASI KANAN ===== --}}
    <aside class="hidden lg:flex flex-col w-56 shrink-0 border-l cyroach-border h-full overflow-hidden">
        <div class="px-4 py-3 border-b cyroach-border">
            <div class="text-xs font-mono cyroach-muted uppercase tracking-widest">Notifikasi Terbaru</div>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-3" id="notif-container">
            <div class="text-xs cyroach-sub text-center py-6">Belum ada notifikasi</div>
        </div>
    </aside>

</div>

{{-- ===== MODAL DETAIL KECOA ===== --}}
<div class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4" id="modal">
    <div class="cy-card w-full max-w-2xl overflow-hidden shadow-2xl" style="border-color: var(--border-accent);">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b cyroach-border" style="background-color: var(--bg-raised);">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400" id="modal-status-dot"></span>
                <span class="text-sm font-semibold cyroach-text" id="modal-title">Detail Kecoa</span>
                <span class="text-xs font-mono px-2 py-0.5 rounded cyroach-live-badge" id="modal-status-badge">ONLINE</span>
            </div>
            <button onclick="closeModal()" class="cyroach-muted hover:cyroach-text text-xl leading-none w-7 h-7 flex items-center justify-center rounded hover:bg-neutral-700 transition-all">×</button>
        </div>

        {{-- Modal Body --}}
        <div class="p-5 grid grid-cols-2 gap-4">

            {{-- Kiri: Thermal + Trajectory --}}
            <div class="flex flex-col gap-3">
                <div>
                    <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-1.5">Kamera Thermal</div>
                    <div class="rounded-lg overflow-hidden border cyroach-border" style="width:220px;height:220px;">
                        <canvas id="modal-canvas" width="220" height="220" style="display:block;width:220px;height:220px;"></canvas>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-mono cyroach-muted uppercase tracking-widest mb-1.5">Trajectory Map</div>
                    <div class="rounded-lg border cyroach-border overflow-hidden" style="aspect-ratio:1/1;position:relative;width:220px;">
                        <canvas id="modal-trajectory" style="position:absolute;top:0;left:0;width:100%;height:100%;display:block;"></canvas>
                    </div>
                </div>
            </div>

            {{-- Kanan: Data --}}
            <div class="flex flex-col gap-2.5">

                {{-- Sensor --}}
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mb-0.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    Sensor
                </div>

                <div class="grid grid-cols-2 gap-1.5">
                    <div class="cy-card-raised p-2.5 text-center">
                        <div class="text-xs cyroach-muted mb-0.5">Suhu Maks</div>
                        <div class="text-lg font-bold text-red-400 font-display" id="modal-suhu-max">—</div>
                    </div>
                    <div class="cy-card-raised p-2.5 text-center">
                        <div class="text-xs cyroach-muted mb-0.5">Suhu Min</div>
                        <div class="text-lg font-bold text-blue-400 font-display" id="modal-suhu-min">—</div>
                    </div>
                </div>

                {{-- Orientasi --}}
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mt-1 mb-0.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4M12 8v8"/></svg>
                    Orientasi
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <div class="cy-card-raised p-2 text-center">
                        <div class="text-xs cyroach-muted mb-0.5">P</div>
                        <div class="text-sm font-semibold cyroach-text font-mono" id="modal-pitch">—</div>
                    </div>
                    <div class="cy-card-raised p-2 text-center">
                        <div class="text-xs cyroach-muted mb-0.5">R</div>
                        <div class="text-sm font-semibold cyroach-text font-mono" id="modal-roll">—</div>
                    </div>
                    <div class="cy-card-raised p-2 text-center">
                        <div class="text-xs cyroach-muted mb-0.5">Y</div>
                        <div class="text-sm font-semibold cyroach-text font-mono" id="modal-yaw">—</div>
                    </div>
                </div>

                {{-- Status Deteksi --}}
                <div class="text-xs font-mono cyroach-muted uppercase tracking-widest flex items-center gap-1.5 mt-1 mb-0.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Status Deteksi
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <div class="cy-card-raised p-2.5">
                        <div class="text-xs cyroach-muted mb-0.5">Timestamp</div>
                        <div class="text-xs font-mono cyroach-text" id="modal-ts">—</div>
                    </div>
                    <div class="cy-card-raised p-2.5">
                        <div class="text-xs cyroach-muted mb-0.5">Deteksi Korban</div>
                        <div class="text-xs font-mono cyroach-accent-text" id="modal-deteksi-status">—</div>
                    </div>
                </div>

                {{-- Battery & Signal --}}
                <div class="cy-card-raised p-2.5 mt-1">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-1.5 text-xs cyroach-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="18" height="12" rx="2"/><line x1="23" y1="13" x2="23" y2="11"/></svg>
                            Bat
                        </div>
                        <div class="text-xs font-mono font-semibold cyroach-text" id="modal-battery">—</div>
                    </div>
                    <div class="w-full rounded-full h-1" style="background-color: var(--bg-hover);">
                        <div id="modal-battery-bar" class="h-1 rounded-full bg-emerald-500 transition-all" style="width:0%"></div>
                    </div>
                </div>
                <div class="cy-card-raised p-2.5">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-1.5 text-xs cyroach-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="1" y1="6" x2="1" y2="18"/><line x1="6" y1="11" x2="6" y2="18"/><line x1="11" y1="7" x2="11" y2="18"/><line x1="16" y1="3" x2="16" y2="18"/><line x1="21" y1="1" x2="21" y2="18"/></svg>
                            Signal
                        </div>
                        <div class="text-xs font-mono font-semibold cyroach-text" id="modal-signal">—</div>
                    </div>
                    <div class="w-full rounded-full h-1" style="background-color: var(--bg-hover);">
                        <div id="modal-signal-bar" class="h-1 rounded-full bg-blue-500 transition-all" style="width:0%"></div>
                    </div>
                </div>

                {{-- Jarak --}}
                <div class="cy-card-raised p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs cyroach-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="2" y1="12" x2="22" y2="12"/><polyline points="8 6 2 12 8 18"/><polyline points="16 6 22 12 16 18"/></svg>
                        Jarak Tempuh
                    </div>
                    <div class="text-sm font-mono font-semibold cyroach-text" id="modal-distance">—</div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Mobile notif container alias
const notifMobile = null; // tidak ada panel mobile terpisah di layout baru
</script>
@endpush