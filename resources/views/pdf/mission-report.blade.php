<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 28px 40px;
        }

        /* ===== KOP SURAT ===== */
        .kop-wrap {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 10px;
            margin-bottom: 6px;
        }
        .kop-logo-cell {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            padding-right: 14px;
        }
        .kop-logo-cell img {
            width: 72px;
            height: 72px;
        }
        .kop-text-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .kop-ministry {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.4;
            color: #1a1a1a;
        }
        .kop-university {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1a1a1a;
            margin-top: 2px;
        }
        .kop-address-cell {
            display: table-cell;
            width: 210px;
            vertical-align: middle;
            text-align: right;
            font-size: 7.5px;
            color: #444;
            line-height: 1.55;
            padding-left: 10px;
            border-left: 1px solid #aaa;
        }
        .kop-sub-line {
            border-top: 1px solid #1a1a1a;
            margin-top: 4px;
            padding-top: 3px;
            font-size: 7.5px;
            color: #555;
            text-align: center;
        }

        /* ===== JUDUL DOKUMEN ===== */
        .doc-title-block {
            text-align: center;
            margin: 18px 0 14px;
        }
        .doc-title-block .doc-type {
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title-block .doc-nomor {
            font-size: 10px;
            margin-top: 2px;
        }
        .doc-title-block .doc-subject {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }

        /* ===== SECTION ===== */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #7f1d1d;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 10px;
            margin-top: 18px;
        }

        table { width: 100%; border-collapse: collapse; }

        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-table .label { width: 38%; color: #555; font-size: 10px; }
        .info-table .value { font-weight: bold; font-size: 10px; }

        .summary-table td {
            text-align: center;
            padding: 10px 5px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .sum-label { font-size: 8px; color: #6b7280; margin-bottom: 4px; }
        .sum-value { font-size: 16px; font-weight: bold; }

        .detection-wrap {
            border: 1px solid #e5e7eb;
            border-left: 3px solid #7f1d1d;
            border-radius: 3px;
            margin-bottom: 14px;
            padding: 12px;
        }
        .det-header td { padding-bottom: 8px; vertical-align: middle; }
        .det-title { font-weight: bold; font-size: 11px; }
        .det-time  { text-align: right; font-size: 9px; color: #6b7280; }

        .det-body td { vertical-align: top; }
        .det-heatmap { width: 42%; padding-right: 12px; }
        .det-heatmap img { width: 100%; max-width: 120px; display: block; border-radius: 3px; border: 1px solid #e5e7eb; }

        .imu-table td {
            text-align: center;
            padding: 5px 3px;
            border: 1px solid #e5e7eb;
            background: #f3f4f6;
        }
        .imu-label { font-size: 8px; color: #6b7280; }
        .imu-value { font-size: 10px; font-weight: bold; margin-top: 2px; }

        .alert-row td {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 3px;
            text-align: center;
            padding: 6px;
            color: #dc2626;
            font-weight: bold;
            font-size: 9px;
        }

        .badge {
            display: inline;
            padding: 2px 7px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef9c3; color: #a16207; }

        /* ===== SIGNATURE ===== */
        .signature-wrap {
            margin-top: 32px;
            display: table;
            width: 100%;
        }
        .sig-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 10px;
        }
        .sig-line {
            border-bottom: 1px solid #1a1a1a;
            width: 160px;
            margin: 48px auto 4px;
        }

        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            text-align: center;
            font-size: 7.5px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

{{-- ===== KOP SURAT ===== --}}
<div class="kop-wrap">
    {{-- Logo --}}
    <div class="kop-logo-cell">
        {{-- Placeholder lingkaran jika tidak ada file logo --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 72 72">
            <circle cx="36" cy="36" r="34" fill="none" stroke="#1a1a1a" stroke-width="2"/>
            <circle cx="36" cy="36" r="26" fill="none" stroke="#1a1a1a" stroke-width="1"/>
            <text x="36" y="30" text-anchor="middle" font-size="6" font-family="DejaVu Sans" font-weight="bold" fill="#1a1a1a">UNIVERSITAS</text>
            <text x="36" y="40" text-anchor="middle" font-size="6" font-family="DejaVu Sans" font-weight="bold" fill="#1a1a1a">DIPONEGORO</text>
            <text x="36" y="50" text-anchor="middle" font-size="5" font-family="DejaVu Sans" fill="#555">SEMARANG</text>
        </svg>
    </div>
    {{-- Teks kop --}}
    <div class="kop-text-cell">
        <div class="kop-ministry">
            KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI
        </div>
        <div class="kop-university">UNIVERSITAS DIPONEGORO</div>
        <div class="kop-sub-line">
            Departemen Teknik Elektro dan Teknologi Informasi &mdash; Fakultas Teknik
        </div>
    </div>
    {{-- Alamat kanan --}}
    <div class="kop-address-cell">
        Gedung Departemen Teknik Elektro<br>
        Jl. Prof. Sudharto, S.H., Tembalang<br>
        Semarang 50275<br>
        Telp. (024) 7460024<br>
        www.undip.ac.id
    </div>
</div>

{{-- ===== JUDUL DOKUMEN ===== --}}
@php
    $start = \Carbon\Carbon::parse($mission->started_at);
    $end   = $mission->ended_at ? \Carbon\Carbon::parse($mission->ended_at) : now();
    $diff  = $start->diff($end);
    $misiNo = str_pad($mission->mission_number, 3, '0', STR_PAD_LEFT);
@endphp
<div class="doc-title-block">
    <div class="doc-type">Berita Acara Operasi SAR</div>
    <div class="doc-nomor">No. BA/CYR/{{ $misiNo }}/{{ $start->format('Y') }}</div>
    <div class="doc-subject">
        Operasi Deteksi Korban Bencana Menggunakan Cyborg Kecoa
    </div>
</div>

{{-- ===== INFO MISI ===== --}}
<div class="section-title">Informasi Misi</div>
<table class="info-table">
    <tr><td class="label">Nomor Misi</td><td class="value">Misi #{{ $misiNo }}</td></tr>
    <tr><td class="label">Tanggal Operasi</td><td class="value">{{ $start->format('d F Y') }}</td></tr>
    <tr><td class="label">Waktu Mulai</td><td class="value">{{ $start->format('H:i') }} WIB</td></tr>
    <tr><td class="label">Waktu Selesai</td><td class="value">{{ $mission->ended_at ? $end->format('H:i').' WIB' : 'Masih berlangsung' }}</td></tr>
    <tr><td class="label">Durasi Operasi</td><td class="value">{{ $diff->h > 0 ? $diff->h.'j ' : '' }}{{ $diff->i }}m {{ $diff->s }}d</td></tr>
    <tr><td class="label">Status</td>
        <td class="value">
            <span class="badge {{ $mission->status === 'selesai' ? 'badge-green' : 'badge-amber' }}">
                {{ $mission->status === 'selesai' ? 'Selesai' : 'Berlangsung' }}
            </span>
        </td>
    </tr>
</table>

{{-- ===== RINGKASAN ===== --}}
<div class="section-title">Ringkasan Hasil Operasi</div>
<table class="summary-table">
    <tr>
        <td>
            <div class="sum-label">Total Deteksi Korban</div>
            <div class="sum-value" style="color:#dc2626;">{{ $mission->detections->count() }}</div>
        </td>
        <td>
            <div class="sum-label">Kecoa Terlibat</div>
            <div class="sum-value">{{ $mission->detections->pluck('device_id')->unique()->count() }}</div>
        </td>
        <td>
            <div class="sum-label">Ambang Batas Suhu</div>
            <div class="sum-value">37.5&deg;C</div>
        </td>
        <td>
            <div class="sum-label">Dicetak</div>
            <div style="font-size:10px; font-weight:bold; margin-top:4px;">{{ now()->format('d M Y') }}</div>
        </td>
    </tr>
</table>

{{-- ===== RIWAYAT DETEKSI ===== --}}
<div class="section-title">Riwayat Deteksi Korban</div>

@if($mission->detections->isEmpty())
    <p style="text-align:center; color:#6b7280; font-style:italic; padding:16px 0;">
        Tidak ada deteksi korban pada misi ini.
    </p>
@else
    @foreach($mission->detections as $i => $d)
    @php
        $devNum = ltrim(str_replace('kecoa_', '', $d->device_id), '0');
    @endphp
    <div class="detection-wrap">
        <table><tr class="det-header">
            <td class="det-title">Deteksi #{{ $i+1 }} &mdash; Kecoa #{{ $devNum }}</td>
            <td class="det-time">{{ \Carbon\Carbon::parse($d->detected_at)->format('d M Y, H:i:s') }} WIB</td>
        </tr></table>

        <table><tr class="det-body">
            <td class="det-heatmap">
                @if(isset($heatmaps[$d->id]))
                    <img src="{{ $heatmaps[$d->id] }}" alt="Thermal Heatmap">
                @else
                    <div style="height:80px; background:#f3f4f6; border:1px solid #e5e7eb; text-align:center; line-height:80px; color:#9ca3af; font-size:9px;">
                        Tidak ada data thermal
                    </div>
                @endif
            </td>
            <td style="vertical-align:top;">
                <table class="imu-table" style="margin-bottom:6px;">
                    <tr>
                        <td><div class="imu-label">Pitch</div><div class="imu-value">{{ number_format($d->pitch,1) }}&deg;</div></td>
                        <td><div class="imu-label">Roll</div><div class="imu-value">{{ number_format($d->roll,1) }}&deg;</div></td>
                        <td><div class="imu-label">Yaw</div><div class="imu-value">{{ number_format($d->yaw,1) }}&deg;</div></td>
                    </tr>
                </table>
                <table class="imu-table" style="margin-bottom:6px;">
                    <tr>
                        <td><div class="imu-label">Suhu Maks</div><div class="imu-value" style="color:#dc2626;">{{ number_format($d->suhu_max,1) }}&deg;C</div></td>
                        <td><div class="imu-label">Suhu Min</div><div class="imu-value" style="color:#2563eb;">{{ number_format($d->suhu_min,1) }}&deg;C</div></td>
                    </tr>
                </table>
                <table class="imu-table">
                    <tr>
                        <td><div class="imu-label">Gyro X</div><div class="imu-value">{{ number_format($d->gyro_x??0,1) }}</div></td>
                        <td><div class="imu-label">Gyro Y</div><div class="imu-value">{{ number_format($d->gyro_y??0,1) }}</div></td>
                        <td><div class="imu-label">Gyro Z</div><div class="imu-value">{{ number_format($d->gyro_z??0,1) }}</div></td>
                    </tr>
                </table>
            </td>
        </tr></table>

        <table style="margin-top:8px;"><tr class="alert-row">
            <td>&#9888; Terindikasi keberadaan korban hidup &mdash; suhu tubuh terdeteksi melebihi ambang batas 37.5&deg;C</td>
        </tr></table>
    </div>
    @endforeach
@endif

{{-- ===== TRAJECTORY ===== --}}
@if(!empty($trajectories))
<div class="section-title">Rekap Trajectory per Kecoa</div>
@foreach($trajectories as $deviceId => $imgBase64)
@php
    $devNum  = ltrim(str_replace('kecoa_', '', $deviceId), '0');
    $telem   = $telemetryPdf[$deviceId] ?? [];
    $avgSig  = isset($telem['avg_signal'])       ? number_format($telem['avg_signal'], 1).'%'     : '—';
    $distM   = isset($telem['distance_total_m']) ? number_format($telem['distance_total_m'], 2).' m' : '—';
@endphp
<div style="border:1px solid #e5e7eb; border-left:3px solid #7f1d1d; border-radius:3px; margin-bottom:12px; padding:10px;">
    <div style="font-weight:bold; font-size:10px; margin-bottom:6px;">Kecoa #{{ $devNum }}</div>
    <img src="{{ $imgBase64 }}" alt="Trajectory" style="width:100%; display:block; border-radius:3px; border:1px solid #e5e7eb;">
    <div style="font-size:8px; color:#6b7280; margin-top:4px;">
        Sumbu X = Roll &nbsp;|&nbsp; Sumbu Y = Pitch &nbsp;|&nbsp; &#9679; Hijau = START &nbsp;|&nbsp; &#9679; Merah = Posisi terakhir
    </div>
    <table style="width:100%; border-collapse:collapse; margin-top:8px;">
        <tr>
            <td style="width:50%; text-align:center; padding:6px; background:#f3f4f6; border:1px solid #e5e7eb;">
                <div style="font-size:8px; color:#6b7280; margin-bottom:2px;">Rata-rata Kekuatan Sinyal</div>
                <div style="font-size:11px; font-weight:bold;">{{ $avgSig }}</div>
            </td>
            <td style="width:50%; text-align:center; padding:6px; background:#f3f4f6; border:1px solid #e5e7eb;">
                <div style="font-size:8px; color:#6b7280; margin-bottom:2px;">Total Jarak Tempuh</div>
                <div style="font-size:11px; font-weight:bold;">{{ $distM }}</div>
            </td>
        </tr>
    </table>
</div>
@endforeach
@endif

{{-- ===== TANDA TANGAN ===== --}}
<div class="signature-wrap">
    <div class="sig-cell">
        <div>Semarang, {{ now()->format('d F Y') }}</div>
        <div style="margin-top:4px; font-size:9px; color:#6b7280;">Peneliti / Operator</div>
        <div class="sig-line"></div>
        <div style="font-size:9px;">Muhammad Afiq</div>
        <div style="font-size:8px; color:#6b7280;">Mahasiswa Teknik Elektro &mdash; Undip</div>
    </div>
    <div class="sig-cell">
        <div>Mengetahui,</div>
        <div style="margin-top:4px; font-size:9px; color:#6b7280;">Dosen Pembimbing</div>
        <div class="sig-line"></div>
        <div style="font-size:9px;">________________________</div>
        <div style="font-size:8px; color:#6b7280;">NIP.</div>
    </div>
</div>

{{-- ===== FOOTER ===== --}}
<div class="footer">
    Dokumen ini digenerate secara otomatis oleh sistem CyRoach Monitoring Dashboard &mdash;
    Universitas Diponegoro, Departemen Teknik Elektro dan Teknologi Informasi &mdash; TA 2025/2026<br>
    Dicetak pada {{ now()->format('d M Y, H:i') }} WIB
</div>

</body>
</html>