<?php

namespace App\Http\Controllers;

use App\Models\Datacenter\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Login pemilih dengan NIS / Nomor Peserta. Data siswa diambil dari aplikasi Data Center,
 * lalu disalin ke tabel users lokal agar suara bisa dicatat.
 */
class VoterLoginController extends Controller
{
    public function show()
    {
        return view('auth.voter-login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:20'],
        ], [
            'nis.required' => 'NIS / Nomor Peserta wajib diisi.',
        ]);

        $nomor = trim($data['nis']);

        try {
            $siswa = Siswa::findByNomor($nomor);
        } catch (Throwable $e) {
            Log::error('Gagal terhubung ke database Data Center: '.$e->getMessage());

            return back()->withInput()->withErrors(['nis' => 'Server Data Center tidak dapat dihubungi. Silakan hubungi panitia.']);
        }

        if (! $siswa) {
            return back()->withInput()->withErrors(['nis' => 'NIS / Nomor Peserta tidak terdaftar.']);
        }

        if (! Siswa::whereKey($siswa->id)->bolehMemilih()->exists()) {
            return back()->withInput()->withErrors(['nis' => 'Siswa tidak aktif sehingga tidak memiliki hak pilih.']);
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

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('polls.index');
    }
}
