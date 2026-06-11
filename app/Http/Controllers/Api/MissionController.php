<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;

class MissionController extends Controller
{
    public function index()
    {
        $missions = Mission::withCount('detections')
            ->withMax('sensorData', 'suhu_max')
            ->orderBy('mission_number', 'desc')
            ->get()->map(function ($m) { $m->max_temperature = $m->sensor_data_max_suhu_max; return $m; });
        return response()->json($missions);
    }

    public function show($id)
    {
        $mission = Mission::with(['detections', 'notifications'])
            ->withCount('detections')
            ->findOrFail($id);

        // Ambil data sensor untuk trajectory + telemetri, dikelompokkan per device
        $sensorData = \App\Models\SensorData::where('mission_id', $id)
            ->select('device_id', 'pitch', 'roll', 'yaw',
                    'signal_strength', 'distance_total_m', 'recorded_at')
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Kelompokkan per device_id
        $trajectoryByDevice = [];
        $telemetryByDevice  = [];

        foreach ($sensorData as $s) {
            // Trajectory
            $trajectoryByDevice[$s->device_id][] = [
                'pitch' => $s->pitch,
                'roll'  => $s->roll,
                'yaw'   => $s->yaw,
                'time'  => $s->recorded_at,
            ];

            // Kumpulkan signal_strength untuk rata-rata
            if (!is_null($s->signal_strength)) {
                $telemetryByDevice[$s->device_id]['signal_samples'][] = $s->signal_strength;
            }

            // Ambil nilai distance_total_m terbesar (akumulasi sudah di server)
            $currentMax = $telemetryByDevice[$s->device_id]['distance_total_m'] ?? 0;
            $telemetryByDevice[$s->device_id]['distance_total_m'] =
                max($currentMax, (float)($s->distance_total_m ?? 0));
        }

        // Hitung rata-rata signal per device
        foreach ($telemetryByDevice as $deviceId => $data) {
            $samples = $data['signal_samples'] ?? [];
            $telemetryByDevice[$deviceId]['avg_signal'] = count($samples) > 0
                ? round(array_sum($samples) / count($samples), 1)
                : null;
            unset($telemetryByDevice[$deviceId]['signal_samples']); // bersihkan
        }

        $mission->trajectory_by_device = $trajectoryByDevice;
        $mission->telemetry_by_device  = $telemetryByDevice;

        return response()->json($mission);
    }

    public function currentStatus()
    {
        $mission = Mission::where('status', 'berlangsung')->latest()->first();
        return response()->json([
            'active' => $mission !== null,
            'mission' => $mission,
        ]);
    }

}