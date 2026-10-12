<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pemilih (siswa) login dengan NIS dari Data Center, tanpa email/password.
            $table->unsignedBigInteger('datacenter_siswa_id')->nullable()->unique()->after('id');
            $table->string('nis', 20)->nullable()->unique()->after('datacenter_siswa_id');
            $table->string('kelas', 100)->nullable()->after('name');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['datacenter_siswa_id']);
            $table->dropUnique(['nis']);
            $table->dropColumn(['datacenter_siswa_id', 'nis', 'kelas']);
        });
    }
};
