@extends('layouts.app')

@section('title', 'Pengaturan Tampilan')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Pengaturan Tampilan</h1>
            <p class="text-slate-500">Atur identitas sekolah dan isi halaman awal. Perubahan langsung berlaku untuk halaman awal, login pemilih, dan file PDF hasil.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="btn-secondary">Lihat halaman awal &nearr;</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-5">
        @csrf @method('PUT')

        <div class="flex flex-col gap-6 lg:col-span-3">
            <section class="card flex flex-col gap-5 p-6">
                <h2 class="text-lg font-semibold">Identitas Sekolah</h2>

                <div>
                    <label for="school_name" class="label">Nama sekolah</label>
                    <input id="school_name" name="school_name" value="{{ old('school_name', $settings['school_name']) }}" data-preview="school_name" class="input" required>
                    @error('school_name') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="school_year" class="label">Tahun pelajaran</label>
                        <input id="school_year" name="school_year" value="{{ old('school_year', $settings['school_year']) }}" data-preview="school_year" class="input" placeholder="2026/2027" required>
                        @error('school_year') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="city" class="label">Kota <span class="font-normal text-slate-400">(untuk tanda tangan PDF)</span></label>
                        <input id="city" name="city" value="{{ old('city', $settings['city']) }}" class="input" required>
                        @error('city') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <span class="label">Logo sekolah</span>
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#0b5c8e] to-[#0ea5e9] p-2">
                            <img src="{{ asset($settings['logo']) }}" alt="Logo saat ini" data-preview-logo class="max-h-full max-w-full object-contain">
                        </div>
                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-xs text-slate-500">PNG, JPG, atau WEBP, maks. 2 MB. Disarankan latar transparan (PNG).</p>
                            @unless ($logoBawaan)
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="reset_logo" value="1" class="rounded border-slate-300"> Kembalikan ke logo bawaan
                                </label>
                            @endunless
                        </div>
                    </div>
                    @error('logo') <p class="error">{{ $message }}</p> @enderror
                </div>
            </section>

            <section class="card flex flex-col gap-5 p-6">
                <h2 class="text-lg font-semibold">Halaman Awal</h2>

                <div>
                    <label for="header_subtitle" class="label">Keterangan di bawah nama sekolah <span class="font-normal text-slate-400">(header)</span></label>
                    <input id="header_subtitle" name="header_subtitle" value="{{ old('header_subtitle', $settings['header_subtitle']) }}" data-preview="header_subtitle" class="input" required>
                    <p class="mt-1 text-xs text-slate-500">Tahun pelajaran otomatis ditambahkan di belakangnya.</p>
                    @error('header_subtitle') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="title" class="label">Judul besar</label>
                    <input id="title" name="title" value="{{ old('title', $settings['title']) }}" data-preview="title" class="input" required>
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="label">Deskripsi di bawah judul</label>
                    <input id="description" name="description" value="{{ old('description', $settings['description']) }}" data-preview="description" class="input">
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="motto" class="label">Motto</label>
                    <input id="motto" name="motto" value="{{ old('motto', $settings['motto']) }}" data-preview="motto" class="input" required>
                    @error('motto') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="motto_caption" class="label">Keterangan motto</label>
                    <textarea id="motto_caption" name="motto_caption" rows="2" data-preview="motto_caption" class="input">{{ old('motto_caption', $settings['motto_caption']) }}</textarea>
                    @error('motto_caption') <p class="error">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary px-6">Simpan Pengaturan</button>
            </div>
        </div>

        {{-- Pratinjau halaman awal, ikut berubah saat mengetik --}}
        <aside class="lg:col-span-2">
            <div class="sticky top-6 flex flex-col gap-2">
                <p class="text-sm font-medium text-slate-500">Pratinjau halaman awal</p>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-[#eef3f8] shadow-sm">
                    <div class="flex items-center gap-3 bg-gradient-to-r from-[#0b5c8e] to-[#0ea5e9] px-4 py-3 text-white">
                        <img src="{{ asset($settings['logo']) }}" alt="" data-preview-logo class="h-10 w-10 object-contain">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold uppercase" data-field="school_name">{{ $settings['school_name'] }}</p>
                            <p class="truncate text-[11px] text-sky-100"><span data-field="header_subtitle">{{ $settings['header_subtitle'] }}</span> <span data-field="school_year">{{ $settings['school_year'] }}</span></p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 p-4">
                        <div class="rounded-xl bg-gradient-to-br from-[#065a8c] to-[#0384c7] px-4 py-6 text-center text-white">
                            <p class="text-lg font-extrabold uppercase" data-field="title">{{ $settings['title'] }}</p>
                            <div class="mt-2 text-xs text-sky-50">
                                <p data-field="description">{{ $settings['description'] }}</p>
                                <p data-field="school_name">{{ $settings['school_name'] }}</p>
                                <p>Tahun Pelajaran <span data-field="school_year">{{ $settings['school_year'] }}</span></p>
                            </div>
                            <div class="mt-4 flex justify-center gap-2">
                                <span class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-[#065a8c] uppercase">Mulai Voting</span>
                                <span class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-[#065a8c] uppercase">Admin</span>
                            </div>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 text-center">
                            <p class="text-sm font-bold">🗳️ <span data-field="motto">{{ $settings['motto'] }}</span></p>
                            <p class="text-[11px] text-slate-500" data-field="motto_caption">{{ $settings['motto_caption'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </form>

    <script>
        document.querySelectorAll('[data-preview]').forEach(function (input) {
            input.addEventListener('input', function () {
                document.querySelectorAll('[data-field="' + input.dataset.preview + '"]').forEach(function (el) {
                    el.textContent = input.value;
                });
            });
        });

        document.getElementById('logo').addEventListener('change', function () {
            const file = this.files[0];
            if (! file) return;
            const url = URL.createObjectURL(file);
            document.querySelectorAll('[data-preview-logo]').forEach(function (img) { img.src = url; });
        });
    </script>
@endsection
