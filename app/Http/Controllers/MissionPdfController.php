<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\SensorData;
use Barryvdh\DomPDF\Facade\Pdf;

class MissionPdfController extends Controller
{
    public function export($id, \Illuminate\Http\Request $request)
    {
        $formData = [
            'lokasi'   => $request->get('lokasi', '—'),
            'komandan' => $request->get('komandan', '—'),
            'operator' => $request->get('operator', '—'),
            'instansi' => $request->get('instansi', '—'),
            'catatan'  => $request->get('catatan', ''),
        ];
        
        $mission = Mission::with(['detections', 'notifications'])
            ->withCount('detections')
            ->findOrFail($id);

        // Generate heatmap base64 untuk tiap deteksi
        $heatmaps = [];
        foreach ($mission->detections as $d) {
            if ($d->thermal_image_path && file_exists('/home/cyrx6347/public_html/storage/' . $d->thermal_image_path)) {
                $heatmaps[$d->id] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents('/home/cyrx6347/public_html/storage/' . $d->thermal_image_path));
            } elseif ($d->thermal_snapshot) {
                $heatmaps[$d->id] = $this->generateHeatmapBase64($d->thermal_snapshot);
                // Hitung ulang suhu_min dari thermal_snapshot
                $flat = array_merge(...(array_map(fn($r) => is_array($r) ? $r : [$r], $d->thermal_snapshot)));
                $flat = array_filter($flat, fn($v) => is_numeric($v) && $v > 0);
                if (count($flat) > 0) {
                    $d->suhu_min = round(min($flat), 1);
                    $d->suhu_max = round(max($flat), 1);
                }
            }
        }

        // Generate trajectory base64 per device
        $trajectories = [];
        $sensorRows = SensorData::where('mission_id', $mission->id)
            ->orderBy('recorded_at')
            ->get(['device_id', 'pos_x', 'pos_y', 'recorded_at']);

        $grouped = [];
        foreach ($sensorRows as $row) {
            $grouped[$row->device_id][] = [
                'x'    => (float)($row->pos_x ?? 0),
                'y'    => (float)($row->pos_y ?? 0),
                'time' => $row->recorded_at,
            ];
        }

        // Kelompokkan deteksi per device untuk marker di trajectory
        $detectionsByDevice = [];
        foreach ($mission->detections as $d) {
            $detectionsByDevice[$d->device_id][] = $d;
        }

        foreach ($grouped as $deviceId => $points) {
            $deviceDetections = $detectionsByDevice[$deviceId] ?? [];
            $trajectories[$deviceId] = $this->generateTrajectoryBase64($points, $deviceDetections);
        }

        // Agregasi telemetri per device: rata-rata signal & total jarak
        $telemetryPdf = [];
        $allSensorFull = SensorData::where('mission_id', $mission->id)
            ->get(['device_id', 'signal_strength', 'distance_total_m']);

        foreach ($allSensorFull as $row) {
            if (!is_null($row->signal_strength)) {
                $telemetryPdf[$row->device_id]['signal_samples'][] = $row->signal_strength;
            }
            $currentMax = $telemetryPdf[$row->device_id]['distance_total_m'] ?? 0;
            $telemetryPdf[$row->device_id]['distance_total_m'] =
                max($currentMax, (float)($row->distance_total_m ?? 0));
        }
        foreach ($telemetryPdf as $deviceId => $data) {
            $samples = $data['signal_samples'] ?? [];
            $telemetryPdf[$deviceId]['avg_signal'] = count($samples) > 0
                ? round(array_sum($samples) / count($samples), 1)
                : null;
            unset($telemetryPdf[$deviceId]['signal_samples']);
        }

        // Konversi waktu ke WIB (UTC+7)
        $wib = new \DateTimeZone('Asia/Jakarta');
        if ($mission->started_at) {
            $mission->started_at = \Carbon\Carbon::parse($mission->started_at)->setTimezone($wib);
        }
        if ($mission->ended_at) {
            $mission->ended_at = \Carbon\Carbon::parse($mission->ended_at)->setTimezone($wib);
        }
        foreach ($mission->detections as $d) {
            if ($d->detected_at) {
                $d->detected_at = \Carbon\Carbon::parse($d->detected_at)->setTimezone($wib);
            }
        }

        $pdf = Pdf::loadView('pdf.mission-report', [
            'mission'      => $mission,
            'heatmaps'     => $heatmaps,
            'trajectories' => $trajectories,
            'telemetryPdf' => $telemetryPdf,
            'kecoaCount'   => count($trajectories),
            'formData'     => $formData,
        ])->setPaper('a4', 'portrait');

        $filename = 'Berita_Acara_Misi_' . str_pad($mission->mission_number, 3, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    // =====================
    // HEATMAP — IRON colormap + bilinear upscale
    // =====================
    private function generateHeatmapBase64(array $grid): string
    {
        if (isset($grid[0]) && is_array($grid[0])) {
            $matrix = $grid;
        } else {
            $matrix = [];
            for ($r = 0; $r < 8; $r++) {
                $matrix[$r] = array_slice($grid, $r * 8, 8);
            }
        }

        // Bilinear upscale 8x8 → 32x32
        $out = 32;
        $upscaled = [];
        for ($y = 0; $y < $out; $y++) {
            for ($x = 0; $x < $out; $x++) {
                $gx = ($x / ($out - 1)) * 7;
                $gy = ($y / ($out - 1)) * 7;
                $x0 = (int)$gx; $x1 = min($x0 + 1, 7);
                $y0 = (int)$gy; $y1 = min($y0 + 1, 7);
                $tx = $gx - $x0; $ty = $gy - $y0;
                $upscaled[$y][$x] = $matrix[$y0][$x0] * (1-$tx)*(1-$ty)
                                + $matrix[$y0][$x1] * $tx*(1-$ty)
                                + $matrix[$y1][$x0] * (1-$tx)*$ty
                                + $matrix[$y1][$x1] * $tx*$ty;
            }
        }

        $flat = array_merge(...$upscaled);
        $min  = min($flat);
        $max  = max($flat);
        $range = $max - $min ?: 1;

        $cellW = 8; $cellH = 8;
        $W = $out * $cellW; $H = $out * $cellH;

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$W}' height='{$H}'>";
        for ($r = 0; $r < $out; $r++) {
            for ($c = 0; $c < $out; $c++) {
                $ratio = ($upscaled[$r][$c] - $min) / $range;
                [$red, $green, $blue] = $this->ratioToColor($ratio);
                $x = $c * $cellW; $y = $r * $cellH;
                $svg .= "<rect x='{$x}' y='{$y}' width='{$cellW}' height='{$cellH}' fill='rgb({$red},{$green},{$blue})'/>";
            }
        }
        $svg .= "</svg>";

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    // IRON colormap
    private function ratioToColor(float $ratio): array
    {
        $stops = [
            [0.00, [0,   0,   0  ]],
            [0.20, [80,  0,   130]],
            [0.40, [150, 0,   100]],
            [0.60, [220, 30,  0  ]],
            [0.75, [255, 120, 0  ]],
            [0.90, [255, 220, 0  ]],
            [1.00, [255, 255, 255]],
        ];

        $lo = $stops[0];
        $hi = $stops[count($stops) - 1];
        for ($i = 0; $i < count($stops) - 1; $i++) {
            if ($ratio >= $stops[$i][0] && $ratio <= $stops[$i+1][0]) {
                $lo = $stops[$i]; $hi = $stops[$i+1]; break;
            }
        }
        $t = ($hi[0] - $lo[0]) > 0 ? ($ratio - $lo[0]) / ($hi[0] - $lo[0]) : 0;
        return [
            (int)round($lo[1][0] + ($hi[1][0] - $lo[1][0]) * $t),
            (int)round($lo[1][1] + ($hi[1][1] - $lo[1][1]) * $t),
            (int)round($lo[1][2] + ($hi[1][2] - $lo[1][2]) * $t),
        ];
    }

    // =====================
    // TRAJECTORY — plot 2D pitch vs roll
    // =====================
    private function findNearestPdfPoint(array $points, $targetTime)
    {
        $best = null; $bestDiff = INF;
        $target = \Carbon\Carbon::parse($targetTime)->timestamp;
        foreach ($points as $pt) {
            if (empty($pt['time'])) continue;
            $diff = abs(\Carbon\Carbon::parse($pt['time'])->timestamp - $target);
            if ($diff < $bestDiff) { $bestDiff = $diff; $best = $pt; }
        }
        return $best;
    }

    // =====================
    // TRAJECTORY — plot 2D posisi X/Y (meter), dengan skala & marker deteksi
    // =====================
    private function generateTrajectoryBase64(array $points, array $detections = []): string
    {
        $W = 600; $H = 320;
        $PAD = 40;

        $img = imagecreatetruecolor($W, $H);

        $white     = imagecolorallocate($img, 255, 255, 255);
        $gridClr   = imagecolorallocate($img, 224, 224, 224);
        $axis      = imagecolorallocate($img, 153, 153, 153);
        $red       = imagecolorallocate($img, 239, 68,  68 );
        $green     = imagecolorallocate($img, 34,  197, 94 );
        $txtClr    = imagecolorallocate($img, 102, 102, 102);
        $korbanClr = imagecolorallocate($img, 234, 179, 8);
        $panasClr  = imagecolorallocate($img, 194, 65,  12);

        imagefill($img, 0, 0, $white);

        if (count($points) < 2) {
            imagestring($img, 3, (int)($W/2 - 90), (int)($H/2), 'Tidak ada data trajectory', $txtClr);
            ob_start(); imagepng($img); $pngData = ob_get_clean(); imagedestroy($img);
            return 'data:image/png;base64,' . base64_encode($pngData);
        }

        $plotW = $W - 2*$PAD;
        $plotH = $H - 2*$PAD - 16;

        // Auto-rescale bounding box berdasar posisi X/Y (meter)
        $allX = array_map(fn($p) => (float)($p['x'] ?? 0), $points);
        $allY = array_map(fn($p) => (float)($p['y'] ?? 0), $points);
        $minX = min($allX); $maxX = max($allX);
        $minY = min($allY); $maxY = max($allY);
        $rangeX = $maxX - $minX ?: 0.01;
        $rangeY = $maxY - $minY ?: 0.01;
        $range = max($rangeX, $rangeY);
        $pad = $range * 0.15;
        $x0 = $minX - $pad; $y0 = $minY - $pad; $span = $range + 2*$pad;

        $toX = fn($x) => (int)($PAD + (($x - $x0) / $span) * $plotW);
        $toY = fn($y) => (int)($PAD + $plotH - (($y - $y0) / $span) * $plotH);

        // Grid + label skala
        for ($i = 0; $i <= 8; $i++) {
            $gx = $PAD + $i * $plotW / 8;
            $gy = $PAD + $i * $plotH / 8;
            imageline($img, (int)$gx, $PAD, (int)$gx, $PAD + $plotH, $gridClr);
            imageline($img, $PAD, (int)$gy, $PAD + $plotW, (int)$gy, $gridClr);
            if ($i % 2 === 0) {
                $valX = number_format($x0 + $i * $span / 8, 1);
                $valY = number_format($y0 + $span - $i * $span / 8, 1);
                imagestring($img, 1, (int)$gx - 8, $PAD + $plotH + 3, $valX.'m', $txtClr);
                imagestring($img, 1, $PAD - 34, (int)$gy - 4, $valY.'m', $txtClr);
            }
        }
        imagerectangle($img, $PAD, $PAD, $PAD + $plotW, $PAD + $plotH, $axis);

        $cellSize = number_format($span / 8, 2);
        imagestring($img, 2, $W - 140, 4, "Skala: {$cellSize} m/kotak", $txtClr);

        // Garis trajectory
        for ($i = 0; $i < count($points) - 1; $i++) {
            $x1 = $toX($points[$i]['x'] ?? 0);
            $y1 = $toY($points[$i]['y'] ?? 0);
            $x2 = $toX($points[$i+1]['x'] ?? 0);
            $y2 = $toY($points[$i+1]['y'] ?? 0);
            imageline($img, $x1, $y1, $x2, $y2, $red);
        }

        // Titik start (hijau) & end (merah)
        $sx = $toX($points[0]['x'] ?? 0);
        $sy = $toY($points[0]['y'] ?? 0);
        imagefilledellipse($img, $sx, $sy, 12, 12, $green);
        imagestring($img, 3, $sx-3, $sy-16, 'S', $green);

        $last = end($points);
        $ex = $toX($last['x'] ?? 0);
        $ey = $toY($last['y'] ?? 0);
        imagefilledellipse($img, $ex, $ey, 12, 12, $red);
        imagestring($img, 3, $ex-3, $ey-16, 'E', $red);

        // Marker deteksi korban/panas
        foreach ($detections as $d) {
            $nearest = $this->findNearestPdfPoint($points, $d->detected_at);
            if (!$nearest) continue;
            $mx = $toX($nearest['x'] ?? 0);
            $my = $toY($nearest['y'] ?? 0);
            $isKorban = $d->detection_type !== 'panas';
            $clr = $isKorban ? $korbanClr : $panasClr;
            imagefilledellipse($img, $mx, $my, 10, 10, $clr);
            imagestring($img, 2, $mx-3, $my-6, $isKorban ? '!' : 'H', $white);
        }

        imagestring($img, 1, 4, $H - 12, 'X (m) | Y (m)', $txtClr);

        $first = $points[0];
        $dx = ($last['x'] ?? 0) - ($first['x'] ?? 0);
        $dy = ($last['y'] ?? 0) - ($first['y'] ?? 0);
        $angle = rad2deg(atan2($dx, $dy));
        $dir = $angle > 0 ? 'kanan' : ($angle < 0 ? 'kiri' : 'lurus');
        $label = 'Kemiringan: '.($angle >= 0 ? '+' : '').number_format($angle, 1).'deg ('.$dir.')';
        imagestring($img, 3, $PAD, 4, $label, $txtClr);

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
