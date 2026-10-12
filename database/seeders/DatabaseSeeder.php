<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Akun admin demo: admin@voting.test / password
     *
     * Pemilih (siswa) tidak di-seed: mereka login dengan NIS yang diambil
     * dari database aplikasi Data Center (koneksi "datacenter").
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@voting.test',
            'is_admin' => true,
        ]);

        $osis = Poll::create([
            'title' => 'Pemilihan Ketua OSIS 2026/2027',
            'description' => "Pilih calon Ketua OSIS yang menurut Anda paling layak memimpin.\nSetiap siswa hanya dapat memberikan satu suara.",
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->addDays(7)->endOfDay(),
            'created_by' => $admin->id,
        ]);

        foreach ([
            ['Calon Ketua OSIS 1', "Visi: OSIS yang aktif dan inklusif.\nMisi: Mengadakan kegiatan ekstrakurikuler rutin setiap bulan."],
            ['Calon Ketua OSIS 2', "Visi: Sekolah hijau dan berprestasi.\nMisi: Program bank sampah dan kelas literasi."],
            ['Calon Ketua OSIS 3', "Visi: Siswa kreatif dan berdaya saing.\nMisi: Membuka klub teknologi dan kewirausahaan."],
        ] as $i => [$name, $description]) {
            $osis->candidates()->create(['number' => $i + 1, 'name' => $name, 'description' => $description]);
        }

        $mpk = Poll::create([
            'title' => 'Pemilihan Ketua MPK 2026/2027',
            'description' => 'Pilih calon Ketua Majelis Perwakilan Kelas (MPK).',
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->addDays(7)->endOfDay(),
            'created_by' => $admin->id,
        ]);

        foreach ([
            ['Calon Ketua MPK 1', 'Visi: MPK sebagai jembatan aspirasi seluruh kelas.'],
            ['Calon Ketua MPK 2', 'Visi: Pengawasan program OSIS yang transparan.'],
        ] as $i => [$name, $description]) {
            $mpk->candidates()->create(['number' => $i + 1, 'name' => $name, 'description' => $description]);
        }
    }
}
