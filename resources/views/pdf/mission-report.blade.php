<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #000000;
            background: #fff;
            padding: 30px 50px;
        }

        /* ===== KOP SURAT ===== */
        .kop-wrap {
            width: 100%;
            margin-bottom: 4px;
        }
        .kop-wrap img {
            width: 100%;
            display: block;
        }

        /* ===== JUDUL DOKUMEN ===== */
        .doc-title-block {
            text-align: center;
            margin: 20px 0 16px;
        }
        .doc-title-block .doc-type {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title-block .doc-subject {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 6px;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 10px;
            margin-top: 20px;
            color: #000;
            letter-spacing: 0.4px;
        }

        /* ===== INFO TABLE ===== */
        table { width: 100%; border-collapse: collapse; }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
            color: #000;
        }
        .info-table .label { width: 36%; font-size: 11px; }
        .info-table .sep   { width: 4%;  font-size: 11px; }
        .info-table .value { font-size: 11px; font-weight: bold; }

        /* ===== SUMMARY TABLE ===== */
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .summary-table td {
            text-align: center;
            padding: 10px 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        .sum-label { font-size: 9px; color: #000; margin-bottom: 4px; }
        .sum-value { font-size: 18px; font-weight: bold; color: #000; }

        /* ===== DETECTION BLOCK ===== */
        .detection-wrap {
            border: 1px solid #000;
            border-radius: 2px;
            margin-bottom: 16px;
            padding: 12px;
        }

        /* Detection header row */
        .det-header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .det-header-table td { vertical-align: middle; color: #000; }
        .det-title { font-weight: bold; font-size: 12px; }
        .det-time  { text-align: right; font-size: 10px; }

        /* Detection body: thermal left, sensor right */
        .det-body-table { width: 100%; border-collapse: collapse; }
        .det-heatmap-cell {
            width: 35%;
            padding-right: 14px;
            vertical-align: middle;
            text-align: center;
        }
        .det-heatmap-cell img {
            width: 130px;
            height: 130px;
            display: block;
            margin: 0 auto;
            border: 1px solid #000;
            border-radius: 2px;
            object-fit: cover;
        }
        .det-heatmap-label {
            font-size: 8px;
            color: #000;
            text-align: center;
            margin-top: 4px;
        }
        .det-sensor-cell { width: 65%; vertical-align: top; }

        /* Sensor tables */
        .sensor-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .sensor-table td {
            text-align: center;
            padding: 5px 4px;
            border: 1px solid #999;
            background: #f5f5f5;
            color: #000;
        }
        .sensor-label { font-size: 8px; color: #000; margin-bottom: 2px; }
        .sensor-value { font-size: 11px; font-weight: bold; color: #000; }

        /* Alert */
        .alert-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .alert-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
            font-weight: bold;
            font-size: 10px;
            color: #000;
            background: #f0f0f0;
        }

        /* ===== TRAJECTORY ===== */
        .traj-wrap {
            border: 1px solid #000;
            border-radius: 2px;
            margin-bottom: 14px;
            padding: 10px;
        }
        .traj-title { font-weight: bold; font-size: 11px; margin-bottom: 8px; color: #000; }
        .traj-img   { width: 100%; display: block; border: 1px solid #000; border-radius: 2px; }
        .traj-legend { font-size: 8px; color: #000; margin-top: 4px; }
        .traj-stats { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .traj-stats td {
            width: 50%;
            text-align: center;
            padding: 6px;
            border: 1px solid #999;
            background: #f5f5f5;
            color: #000;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 28px;
            border-top: 1px solid #000;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #000;
        }
    </style>
</head>
<body>

{{-- ===== KOP SURAT ===== --}}
<div class="kop-wrap">
    <img src="{{ public_path('images/kopsuratft.png') }}" alt="Kop Surat Fakultas Teknik Undip">
</div>

{{-- ===== JUDUL DOKUMEN ===== --}}
@php
    $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $fmtTgl  = function($dt) use ($bulanId) {
        return $dt->day.' '.$bulanId[$dt->month].' '.$dt->year;
    };
    $fmtWkt  = function($dt) use ($bulanId) {
        return $dt->format('d').' '.$bulanId[$dt->month].' '.$dt->format('Y, H:i:s');
    };
    $start  = \Carbon\Carbon::parse($mission->started_at);
    $end    = $mission->ended_at ? \Carbon\Carbon::parse($mission->ended_at) : now();
    $diff   = $start->diff($end);
    $misiNo = str_pad($mission->mission_number, 3, '0', STR_PAD_LEFT);
@endphp
<div class="doc-title-block">
    <div class="doc-type">Berita Acara Operasi SAR</div>
    <div class="doc-subject">Operasi Deteksi Korban Bencana Menggunakan Cyborg Kecoa</div>
</div>

{{-- ===== INFORMASI MISI ===== --}}
<div class="section-title">I. Informasi Misi</div>
<table class="info-table">
    <tr>
        <td class="label">Nomor Misi</td>
        <td class="sep">:</td>
        <td class="value">Misi #{{ $misiNo }}</td>
    </tr>
    <tr>
        <td class="label">Tanggal Operasi</td>
        <td class="sep">:</td>
        <td class="value">{{ $fmtTgl($start) }}</td>
    </tr>
    <tr>
        <td class="label">Waktu Mulai</td>
        <td class="sep">:</td>
        <td class="value">{{ $start->format('H:i') }} WIB</td>
    </tr>
    <tr>
        <td class="label">Waktu Selesai</td>
        <td class="sep">:</td>
        <td class="value">{{ $mission->ended_at ? $end->format('H:i').' WIB' : 'Masih berlangsung' }}</td>
    </tr>
    <tr>
        <td class="label">Durasi Operasi</td>
        <td class="sep">:</td>
        <td class="value">{{ $diff->h > 0 ? $diff->h.' jam ' : '' }}{{ $diff->i }} menit {{ $diff->s }} detik</td>
    </tr>
    <tr>
        <td class="label">Status</td>
        <td class="sep">:</td>
        <td class="value">{{ $mission->status === 'selesai' ? 'Selesai' : 'Masih Berlangsung' }}</td>
    </tr>
    <tr>
        <td class="label">Ambang Batas Suhu</td>
        <td class="sep">:</td>
        <td class="value">37,5&deg;C</td>
    </tr>
</table>

{{-- ===== RINGKASAN HASIL OPERASI ===== --}}
<div class="section-title">II. Ringkasan Hasil Operasi</div>
<table class="summary-table">
    <tr>
        <td style="width:33%;">
            <div class="sum-label">Total Deteksi Korban</div>
            <div class="sum-value">{{ $mission->detections->count() }}</div>
        </td>
        <td style="width:33%;">
            <div class="sum-label">Kecoa Terlibat</div>
            <div class="sum-value">{{ $mission->detections->pluck('device_id')->unique()->count() }}</div>
        </td>
        <td style="width:34%;">
            <div class="sum-label">Tanggal Cetak</div>
            <div style="font-size:11px; font-weight:bold; margin-top:4px;">{{ $fmtTgl(now()) }}</div>
        </td>
    </tr>
</table>

{{-- ===== RIWAYAT DETEKSI KORBAN ===== --}}
<div class="section-title">III. Riwayat Deteksi Korban</div>

@if($mission->detections->isEmpty())
    <p style="color:#000; font-style:italic; padding:12px 0; text-align:center;">
        Tidak ada deteksi korban yang tercatat pada misi ini.
    </p>
@else
    @foreach($mission->detections as $i => $d)
    @php
        $devNum  = ltrim(str_replace('kecoa_', '', $d->device_id), '0');
        $detAt   = \Carbon\Carbon::parse($d->detected_at);
    @endphp
    <div class="detection-wrap">

        {{-- Header --}}
        <table class="det-header-table">
            <tr>
                <td class="det-title">Deteksi #{{ $i + 1 }} &mdash; Kecoa #{{ $devNum }}</td>
                <td class="det-time">{{ $fmtWkt($detAt) }} WIB</td>
            </tr>
        </table>

        {{-- Body: thermal kiri, sensor kanan --}}
        <table class="det-body-table">
            <tr>
                <td class="det-heatmap-cell">
                    @if(isset($heatmaps[$d->id]))
                        <img src="{{ $heatmaps[$d->id] }}" alt="Thermal Heatmap">
                        <div class="det-heatmap-label">Thermal Heatmap AMG8833 8&times;8</div>
                    @else
                        <div style="width:130px;height:130px;border:1px solid #000;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:9px;color:#555;">
                            Tidak ada data thermal
                        </div>
                        <div class="det-heatmap-label">Thermal Heatmap AMG8833 8&times;8</div>
                    @endif
                </td>
                <td class="det-sensor-cell">

                    {{-- IMU --}}
                    <table class="sensor-table">
                        <tr>
                            <td>
                                <div class="sensor-label">Pitch</div>
                                <div class="sensor-value">{{ number_format($d->pitch, 1) }}&deg;</div>
                            </td>
                            <td>
                                <div class="sensor-label">Roll</div>
                                <div class="sensor-value">{{ number_format($d->roll, 1) }}&deg;</div>
                            </td>
                            <td>
                                <div class="sensor-label">Yaw</div>
                                <div class="sensor-value">{{ number_format($d->yaw, 1) }}&deg;</div>
                            </td>
                        </tr>
                    </table>

                    {{-- Suhu --}}
                    <table class="sensor-table">
                        <tr>
                            <td>
                                <div class="sensor-label">Suhu Maksimum</div>
                                <div class="sensor-value">{{ number_format($d->suhu_max, 1) }}&deg;C</div>
                            </td>
                            <td>
                                <div class="sensor-label">Suhu Minimum</div>
                                <div class="sensor-value">{{ number_format($d->suhu_min, 1) }}&deg;C</div>
                            </td>
                        </tr>
                    </table>

                    {{-- Gyro --}}
                    <table class="sensor-table">
                        <tr>
                            <td>
                                <div class="sensor-label">Gyro X</div>
                                <div class="sensor-value">{{ number_format($d->gyro_x ?? 0, 1) }}</div>
                            </td>
                            <td>
                                <div class="sensor-label">Gyro Y</div>
                                <div class="sensor-value">{{ number_format($d->gyro_y ?? 0, 1) }}</div>
                            </td>
                            <td>
                                <div class="sensor-label">Gyro Z</div>
                                <div class="sensor-value">{{ number_format($d->gyro_z ?? 0, 1) }}</div>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

        {{-- Alert: ganti &#9888; dengan teks [!] agar aman di semua PDF engine --}}
        <table class="alert-table">
            <tr>
                <td>
                    [!] Terindikasi keberadaan korban hidup &mdash;
                    suhu tubuh terdeteksi melebihi ambang batas 37,5&deg;C
                </td>
            </tr>
        </table>

    </div>
    @endforeach
@endif

{{-- ===== REKAP TRAJECTORY ===== --}}
@if(!empty($trajectories))
<div class="section-title">IV. Rekap Trajectory Pergerakan Kecoa</div>
<p style="font-size:10px; color:#000; margin-bottom:12px; line-height:1.6;">
    Berikut adalah peta pergerakan kecoa selama operasi berlangsung berdasarkan data IMU (Inertial Measurement Unit).
    Sumbu X merepresentasikan nilai Roll dan sumbu Y merepresentasikan nilai Pitch.
</p>

@foreach($trajectories as $deviceId => $imgBase64)
@php
    $devNum = ltrim(str_replace('kecoa_', '', $deviceId), '0');
    $telem  = $telemetryPdf[$deviceId] ?? [];
    $avgSig = isset($telem['avg_signal'])       ? number_format($telem['avg_signal'], 1).'%'        : '-';
    $distM  = isset($telem['distance_total_m']) ? number_format($telem['distance_total_m'], 2).' m'  : '-';
@endphp
<div class="traj-wrap">
    <div class="traj-title">Kecoa #{{ $devNum }}</div>
    <img src="{{ $imgBase64 }}" alt="Trajectory Kecoa #{{ $devNum }}" class="traj-img">
    {{-- Ganti bullet unicode dengan teks biasa agar tidak jadi "?" --}}
    <div class="traj-legend">
        Sumbu X = Roll &nbsp;|&nbsp; Sumbu Y = Pitch &nbsp;|&nbsp;
        [H] = Posisi Awal (START) &nbsp;|&nbsp; [M] = Posisi Terakhir
    </div>
    <table class="traj-stats">
        <tr>
            <td>
                <div style="font-size:9px; color:#000; margin-bottom:2px;">Rata-rata Kekuatan Sinyal</div>
                <div style="font-size:12px; font-weight:bold;">{{ $avgSig }}</div>
            </td>
            <td>
                <div style="font-size:9px; color:#000; margin-bottom:2px;">Total Jarak Tempuh</div>
                <div style="font-size:12px; font-weight:bold;">{{ $distM }}</div>
            </td>
        </tr>
    </table>
</div>
@endforeach
@endif

{{-- ===== FOOTER ===== --}}
<div class="footer">
    Dokumen ini digenerate secara otomatis oleh sistem CyRoach Monitoring Dashboard &mdash;
    Universitas Diponegoro, Departemen Teknik Elektro &mdash; TA 2025/2026<br>
    Dicetak pada {{ $fmtTgl(now()) }}, {{ now()->format('H:i') }} WIB
</div>

</body>
</html>