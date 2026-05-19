<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'mission_number',
        'started_at',
        'ended_at',
        'status',
    ];

    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }

    public function detections()
    {
        return $this->hasMany(Detection::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}