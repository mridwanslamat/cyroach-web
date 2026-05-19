<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('battery')->default(100)->after('gyro_z');
            $table->float('signal_strength')->default(0)->after('battery');
            $table->float('distance_cm')->default(0)->after('signal_strength');
            $table->float('distance_total_m')->default(0)->after('distance_cm');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn(['battery', 'signal_strength', 'distance_cm', 'distance_total_m']);
        });
    }
};