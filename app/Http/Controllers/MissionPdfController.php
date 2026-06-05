<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\SensorData;
use Barryvdh\DomPDF\Facade\Pdf;

class MissionPdfController extends Controller
{
    public function export($id)
    {
        $mission = Mission::with(['detections', 'notifications'])
            ->withCount('detections')
            ->findOrFail($id);

        // Generate heatmap base64 untuk tiap deteksi
        $heatmaps = [];
        foreach ($mission->detections as $d) {
            if ($d->thermal_snapshot) {
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
            ->get(['device_id', 'pitch', 'roll', 'recorded_at']);

        $grouped = [];
        foreach ($sensorRows as $row) {
            $grouped[$row->device_id][] = ['pitch' => $row->pitch, 'roll' => $row->roll];
        }
        foreach ($grouped as $deviceId => $points) {
            $trajectories[$deviceId] = $this->generateTrajectoryBase64($points);
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
    private function generateTrajectoryBase64(array $points): string
    {
        $W = 600; $H = 320;
        $scale = 4;

        $img = imagecreatetruecolor($W, $H);

        $white   = imagecolorallocate($img, 255, 255, 255);
        $grid    = imagecolorallocate($img, 204, 204, 204);
        $axis    = imagecolorallocate($img, 153, 153, 153);
        $red     = imagecolorallocate($img, 239, 68,  68 );
        $green   = imagecolorallocate($img, 34,  197, 94 );
        $txtClr  = imagecolorallocate($img, 102, 102, 102);

        imagefill($img, 0, 0, $white);

        // Grid
        for ($x = 0; $x <= $W; $x += $W/4) {
            imageline($img, (int)$x, 0, (int)$x, $H, $grid);
        }
        for ($y = 0; $y <= $H; $y += $H/4) {
            imageline($img, 0, (int)$y, $W, (int)$y, $grid);
        }
        // Sumbu tengah
        imageline($img, $W/2, 0, $W/2, $H, $axis);
        imageline($img, 0, $H/2, $W, $H/2, $axis);

        // Trajectory
        if (count($points) >= 2) {
            for ($i = 0; $i < count($points) - 1; $i++) {
                $x1 = (int)($W/2 + ($points[$i]['roll']   ?? 0) * $scale);
                $y1 = (int)($H/2 - ($points[$i]['pitch']  ?? 0) * $scale);
                $x2 = (int)($W/2 + ($points[$i+1]['roll'] ?? 0) * $scale);
                $y2 = (int)($H/2 - ($points[$i+1]['pitch']?? 0) * $scale);
                imageline($img, $x1, $y1, $x2, $y2, $red);
            }

            // Titik start (hijau)
            $sx = (int)($W/2 + ($points[0]['roll']  ?? 0) * $scale);
            $sy = (int)($H/2 - ($points[0]['pitch'] ?? 0) * $scale);
            imagefilledellipse($img, $sx, $sy, 10, 10, $green);

            // Titik end (merah)
            $last = end($points);
            $ex = (int)($W/2 + ($last['roll']  ?? 0) * $scale);
            $ey = (int)($H/2 - ($last['pitch'] ?? 0) * $scale);
            imagefilledellipse($img, $ex, $ey, 10, 10, $red);
        }

        imagestring($img, 1, 4, $H - 12, 'Roll (X) | Pitch (Y)', $txtClr);
        if (count($points) >= 2) {
            $first = $points[0];
            $lastP = end($points);
            $dx = ($lastP['roll'] ?? 0) - ($first['roll'] ?? 0);
            $dy = ($lastP['pitch'] ?? 0) - ($first['pitch'] ?? 0);
            $angle = rad2deg(atan2($dx, $dy));
            $dir = $angle > 0 ? 'kanan' : ($angle < 0 ? 'kiri' : 'lurus');
            $label = 'Kemiringan: '.($angle >= 0 ? '+' : '').number_format($angle, 1).'deg ('.$dir.')';
            imagestring($img, 2, 4, 4, $label, $txtClr);
        }

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}