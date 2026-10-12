@extends('layouts.public')

@section('title', 'Login Pemilih')

@section('content')
    <div class="mx-auto max-w-md rounded-3xl bg-white p-8 shadow-xl shadow-slate-300/40">
        <h1 class="mb-2 text-2xl font-bold">🔐 Login Pemilih</h1>
        <p class="mb-6 text-sm text-slate-500">Siswa masukkan NIS, guru masukkan NIP untuk melanjutkan.</p>

        <form method="POST" action="{{ route('voter.login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="nis" class="mb-2 block font-bold">NIS / NIP</label>
                <input id="nis" name="nis" value="{{ old('nis') }}" inputmode="numeric" autocomplete="off" autofocus required
                       placeholder="Contoh: 88001 (siswa) atau NIP guru"
                       @class([
                           'block w-full rounded-xl border bg-white px-4 py-3 text-lg outline-none transition focus:ring-4',
                           'border-slate-300 focus:border-sky-600 focus:ring-sky-100' => ! $errors->has('nis'),
                           'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('nis'),
                       ])>
                @error('nis') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full cursor-pointer rounded-xl bg-[#0384c7] py-3 font-bold tracking-wide text-white uppercase shadow transition hover:bg-[#036ba3]">Lanjutkan</button>
            <a href="{{ route('home') }}" class="block w-full rounded-xl bg-slate-100 py-3 text-center font-bold tracking-wide uppercase transition hover:bg-slate-200">Kembali</a>
        </form>
    </div>
@endsection
