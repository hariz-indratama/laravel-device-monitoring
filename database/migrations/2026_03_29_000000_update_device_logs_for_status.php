<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_logs', function (Blueprint $table) {
            $table->dropColumn(['temperature', 'humidity', 'battery', 'signal_strength']);
        });

        Schema::table('device_logs', function (Blueprint $table) {
            $table->string('status', 10)->default('on')->after('device_id');
            $table->timestamp('start_uptime')->nullable()->after('status');
            $table->timestamp('end_uptime')->nullable()->after('start_uptime');
        });
    }

    public function down(): void
    {
        Schema::table('device_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'start_uptime', 'end_uptime']);
        });

        Schema::table('device_logs', function (Blueprint $table) {
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->integer('battery')->nullable();
            $table->integer('signal_strength')->nullable();
        });
    }
};
