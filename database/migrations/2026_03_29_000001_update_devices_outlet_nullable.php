<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('outlet_id')->nullable()->change();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('assigned_at');
            $table->foreignId('outlet_id')->nullable(false)->change();
        });
    }
};
