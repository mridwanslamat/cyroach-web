<?php

namespace App\Console\Commands;

use App\Models\Mission;
use App\Models\SensorData;
use Illuminate\Console\Command;

class CheckMissionTimeout extends Command
{
    protected $signature   = 'mission:check-timeout';
    protected $description = 'Tutup misi otomatis jika semua kecoa tidak aktif selama 15 menit';

    public function handle()
    {
        $missions = Mission::where('status', 'berlangsung')->get();

        foreach ($missions as $mission) {
            $lastData = SensorData::where('mission_id', $mission->id)
                ->latest('recorded_at')
                ->first();

            if (!$lastData) continue;

            $menitBerlalu = now()->diffInMinutes($lastData->recorded_at);

            if ($menitBerlalu >= 15) {
                $mission->update([
                    'status'   => 'selesai',
                    'ended_at' => $lastData->recorded_at,
                ]);

                $this->info("Misi #{$mission->mission_number} ditutup otomatis.");
            }
        }

        return Command::SUCCESS;
    }
}