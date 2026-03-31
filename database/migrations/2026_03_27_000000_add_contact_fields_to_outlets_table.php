<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('email', 255)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['email', 'kota', 'provinsi', 'kode_pos']);
        });
    }
};
