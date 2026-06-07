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
            'battery'          => 'nullable|integer',
            'signal_strength'  => 'nullable|integer',
            'distance_cm'      => 'nullable|numeric',
            'dx'               => 'nullable|numeric', // ← BARU: delta posisi X dari Android
            'dy'               => 'nullable|numeric', // ← BARU: delta posisi Y dari Android
            'distance_total_m' => 'nullable|numeric', // ← BARU: jarak total dari Android
        ]);

        $deviceId  = $request->device_id;
        // Simpan thermal image jika ada
        $thermalImagePath = null;
        if ($request->has('thermal_image') && $request->thermal_image) {
            $imageData = base64_decode($request->thermal_image);
            $filename = $deviceId . '_' . time() . '.jpg';
            $savePath = '/home/cyrx6347/public_html/storage/thermal/' . $filename;
            file_put_contents($savePath, $imageData);
            $thermalImagePath = 'thermal/' . $filename;
        }
        $suhuMax   = $request->suhu_max;
        $threshold = 37.5;

        // 1. Update atau daftarkan device
        Device::updateOrCreate(
            ['device_id' => $deviceId],
            ['status' => 'online', 'last_seen' => now()]
        );

        // 2. Cek apakah ada misi yang sedang berlangsung
        $mission = Mission::where('status', 'berlangsung')->latest()->first();

        // 3. Kalau belum ada misi, buat misi baru otomatis
        if (!$mission) {
            $lastNumber = Mission::max('mission_number') ?? 0;
            $mission = Mission::create([
                'mission_number' => $lastNumber + 1,
                'started_at'     => now(),
                'status'         => 'berlangsung',
            ]);
        }

        // 4. Simpan data sensor (throttle: hanya setiap 5 detik per device)
        $distanceCm     = $request->distance_cm ?? 0;
        $distanceTotalM = $request->distance_total_m ?? 0;

        $cacheKey    = "last_insert_{$deviceId}";
        $lastInsert  = cache($cacheKey, 0);
        $shouldInsert = (now()->timestamp - $lastInsert) >= 5;

        if ($shouldInsert) {
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
                'recorded_at'      => now(),
            ]);
            cache([$cacheKey => now()->timestamp], 60);
        }

        // 5. Broadcast ke browser via Pusher
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
            'dx'               => $request->dx ?? 0, // ← BARU: untuk trajectory di web
            'dy'               => $request->dy ?? 0, // ← BARU: untuk trajectory di web
            'thermal_image_url' => $thermalImagePath ? url('storage/' . $thermalImagePath) : null,
        ]));

        // 6. Cek threshold deteksi korban
        if ($suhuMax >= $threshold) {
            $sudahDeteksi = Detection::where('device_id', $deviceId)
                ->where('mission_id', $mission->id)
                ->where('detected_at', '>=', now()->subMinutes(5))
                ->exists();

            if (!$sudahDeteksi) {
                Detection::create([
                    'mission_id'       => $mission->id,
                    'device_id'        => $deviceId,
                    'thermal_snapshot' => $request->thermal_grid,
                    'thermal_image_path' => $thermalImagePath,
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
                    'message'     => "Kecoa #" . ltrim(str_replace('kecoa_', '', $deviceId), '0') . " menemukan korban",
                    'notified_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Data diterima'], 200);
    }
    
    
    public function getTrajectory()
    {
        $mission = \App\Models\Mission::where("status", "berlangsung")->latest()->first();
        if (!$mission) {
            return response()->json([]);
        }

        $rows = \App\Models\SensorData::where("mission_id", $mission->id)
            ->orderBy("recorded_at", "asc")
            ->get(["device_id", "dx", "dy"]);

        $trajectories = [];
        foreach ($rows as $row) {
            if (!isset($trajectories[$row->device_id])) {
                $trajectories[$row->device_id] = [["x" => 0, "y" => 0]];
            }
            $last = end($trajectories[$row->device_id]);
            $dx = (float)($row->dx ?? 0);
            $dy = (float)($row->dy ?? 0);
            if ($dx !== 0.0 || $dy !== 0.0) {
                $trajectories[$row->device_id][] = [
                    "x" => $last["x"] + $dx,
                    "y" => $last["y"] + $dy,
                ];
            }
        }

        return response()->json($trajectories);
    }
    public function endMission(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $deviceId = $request->device_id;

        // Tandai device sebagai offline
        Device::where('device_id', $deviceId)
            ->update(['status' => 'offline']);

        // Cek misi yang sedang berlangsung
        $mission = Mission::where('status', 'berlangsung')->latest()->first();

        if (!$mission) {
            return response()->json(['message' => 'Tidak ada misi yang berlangsung'], 200);
        }

        // Cek apakah semua device di misi ini sudah offline
        $allOffline = Device::where('status', 'online')->count() === 0;

        if ($allOffline) {
            $mission->update([
                'status'   => 'selesai',
                'ended_at' => now(),
            ]);

            event(new \App\Events\MissionEnded(['status' => 'selesai']));
            return response()->json(['message' => 'Misi selesai'], 200);
        }

        return response()->json(['message' => 'Device selesai, menunggu kecoa lain'], 200);
    }

}
