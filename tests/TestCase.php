<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Jangan pernah menyentuh database Data Center asli saat testing:
        // ganti dengan SQLite in-memory yang berisi tiruan tabel-tabelnya.
        config(['database.connections.datacenter' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);
        DB::purge('datacenter');

        $schema = Schema::connection('datacenter');

        $schema->create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique();
            $table->string('nis')->nullable();
            $table->string('nama_siswa');
            $table->string('password')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('status_siswa')->default('Aktif');
            $table->string('account_status')->default('active');
            $table->timestamps();
        });

        $schema->create('guru', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('nuptk')->nullable();
            $table->string('nama_ptk');
            $table->string('jabatan')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('account_status')->default('active');
            $table->timestamps();
        });

        $schema->create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tahun_ajaran');
            $table->boolean('is_aktif')->default(false);
        });

        $schema->create('rombongan_belajar', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rombel');
            $table->unsignedBigInteger('tahun_ajaran_id');
        });

        $schema->create('siswa_rombel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('rombongan_belajar_id');
            $table->unsignedBigInteger('tahun_ajaran_id');
        });
    }
}
