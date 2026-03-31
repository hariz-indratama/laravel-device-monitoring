<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('last_seen_at');
            $table->integer('missed_count')->default(0);
            $table->integer('interval_seconds')->default(300);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_heartbeats');
    }
};
