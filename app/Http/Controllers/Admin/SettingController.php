<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Halaman Pengaturan: mengubah identitas sekolah dan teks halaman awal tanpa mengedit .env.
 */
class SettingController extends Controller
{
    private const LOGO_PREFIX = 'storage/branding/';

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => config('voting'),
            'logoBawaan' => config('voting.logo') === config('voting.default.logo'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'school_year' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'header_subtitle' => ['required', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'motto' => ['required', 'string', 'max:150'],
            'motto_caption' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'logo.mimes' => 'Logo harus berupa gambar PNG, JPG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
        ]);

        $logoLama = config('voting.logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = self::LOGO_PREFIX.Str::after($request->file('logo')->store('branding', 'public'), 'branding/');
            $this->hapusLogoUpload($logoLama);
        } else {
            unset($data['logo']);
        }

        Setting::simpan($data);

        if (! $request->hasFile('logo') && $request->boolean('reset_logo')) {
            Setting::hapus('logo');
            $this->hapusLogoUpload($logoLama);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan tampilan berhasil disimpan.');
    }

    /**
     * Hapus file logo hasil upload sebelumnya (logo bawaan di public/images tidak disentuh).
     */
    private function hapusLogoUpload(?string $logo): void
    {
        if ($logo && str_starts_with($logo, self::LOGO_PREFIX)) {
            Storage::disk('public')->delete('branding/'.Str::after($logo, self::LOGO_PREFIX));
        }
    }
}
