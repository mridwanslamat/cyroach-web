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

        $flat = array_merge(...$matrix);
        $min  = min($flat);
        $max  = max($flat);
        $range = $max - $min ?: 1;

        $cellW = 30;
        $cellH = 20;
        $W = 8 * $cellW;
        $H = 8 * $cellH;

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$W}' height='{$H}'>";
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $val   = $matrix[$r][$c];
                $ratio = ($val - $min) / $range;
                [$red, $green, $blue] = $this->ratioToColor($ratio);
                $x = $c * $cellW;
                $y = $r * $cellH;
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
        $W = 300; $H = 160;
        $scale = 2;

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$W}' height='{$H}' style='background:#0a0a0a'>";

        // Grid
        for ($x = 0; $x <= $W; $x += $W/4) {
            $svg .= "<line x1='{$x}' y1='0' x2='{$x}' y2='{$H}' stroke='#262626' stroke-width='0.5'/>";
        }
        for ($y = 0; $y <= $H; $y += $H/4) {
            $svg .= "<line x1='0' y1='{$y}' x2='{$W}' y2='{$y}' stroke='#262626' stroke-width='0.5'/>";
        }
        $svg .= "<line x1='".($W/2)."' y1='0' x2='".($W/2)."' y2='{$H}' stroke='#404040' stroke-width='1'/>";
        $svg .= "<line x1='0' y1='".($H/2)."' x2='{$W}' y2='".($H/2)."' stroke='#404040' stroke-width='1'/>";

        if (count($points) >= 2) {
            $polyline = '';
            foreach ($points as $pt) {
                $x = $W/2 + ($pt['roll'] ?? 0) * $scale;
                $y = $H/2 - ($pt['pitch'] ?? 0) * $scale;
                $polyline .= "{$x},{$y} ";
            }
            $svg .= "<polyline points='{$polyline}' fill='none' stroke='#ef4444' stroke-width='1.5'/>";

            // Titik start
            $sx = $W/2 + ($points[0]['roll'] ?? 0) * $scale;
            $sy = $H/2 - ($points[0]['pitch'] ?? 0) * $scale;
            $svg .= "<circle cx='{$sx}' cy='{$sy}' r='4' fill='#22c55e'/>";

            // Titik end
            $last = end($points);
            $ex = $W/2 + ($last['roll'] ?? 0) * $scale;
            $ey = $H/2 - ($last['pitch'] ?? 0) * $scale;
            $svg .= "<circle cx='{$ex}' cy='{$ey}' r='4' fill='#ef4444'/>";
        }

        $svg .= "<text x='4' y='".($H-4)."' fill='#525252' font-size='8'>Roll → | ↑ Pitch</text>";
        $svg .= "</svg>";

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}