<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guru login dengan NIP dari Data Center, sama seperti siswa login dengan NIS.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('datacenter_guru_id')->nullable()->unique()->after('datacenter_siswa_id');
            $table->string('nip', 30)->nullable()->unique()->after('nis');
            $table->string('jabatan', 191)->nullable()->after('kelas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['datacenter_guru_id']);
            $table->dropUnique(['nip']);
            $table->dropColumn(['datacenter_guru_id', 'nip', 'jabatan']);
        });
    }
};
