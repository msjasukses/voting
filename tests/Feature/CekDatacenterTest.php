<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CekDatacenterTest extends TestCase
{
    public function test_reports_success_and_eligible_voter_counts(): void
    {
        DB::connection('datacenter')->table('siswa')->insert(['nisn' => '1', 'nis' => '88001', 'nama_siswa' => 'Budi']);
        DB::connection('datacenter')->table('guru')->insert(['nip' => '1001617', 'nama_ptk' => 'Pak Budi']);

        $this->artisan('datacenter:cek')
            ->expectsOutput('Koneksi berhasil.')
            ->expectsOutput("Tabel 'guru' OK.")
            ->expectsOutput('Siswa berhak memilih: 1')
            ->expectsOutput('Guru berhak memilih : 1')
            ->assertSuccessful();
    }

    public function test_warns_about_missing_optional_columns_without_failing(): void
    {
        Schema::connection('datacenter')->table('guru', fn ($table) => $table->dropColumn('nuptk'));

        $this->artisan('datacenter:cek')
            ->expectsOutputToContain("Kolom opsional tidak ada di 'guru': nuptk")
            ->assertSuccessful();
    }

    public function test_fails_when_a_required_table_is_missing(): void
    {
        Schema::connection('datacenter')->drop('guru');

        $this->artisan('datacenter:cek')
            ->expectsOutput("Tabel 'guru' tidak ditemukan (atau user database tidak punya hak SELECT).")
            ->assertFailed();
    }
}
