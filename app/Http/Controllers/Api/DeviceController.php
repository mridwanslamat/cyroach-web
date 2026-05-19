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
        // Tandai device offline kalau tidak ada data lebih dari 1 menit
        Device::where('last_seen', '<=', now()->subMinute())
            ->update(['status' => 'offline']);

        // Ambil data sensor terbaru per device
        $devices = Device::all()->map(function ($device) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->latest('recorded_at')
                ->first();

            return [
                'device_id' => $device->device_id,
                'status'    => $device->status,
                'last_seen' => $device->last_seen,
                'latest'    => $latest,
            ];
        });

        // Ambil notifikasi terbaru
        $notifications = Notification::latest('notified_at')
            ->take(20)
            ->get();

        return response()->json([
            'devices'       => $devices,
            'notifications' => $notifications,
        ]);
    }
}