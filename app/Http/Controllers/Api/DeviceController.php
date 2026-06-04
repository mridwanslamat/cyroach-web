<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Notification;
use App\Models\SensorData;

class DeviceController extends Controller
{
    public function live()
    {
        // Cek apakah ada misi yang sedang berlangsung
        $activeMission = \App\Models\Mission::where('status', 'berlangsung')->latest()->first();

        if (!$activeMission) {
            return response()->json([
                'devices'       => [],
                'notifications' => [],
            ]);
        }

        // Tandai device offline kalau tidak ada data lebih dari 1 menit
        Device::where('last_seen', '<=', now()->subMinute())
            ->update(['status' => 'offline']);

        // Ambil device yang terlibat di misi aktif saja
        $deviceIds = \App\Models\SensorData::where('mission_id', $activeMission->id)
            ->distinct()
            ->pluck('device_id');

        $devices = Device::whereIn('device_id', $deviceIds)->get()->map(function ($device) use ($activeMission) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->where('mission_id', $activeMission->id)
                ->latest('recorded_at')
                ->first();

            return [
                'device_id' => $device->device_id,
                'status'    => $device->status,
                'last_seen' => $device->last_seen,
                'latest'    => $latest,
            ];
        });

        // Notifikasi hanya dari misi aktif
        $notifications = \App\Models\Notification::where('mission_id', $activeMission->id)
            ->latest('notified_at')
            ->take(20)
            ->get();

        return response()->json([
            'devices'       => $devices,
            'notifications' => $notifications,
        ]);
    }
}