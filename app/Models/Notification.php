<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'mission_id',
        'device_id',
        'message',
        'notified_at',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}