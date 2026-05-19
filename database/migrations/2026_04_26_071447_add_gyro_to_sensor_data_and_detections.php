<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('gyro_x')->default(0)->after('yaw');
            $table->float('gyro_y')->default(0)->after('gyro_x');
            $table->float('gyro_z')->default(0)->after('gyro_y');
        });

        Schema::table('detections', function (Blueprint $table) {
            $table->float('gyro_x')->default(0)->after('yaw');
            $table->float('gyro_y')->default(0)->after('gyro_x');
            $table->float('gyro_z')->default(0)->after('gyro_y');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn(['gyro_x', 'gyro_y', 'gyro_z']);
        });

        Schema::table('detections', function (Blueprint $table) {
            $table->dropColumn(['gyro_x', 'gyro_y', 'gyro_z']);
        });
    }
};