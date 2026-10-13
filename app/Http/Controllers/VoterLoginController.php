<?php

namespace App\Http\Controllers;

use App\Models\Datacenter\Guru;
use App\Models\Datacenter\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Login pemilih dengan NIS/NISN (siswa) atau NIP/NUPTK (guru). Data pemilih diambil dari
 * aplikasi Data Center, lalu disalin ke tabel users lokal agar suara bisa dicatat.
 */
class VoterLoginController extends Controller
{
    public function show(): View
    {
        return view('auth.voter-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30'],
        ], [
            'nis.required' => 'NIS / NIP wajib diisi.',
        ]);

        $nomor = trim($data['nis']);

        try {
            DB::connection('datacenter')->getPdo();
        } catch (Throwable $e) {
            Log::error('Gagal terhubung ke database Data Center: '.$e->getMessage());

            return $this->gagal('Server Data Center tidak dapat dihubungi. Silakan hubungi panitia.');
        }

        try {
            $pemilih = $this->cariPemilih($nomor);
        } catch (Throwable $e) {
            Log::error('Gagal memproses login pemilih (cek dengan: php artisan datacenter:cek): '.$e->getMessage(), ['nomor' => $nomor]);

            return $this->gagal('Terjadi kesalahan saat membaca data Data Center. Silakan hubungi panitia.');
        }

        if (is_string($pemilih)) {
            return $this->gagal($pemilih);
        }

        Auth::login($pemilih);
        $request->session()->regenerate();

        return redirect()->route('polls.index');
    }

    /**
     * Cari siswa lalu guru dengan nomor tersebut dan salin datanya ke tabel users lokal.
     * Mengembalikan pesan kesalahan jika nomor tidak terdaftar atau tidak punya hak pilih.
     */
    private function cariPemilih(string $nomor): User|string
    {
        $siswa = Siswa::findByNomor($nomor);

        if ($siswa) {
            if (! Siswa::whereKey($siswa->id)->bolehMemilih()->exists()) {
                return 'Siswa tidak aktif sehingga tidak memiliki hak pilih.';
            }

            return User::updateOrCreate(
                ['datacenter_siswa_id' => $siswa->id],
                [
                    'nis' => $siswa->nis ?: $siswa->nisn,
                    'name' => $siswa->nama_siswa,
                    'kelas' => $siswa->kelasSekarang(),
                    'is_admin' => false,
                ],
            );
        }

        $guru = Guru::findByNomor($nomor);

        if ($guru) {
            if (! Guru::whereKey($guru->id)->bolehMemilih()->exists()) {
                return 'Guru tidak aktif sehingga tidak memiliki hak pilih.';
            }

            return User::updateOrCreate(
                ['datacenter_guru_id' => $guru->id],
                [
                    'nip' => $guru->nip,
                    'name' => $guru->nama_ptk,
                    'jabatan' => $guru->jabatan,
                    'is_admin' => false,
                ],
            );
        }

        return 'NIS / NIP tidak terdaftar.';
    }

    private function gagal(string $pesan): RedirectResponse
    {
        return back()->withInput()->withErrors(['nis' => $pesan]);
    }
}
