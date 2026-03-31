<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('type')->default('temperature_sensor');
            $table->string('location')->nullable();
            $table->string('status')->default('offline');
            $table->float('temperature_min')->default(0);
            $table->float('temperature_max')->default(40);
            $table->integer('battery_threshold')->default(20);
            $table->integer('heartbeat_interval')->default(300);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
