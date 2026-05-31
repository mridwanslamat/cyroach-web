@extends('layouts.app')

@section('title', 'About Project')
@section('page-title', 'About Project')

@section('content')
<div class="overflow-y-auto h-full">

    {{-- HERO BANNER --}}
    <div class="relative mx-6 mt-6 rounded-xl overflow-hidden" style="height:220px;background:linear-gradient(135deg,#0f0f0f 0%,#1a0505 60%,#0f0f0f 100%);">
        <img src="/images/hero-cockroach.png"
            alt="CyRoach"
            style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:60% center;opacity:0.55;">
        <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 30px,#fff 30px,#fff 31px),repeating-linear-gradient(90deg,transparent,transparent 30px,#fff 30px,#fff 31px);"></div>
        <div class="absolute inset-0 flex flex-col justify-end p-7" style="background:linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);">
            <div class="text-xs px-2.5 py-1 rounded-full mb-3 inline-block w-fit font-medium" style="background-color:var(--accent);color:#fff;font-family:var(--font-mono);letter-spacing:0.08em;">
                MISSION CRITICAL RESEARCH
            </div>
            <h2 class="text-2xl font-display font-bold mb-1.5" style="color:#ffffff;text-shadow:0 2px 12px rgba(0,0,0,0.8);">CyRoach: Bio-Hybrid Robotics for Disaster Rescue</h2>
            <p class="text-sm" style="color:rgba(255,255,255,0.7);font-family:var(--font-mono);">Menciptakan jembatan antara organisme biologis dan kontrol digital untuk navigasi di medan bencana.</p>
        </div>
    </div>

    <div class="p-6 flex flex-col gap-5" style="max-width:1200px;">

        {{-- ROW 1: Kiri (Overview + Thermal + System Purpose) | Kanan (Engineering Profile) --}}
        <div class="grid gap-5 items-start grid-cols-1 lg:grid-cols-[1fr_320px]">

            {{-- KOLOM KIRI --}}
            <div class="flex flex-col gap-4 h-full">

                {{-- Project Overview --}}
                <div class="cy-card p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="cyroach-accent-text"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <span class="text-xs font-semibold cyroach-accent-text uppercase tracking-widest" style="font-family:var(--font-mono);">Project Overview</span>
                    </div>
                    <p class="text-sm cyroach-text leading-relaxed mb-3">
                        Proyek ini dikembangkan sebagai respons atas keterbatasan metode konvensional yang tidak mampu menjangkau celah sempit dan area tertutup pascabencana. Sistem Cyborg Kecoa Madagaskar dirancang sebagai solusi alternatif inovatif untuk mendukung operasi pencarian dan penyelamatan korban di area reruntuhan yang kompleks dan berbahaya bagi tim SAR.
                    </p>
                    <p class="text-sm cyroach-text leading-relaxed mb-3">
                        Sistem ini mengintegrasikan tiga komponen utama secara sinergis, <strong class="cyroach-text">elektronik miniatur berbasis mikrokontroler</strong> yang terpasang pada platform biologis kecoa, <strong class="cyroach-text">aplikasi mobile</strong> untuk pengendalian dan pemantauan real-time, serta <strong class="cyroach-text">sistem web monitoring</strong> untuk visualisasi data dan koordinasi tim penyelamat.
                    </p>
                    <p class="text-sm cyroach-text leading-relaxed">
                        Beroperasi secara <em>human-in-the-loop</em>, sistem ini memanfaatkan kemampuan alami kecoa Madagaskar yang tangguh dan hemat biaya untuk mempercepat pencarian korban dalam fase kritis <strong class="cyroach-text">72 jam pertama</strong> pascabencana, sekaligus meminimalkan risiko keselamatan petugas SAR di lapangan.
                    </p>
                </div>

                {{-- 3 Feature Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <div class="cy-card p-4">
                        <div class="w-9 h-9 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.25);">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8">
                                <circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                            </svg>
                        </div>
                        <div class="text-sm font-semibold cyroach-text mb-2">Bio-Hybrid Control</div>
                        <div class="text-xs cyroach-muted leading-relaxed">Memanfaatkan kecoa Madagaskar sebagai platform biologis yang tangguh dan mampu menjangkau ruang sempit yang tidak dapat dijangkau perangkat mekanik konvensional.</div>
                    </div>

                    <div class="cy-card p-4">
                        <div class="w-9 h-9 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.8">
                                <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <div class="text-sm font-semibold cyroach-text mb-2">Thermal Imaging</div>
                        <div class="text-xs cyroach-muted leading-relaxed">Deteksi keberadaan korban secara real-time melalui sensor panas tubuh dengan threshold <strong class="cyroach-text">37.5°C</strong>, membedakan manusia dari objek lain di bawah reruntuhan.</div>
                    </div>

                    <div class="cy-card p-4">
                        <div class="w-9 h-9 rounded-lg mb-3 flex items-center justify-center" style="background-color:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.25);">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8">
                                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                        </div>
                        <div class="text-sm font-semibold cyroach-text mb-2">Trajectory Analysis</div>
                        <div class="text-xs cyroach-muted leading-relaxed">Pemetaan pergerakan 1:1 untuk melacak area yang telah disisir, mendukung koordinasi tim SAR dalam operasi pencarian di medan yang kompleks.</div>
                    </div>

                </div>

            </div>


            {{-- KOLOM KANAN: Engineering Profile --}}
            <div class="cy-card p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="cyroach-accent-text"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    <span class="text-xs font-semibold cyroach-accent-text uppercase tracking-widest" style="font-family:var(--font-mono);">Engineering Profile</span>
                </div>
                <div class="flex flex-col">
                    <div class="text-xs cyroach-muted py-1.5 border-b cyroach-border" style="font-family:var(--font-mono);font-size:9px;letter-spacing:0.1em;">— WEB & BACKEND</div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Framework</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">Laravel + MySQL</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Styling</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">Tailwind CSS</span>
                    </div>
                    <div class="text-xs cyroach-muted py-1.5 border-b cyroach-border" style="font-family:var(--font-mono);font-size:9px;letter-spacing:0.1em;">— MOBILE</div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Platform</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">Android Studio</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Language</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">Java + XML</span>
                    </div>
                    <div class="text-xs cyroach-muted py-1.5 border-b cyroach-border" style="font-family:var(--font-mono);font-size:9px;letter-spacing:0.1em;">— HARDWARE</div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">MCU</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">ESP32-C6 Supermini</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Thermal</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">AMG8833 8×8</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Jarak</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">VL53L0X</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Gyro / IMU</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">MPU6050</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Mikrofon</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">INMP441</span>
                    </div>
                    <div class="text-xs cyroach-muted py-1.5 border-b cyroach-border" style="font-family:var(--font-mono);font-size:9px;letter-spacing:0.1em;">— SYSTEM INFO</div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Versi</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">CyRoach v1.0</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b cyroach-border">
                        <span class="text-xs cyroach-muted">Komunikasi</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">2.4GHz NRF24</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-xs cyroach-muted">Tahun</span>
                        <span class="text-xs font-semibold cyroach-text" style="font-family:var(--font-mono);">TA 2025/2026</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection