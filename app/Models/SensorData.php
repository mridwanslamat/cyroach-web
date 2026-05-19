<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $fillable = [
        'mission_id',
        'device_id',
        'thermal_grid',
        'suhu_max',
        'suhu_min',
        'pitch',
        'roll',
        'yaw',
        'gyro_x',
        'gyro_y',
        'gyro_z',
        'battery',
        'signal_strength',
        'distance_cm',
        'distance_total_m',
        'recorded_at',
    ];

    protected $casts = [
        'thermal_grid' => 'array',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}