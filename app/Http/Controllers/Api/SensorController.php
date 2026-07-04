<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detection;
use App\Models\Device;
use App\Models\Mission;
use App\Models\Notification;
use App\Models\SensorData;
use Illuminate\Http\Request;
use App\Events\SensorDataReceived;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'device_id'        => 'required|string',
            'thermal_grid'     => 'required|array',
            'suhu_max'         => 'required|numeric',
            'suhu_min'         => 'required|numeric',
            'pitch'            => 'required|numeric',
            'roll'             => 'required|numeric',
            'yaw'              => 'required|numeric',
            'gyro_x'           => 'nullable|numeric',
            'gyro_y'           => 'nullable|numeric',
            'gyro_z'           => 'nullable|numeric',
            'battery'          => 'nullable|numeric',
            'signal_strength'  => 'nullable|integer',
            'distance_cm'      => 'nullable|numeric',
            'dx'               => 'nullable|numeric',
            'dy'               => 'nullable|numeric',
            'distance_total_m' => 'nullable|numeric',
        ]);

        $deviceId  = $request->device_id;
        $thermalBase64ForBroadcast = $request->thermal_image ?? null;
        $suhuMax   = $request->suhu_max;
        $threshold_bawah = 35.0;
        $threshold_atas  = 42.0;

        Device::updateOrCreate(
            ['device_id' => $deviceId],
            ['status' => 'online', 'last_seen' => now()]
        );

        $mission = Mission::where('status', 'berlangsung')->latest()->first();

        if (!$mission) {
            $endCooldownKey = 'end_mission_cooldown_' . $deviceId;
            if (cache($endCooldownKey, false)) {
                return response()->json(['message' => 'Cooldown setelah end mission'], 200);
            }
            $lastNumber = Mission::max('mission_number') ?? 0;
            $mission = Mission::create([
                'mission_number' => $lastNumber + 1,
                'started_at'     => now(),
                'status'         => 'berlangsung',
            ]);
        }

        $distanceCm     = $request->distance_cm ?? 0;
        $distanceTotalM = $request->distance_total_m ?? 0;
        $dx = (float)($request->dx ?? 0);
        $dy = (float)($request->dy ?? 0);

        $cacheKey    = "last_insert_{$deviceId}";
        $lastInsert  = cache($cacheKey, 0);
        $shouldInsert = (now()->timestamp - $lastInsert) >= 5;

        if ($shouldInsert) {
            // Hitung pos_x/pos_y dari distance_total_m + yaw
            $lastSensor = SensorData::where('mission_id', $mission->id)
                ->where('device_id', $deviceId)
                ->latest('recorded_at')
                ->select('pos_x', 'pos_y', 'distance_total_m')
                ->first();
            $lastDist = (float)($lastSensor->distance_total_m ?? 0);
            $deltaDist = $distanceTotalM - $lastDist;
            $yawRad = deg2rad((float)$request->yaw);
            $posX = ($lastSensor->pos_x ?? 0) + $deltaDist * sin($yawRad);
            $posY = ($lastSensor->pos_y ?? 0) + $deltaDist * cos($yawRad);

            SensorData::create([
                'mission_id'       => $mission->id,
                'device_id'        => $deviceId,
                'thermal_grid'     => $request->thermal_grid,
                'suhu_max'         => $suhuMax,
                'suhu_min'         => $request->suhu_min,
                'pitch'            => $request->pitch,
                'roll'             => $request->roll,
                'yaw'              => $request->yaw,
                'gyro_x'           => $request->gyro_x ?? 0,
                'gyro_y'           => $request->gyro_y ?? 0,
                'gyro_z'           => $request->gyro_z ?? 0,
                'battery'          => $request->battery ?? 0,
                'signal_strength'  => $request->signal_strength ?? 0,
                'distance_cm'      => $distanceCm,
                'distance_total_m' => $distanceTotalM,
                'dx'               => $dx,
                'dy'               => $dy,
                'pos_x'            => $posX,
                'pos_y'            => $posY,
                'recorded_at'      => now(),
            ]);
            cache([$cacheKey => now()->timestamp], 60);
        }

        $detectionsCount = Detection::where('mission_id', $mission->id)->where('detection_type', 'korban')->count();
        if ($suhuMax >= $threshold_bawah && $suhuMax <= $threshold_atas) {
            $alreadyDetected = Detection::where('device_id', $deviceId)->where('mission_id', $mission->id)->where('detected_at', '>=', now()->subMinutes(5))->exists();
            if (!$alreadyDetected) { $detectionsCount++; }
        }

        try {
            event(new SensorDataReceived([
                'device_id'        => $deviceId,
                'suhu_max'         => $suhuMax,
                'suhu_min'         => $request->suhu_min,
                'thermal'          => $request->thermal_grid,
                'pitch'            => $request->pitch,
                'roll'             => $request->roll,
                'yaw'              => $request->yaw,
                'gyro_x'           => $request->gyro_x ?? 0,
                'gyro_y'           => $request->gyro_y ?? 0,
                'gyro_z'           => $request->gyro_z ?? 0,
                'battery'          => $request->battery ?? 0,
                'signal_strength'  => $request->signal_strength ?? 0,
                'distance_total_m' => $distanceTotalM,
                'dx'               => $dx,
                'dy'               => $dy,
                'thermal_image_b64' => $thermalBase64ForBroadcast,
                'detections_count' => $detectionsCount,
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast Pusher gagal: ' . $e->getMessage());
        }

        if ($suhuMax >= $threshold_bawah) {
            $detectionType = ($suhuMax <= $threshold_atas) ? 'korban' : 'panas';
            $sudahDeteksi = Detection::where('device_id', $deviceId)
                ->where('mission_id', $mission->id)
                ->where('detected_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$sudahDeteksi) {
                Detection::create([
                    'mission_id'       => $mission->id,
                    'device_id'        => $deviceId,
                    'detection_type'   => $detectionType,
                    'thermal_snapshot' => $request->thermal_grid,
                    'thermal_image_path' => (function() use ($request, $deviceId, $thermalBase64ForBroadcast) {
                        $b64 = $request->thermal_image ?? $thermalBase64ForBroadcast;
                        if (!$b64) return null;
                        $imageData = base64_decode($b64);
                        $filename = $deviceId . '_' . time() . '.jpg';
                        $savePath = '/home/cyrx6347/public_html/storage/thermal/' . $filename;
                        file_put_contents($savePath, $imageData);
                        return 'thermal/' . $filename;
                    })(),
                    'suhu_max'         => $suhuMax,
                    'suhu_min'         => $request->suhu_min,
                    'pitch'            => $request->pitch,
                    'roll'             => $request->roll,
                    'yaw'              => $request->yaw,
                    'gyro_x'           => $request->gyro_x ?? 0,
                    'gyro_y'           => $request->gyro_y ?? 0,
                    'gyro_z'           => $request->gyro_z ?? 0,
                    'detected_at'      => now(),
                ]);

                Notification::create([
                    'mission_id'  => $mission->id,
                    'device_id'   => $deviceId,
                    'message'     => $detectionType === 'korban'
                        ? "Kecoa #" . ltrim(str_replace('kecoa_', '', $deviceId), '0') . " menemukan korban"
                        : "Kecoa #" . ltrim(str_replace('kecoa_', '', $deviceId), '0') . " mendeteksi sumber panas (bukan korban)",
                    'notified_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Data diterima'], 200);
    }

    public function getTrajectory()
    {
        $mission = Mission::where('status', 'berlangsung')->latest()->first();
        if (!$mission) return response()->json([]);

        $rows = SensorData::where('mission_id', $mission->id)
            ->orderBy('recorded_at', 'asc')
            ->get(['device_id', 'pos_x', 'pos_y']);

        $trajectories = [];
        foreach ($rows as $row) {
            $trajectories[$row->device_id][] = [
                'x' => (float)$row->pos_x,
                'y' => (float)$row->pos_y,
            ];
        }

        return response()->json($trajectories);
    }

    public function endMission(Request $request)
    {
        $request->validate(['device_id' => 'required|string']);
        $deviceId = $request->device_id;

        Device::where('device_id', $deviceId)->update(['status' => 'offline']);

        $mission = Mission::where('status', 'berlangsung')->latest()->first();
        if (!$mission) {
            return response()->json(['message' => 'Tidak ada misi yang berlangsung'], 200);
        }

        $allOffline = Device::where('status', 'online')->count() === 0;
        if ($allOffline) {
            $mission->update(['status' => 'selesai', 'ended_at' => now()]);
            cache(['end_mission_cooldown_' . $deviceId => true], 60);
            event(new \App\Events\MissionEnded(['status' => 'selesai']));
            return response()->json(['message' => 'Misi selesai'], 200);
        }

        return response()->json(['message' => 'Device selesai, menunggu kecoa lain'], 200);
    }
}
