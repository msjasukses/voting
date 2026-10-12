@extends('layouts.app')

@section('title', $poll->exists ? 'Edit Voting' : 'Buat Voting')

@section('content')
    <a href="{{ $poll->exists ? route('admin.polls.show', $poll) : route('admin.polls.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">&larr; Kembali</a>

    <div class="card mx-auto max-w-2xl p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold">{{ $poll->exists ? 'Edit Voting' : 'Buat Voting Baru' }}</h1>

        <form method="POST" action="{{ $poll->exists ? route('admin.polls.update', $poll) : route('admin.polls.store') }}" class="space-y-5">
            @csrf
            @if ($poll->exists) @method('PUT') @endif

            <div>
                <label for="title" class="label">Judul voting</label>
                <input id="title" name="title" value="{{ old('title', $poll->title) }}" class="input" placeholder="Contoh: Pemilihan Ketua OSIS 2026" required>
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="label">Deskripsi <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="description" name="description" rows="4" class="input">{{ old('description', $poll->description) }}</textarea>
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="label">Waktu mulai <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $poll->starts_at?->format('Y-m-d\TH:i')) }}" class="input">
                    @error('starts_at') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ends_at" class="label">Waktu selesai <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $poll->ends_at?->format('Y-m-d\TH:i')) }}" class="input">
                    @error('ends_at') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="-mt-3 text-xs text-slate-500">Kosongkan waktu mulai/selesai jika voting dibuka/ditutup secara manual.</p>

            <div class="space-y-3 rounded-xl bg-slate-50 p-4">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $poll->is_active)) class="mt-0.5 rounded border-slate-300 text-indigo-600">
                    <span>
                        <span class="block text-sm font-medium">Aktifkan voting</span>
                        <span class="block text-xs text-slate-500">Jika dinonaktifkan, voting disembunyikan dari pemilih.</span>
                    </span>
                </label>
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="show_results" value="1" @checked(old('show_results', $poll->show_results)) class="mt-0.5 rounded border-slate-300 text-indigo-600">
                    <span>
                        <span class="block text-sm font-medium">Tampilkan hasil ke pemilih</span>
                        <span class="block text-xs text-slate-500">Hasil terlihat oleh pemilih setelah mereka memilih atau setelah voting selesai.</span>
                    </span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ $poll->exists ? route('admin.polls.show', $poll) : route('admin.polls.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">{{ $poll->exists ? 'Simpan Perubahan' : 'Buat Voting' }}</button>
            </div>
        </form>
    </div>
@endsection
