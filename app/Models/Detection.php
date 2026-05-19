<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detection extends Model
{
    protected $fillable = [
        'mission_id',
        'device_id',
        'thermal_snapshot',
        'suhu_max',
        'suhu_min',
        'pitch',
        'roll',
        'yaw',
        'gyro_x',
        'gyro_y',
        'gyro_z',
        'detected_at',
    ];

    protected $casts = [
        'thermal_snapshot' => 'array',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}