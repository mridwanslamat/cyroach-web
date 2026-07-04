<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('detections', function (Blueprint $table) {
            $table->string('detection_type')->default('korban')->after('device_id');
            // 'korban' = suhu 35-42°C, 'panas' = suhu > 42°C
        });
    }
    public function down(): void {
        Schema::table('detections', function (Blueprint $table) {
            $table->dropColumn('detection_type');
        });
    }
};
