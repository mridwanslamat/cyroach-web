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

        $pdf = Pdf::loadView('pdf.mission-report', [
            'mission'      => $mission,
            'heatmaps'     => $heatmaps,
            'trajectories' => $trajectories,
            'telemetryPdf' => $telemetryPdf,
        ])->setPaper('a4', 'portrait');

        $filename = 'Berita_Acara_Misi_' . str_pad($mission->mission_number, 3, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    // =====================
    // HEATMAP — IRON colormap + bilinear upscale
    // =====================
    private function generateHeatmapBase64(array $grid): string
    {
        // Normalize ke 8x8 matrix
        if (isset($grid[0]) && is_array($grid[0])) {
            $matrix = $grid;
        } else {
            $matrix = [];
            for ($r = 0; $r < 8; $r++) {
                $matrix[$r] = array_slice($grid, $r * 8, 8);
            }
        }
        $flat = array_merge(...$matrix);
        $min  = min($flat);
        $max  = max($flat);

        // Bilinear upscale 8x8 → 64x64
        $OUT = 64;
        $upscaled = [];
        for ($y = 0; $y < $OUT; $y++) {
            for ($x = 0; $x < $OUT; $x++) {
                $gx = ($x / ($OUT - 1)) * 7;
                $gy = ($y / ($OUT - 1)) * 7;
                $x0 = (int)floor($gx); $x1 = min($x0 + 1, 7);
                $y0 = (int)floor($gy); $y1 = min($y0 + 1, 7);
                $tx = $gx - $x0; $ty = $gy - $y0;
                $upscaled[$y][$x] =
                    $matrix[$y0][$x0] * (1-$tx)*(1-$ty) +
                    $matrix[$y0][$x1] * $tx*(1-$ty) +
                    $matrix[$y1][$x0] * (1-$tx)*$ty +
                    $matrix[$y1][$x1] * $tx*$ty;
            }
        }

        $labelH = 20;
        $W_IMG  = 240;   // lebih lebar
        $H_IMG  = 160;   // proporsional 3:2
        $img = imagecreatetruecolor($W_IMG, $H_IMG + $labelH);

        // Render pixels heatmap
        for ($y = 0; $y < $H_IMG; $y++) {
            for ($x = 0; $x < $W_IMG; $x++) {
                $gx = ($x / ($W_IMG - 1)) * 7;
                $gy = ($y / ($H_IMG - 1)) * 7;
                $x0 = (int)floor($gx); $x1 = min($x0 + 1, 7);
                $y0 = (int)floor($gy); $y1 = min($y0 + 1, 7);
                $tx = $gx - $x0; $ty = $gy - $y0;
                $val =
                    $matrix[$y0][$x0] * (1-$tx)*(1-$ty) +
                    $matrix[$y0][$x1] * $tx*(1-$ty)      +
                    $matrix[$y1][$x0] * (1-$tx)*$ty      +
                    $matrix[$y1][$x1] * $tx*$ty;
                $ratio = ($max > $min) ? ($val - $min) / ($max - $min) : 0;
                [$r, $g, $b] = $this->ratioToColor($ratio);
                $c = imagecolorallocate($img, $r, $g, $b);
                imagesetpixel($img, $x, $y, $c);
            }
        }

        $bg  = imagecolorallocate($img, 0, 0, 0);
        $txt = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, $H_IMG, $W_IMG, $H_IMG + $labelH, $bg);
        imagestring($img, 2, 4, $H_IMG + 3,  'MAX: '.number_format($max, 1).'C', $txt);
        imagestring($img, 2, $W_IMG/2 + 4, $H_IMG + 3, 'MIN: '.number_format($min, 1).'C', $txt);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($png);
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
        $W = 300; $H = 160;
        $img = imagecreatetruecolor($W, $H);

        $cBg     = imagecolorallocate($img, 10,  10,  10 );
        $cGrid   = imagecolorallocate($img, 38,  38,  38 );
        $cAxis   = imagecolorallocate($img, 64,  64,  64 );
        $cLine   = imagecolorallocate($img, 239, 68,  68 );
        $cStart  = imagecolorallocate($img, 34,  197, 94 );
        $cEnd    = imagecolorallocate($img, 239, 68,  68 );
        $cText   = imagecolorallocate($img, 82,  82,  82 );
        $cWhite  = imagecolorallocate($img, 255, 255, 255);

        imagefill($img, 0, 0, $cBg);

        // Grid lines
        for ($x = 0; $x <= $W; $x += $W/4) {
            imageline($img, (int)$x, 0, (int)$x, $H, $cGrid);
        }
        for ($y = 0; $y <= $H; $y += $H/4) {
            imageline($img, 0, (int)$y, $W, (int)$y, $cGrid);
        }
        // Axis lines
        imageline($img, $W/2, 0, $W/2, $H, $cAxis);
        imageline($img, 0, $H/2, $W, $H/2, $cAxis);

        if (count($points) < 2) {
            $noData = imagecolorallocate($img, 64, 64, 64);
            imagestring($img, 2, $W/2 - 50, $H/2 - 6, 'Tidak ada data trajectory', $noData);
            ob_start(); imagepng($img); $png = ob_get_clean(); imagedestroy($img);
            return 'data:image/png;base64,' . base64_encode($png);
        }

        $scale = 2;
        // Draw trajectory line
        for ($i = 1; $i < count($points); $i++) {
            $x1 = (int)($W/2 + $points[$i-1]['roll']  * $scale);
            $y1 = (int)($H/2 - $points[$i-1]['pitch'] * $scale);
            $x2 = (int)($W/2 + $points[$i]['roll']    * $scale);
            $y2 = (int)($H/2 - $points[$i]['pitch']   * $scale);
            imageline($img, $x1, $y1, $x2, $y2, $cLine);
        }

        // Titik START (hijau)
        $sx = (int)($W/2 + $points[0]['roll']  * $scale);
        $sy = (int)($H/2 - $points[0]['pitch'] * $scale);
        imagefilledellipse($img, $sx, $sy, 8, 8, $cStart);
        imagestring($img, 1, $sx + 5, $sy - 4, 'START', $cStart);

        // Titik END (merah)
        $last = $points[count($points)-1];
        $ex = (int)($W/2 + $last['roll']  * $scale);
        $ey = (int)($H/2 - $last['pitch'] * $scale);
        imagefilledellipse($img, $ex, $ey, 8, 8, $cEnd);

        // Label sumbu
        imagestring($img, 1, 3,    $H-10, 'Roll ->', $cText);
        imagestring($img, 1, $W-30, 4,    'Pitch^', $cText);

        // Jumlah titik
        imagestring($img, 1, 3, 4, count($points).' titik', $cWhite);

        ob_start(); imagepng($img); $png = ob_get_clean(); imagedestroy($img);
        return 'data:image/png;base64,' . base64_encode($png);
    }
}