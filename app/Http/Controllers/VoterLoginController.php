<?php

namespace App\Http\Controllers;

use App\Models\Datacenter\Guru;
use App\Models\Datacenter\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $siswa = Siswa::findByNomor($nomor);
            $guru = $siswa ? null : Guru::findByNomor($nomor);
        } catch (Throwable $e) {
            Log::error('Gagal terhubung ke database Data Center: '.$e->getMessage());

            return $this->gagal('Server Data Center tidak dapat dihubungi. Silakan hubungi panitia.');
        }

        if ($siswa) {
            if (! Siswa::whereKey($siswa->id)->bolehMemilih()->exists()) {
                return $this->gagal('Siswa tidak aktif sehingga tidak memiliki hak pilih.');
            }

            $user = User::updateOrCreate(
                ['datacenter_siswa_id' => $siswa->id],
                [
                    'nis' => $siswa->nis ?: $siswa->nisn,
                    'name' => $siswa->nama_siswa,
                    'kelas' => $siswa->kelasSekarang(),
                    'is_admin' => false,
                ],
            );
        } elseif ($guru) {
            if (! Guru::whereKey($guru->id)->bolehMemilih()->exists()) {
                return $this->gagal('Guru tidak aktif sehingga tidak memiliki hak pilih.');
            }

            $user = User::updateOrCreate(
                ['datacenter_guru_id' => $guru->id],
                [
                    'nip' => $guru->nip,
                    'name' => $guru->nama_ptk,
                    'jabatan' => $guru->jabatan,
                    'is_admin' => false,
                ],
            );
        } else {
            return $this->gagal('NIS / NIP tidak terdaftar.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('polls.index');
    }

    private function gagal(string $pesan): RedirectResponse
    {
        return back()->withInput()->withErrors(['nis' => $pesan]);
    }
}
