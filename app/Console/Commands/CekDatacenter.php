<?php

namespace App\Console\Commands;

use App\Models\Datacenter\Guru;
use App\Models\Datacenter\Siswa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('datacenter:cek')]
#[Description('Periksa koneksi, tabel, dan kolom database Data Center yang dipakai untuk login pemilih')]
class CekDatacenter extends Command
{
    /**
     * Kolom yang wajib ada dan kolom opsional (dilewati jika tidak ada) per tabel.
     *
     * @var array<string, array{wajib: list<string>, opsional: list<string>}>
     */
    private const STRUKTUR = [
        'siswa' => ['wajib' => ['id', 'nis', 'nisn', 'nama_siswa', 'is_aktif'], 'opsional' => ['status_siswa', 'account_status']],
        'guru' => ['wajib' => ['id', 'nip', 'nama_ptk', 'jabatan', 'is_aktif'], 'opsional' => ['nuptk', 'account_status']],
        'siswa_rombel' => ['wajib' => ['siswa_id', 'rombongan_belajar_id', 'tahun_ajaran_id'], 'opsional' => []],
        'rombongan_belajar' => ['wajib' => ['id', 'nama_rombel'], 'opsional' => []],
        'tahun_ajaran' => ['wajib' => ['id', 'is_aktif'], 'opsional' => []],
    ];

    public function handle(): int
    {
        $config = config('database.connections.datacenter');
        $this->line(sprintf(
            'Koneksi datacenter: %s://%s@%s:%s/%s',
            $config['driver'] ?? '-',
            $config['username'] ?? '-',
            $config['host'] ?? '-',
            $config['port'] ?? '-',
            $config['database'] ?? '-',
        ));

        try {
            DB::connection('datacenter')->getPdo();
        } catch (Throwable $e) {
            $this->error('GAGAL terhubung: '.$e->getMessage());
            $this->line('Periksa DATACENTER_DB_* di file .env, lalu jalankan: php artisan config:clear');

            return self::FAILURE;
        }

        $this->info('Koneksi berhasil.');
        $schema = DB::connection('datacenter')->getSchemaBuilder();
        $adaMasalah = false;

        foreach (self::STRUKTUR as $tabel => $kolom) {
            if (! $schema->hasTable($tabel)) {
                $this->error("Tabel '{$tabel}' tidak ditemukan (atau user database tidak punya hak SELECT).");
                $adaMasalah = true;

                continue;
            }

            $ada = $schema->getColumnListing($tabel);
            $kurangWajib = array_diff($kolom['wajib'], $ada);
            $kurangOpsional = array_diff($kolom['opsional'], $ada);

            if ($kurangWajib) {
                $this->error("Tabel '{$tabel}' tidak punya kolom wajib: ".implode(', ', $kurangWajib));
                $adaMasalah = true;
            } else {
                $this->info("Tabel '{$tabel}' OK.");
            }

            if ($kurangOpsional) {
                $this->warn("  Kolom opsional tidak ada di '{$tabel}': ".implode(', ', $kurangOpsional).' (aman, akan dilewati; jalankan migrasi aplikasi datacenter untuk melengkapinya)');
            }
        }

        if ($adaMasalah) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Siswa berhak memilih: '.Siswa::bolehMemilih()->count());
        $this->info('Guru berhak memilih : '.Guru::bolehMemilih()->count());

        return self::SUCCESS;
    }
}
