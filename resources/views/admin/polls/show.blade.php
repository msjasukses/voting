@extends('layouts.app')

@section('title', $poll->title)

@section('content')
    <a href="{{ route('admin.polls.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">&larr; Kembali ke Kelola Voting</a>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="mb-2">@include('partials.status-badge')</div>
            <h1 class="text-2xl font-bold">{{ $poll->title }}</h1>
            <p class="text-sm text-slate-500">
                {{ $poll->starts_at?->translatedFormat('d M Y H:i') ?? 'Tanpa waktu mulai' }} — {{ $poll->ends_at?->translatedFormat('d M Y H:i') ?? 'tanpa waktu selesai' }}
                · Hasil {{ $poll->show_results ? 'ditampilkan' : 'disembunyikan' }} untuk pemilih
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('polls.show', $poll) }}" class="btn-secondary">Lihat sebagai pemilih</a>
            <a href="{{ route('admin.polls.edit', $poll) }}" class="btn-secondary">Edit</a>
            <form method="POST" action="{{ route('admin.polls.reset', $poll) }}" onsubmit="return confirm('Hapus SEMUA suara pada voting ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <button class="btn-secondary text-amber-700">Reset Suara</button>
            </form>
            <form method="POST" action="{{ route('admin.polls.destroy', $poll) }}" onsubmit="return confirm('Hapus voting ini beserta semua kandidat dan suaranya?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Hapus</button>
            </form>
        </div>
    </div>

    @php($turnout = $eligibleVoters > 0 ? round($totalVotes / $eligibleVoters * 100, 1) : 0)
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-slate-500">Kandidat</p><p class="mt-1 text-3xl font-bold">{{ $poll->candidates->count() }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Suara masuk</p><p class="mt-1 text-3xl font-bold">{{ number_format($totalVotes) }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Pemilih terdaftar</p><p class="mt-1 text-3xl font-bold">{{ number_format($eligibleVoters) }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Partisipasi</p><p class="mt-1 text-3xl font-bold">{{ min($turnout, 100) }}%</p></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            <div class="card p-6">
                <h2 class="mb-5 text-lg font-semibold">Hasil Perolehan Suara</h2>
                @include('partials.results')
            </div>

            <div class="card overflow-hidden">
                <h2 class="border-b border-slate-200 px-6 py-4 text-lg font-semibold">Riwayat Suara</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs tracking-wide text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Pemilih</th>
                                <th class="px-6 py-3">Pilihan</th>
                                <th class="px-6 py-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($voters as $vote)
                                <tr>
                                    <td class="px-6 py-3">
                                        <p class="font-medium">{{ $vote->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $vote->user->identitas() }}</p>
                                    </td>
                                    <td class="px-6 py-3">#{{ $vote->candidate->number }} {{ $vote->candidate->name }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-slate-500">{{ $vote->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-8 text-center text-slate-500">Belum ada suara masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($voters->hasPages())
                    <div class="border-t border-slate-200 px-6 py-3">{{ $voters->links() }}</div>
                @endif
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <h2 class="mb-4 text-lg font-semibold">Kandidat</h2>
                <ul class="divide-y divide-slate-100">
                    @forelse ($poll->candidates as $candidate)
                        <li class="flex items-center gap-3 py-3">
                            @include('partials.avatar', ['candidate' => $candidate, 'size' => 'h-10 w-10 text-sm'])
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium"><span class="text-slate-400">#{{ $candidate->number }}</span> {{ $candidate->name }}</p>
                                @if ($candidate->description)
                                    <p class="truncate text-xs text-slate-500">{{ $candidate->description }}</p>
                                @endif
                            </div>
                            <a href="{{ route('admin.polls.candidates.edit', [$poll, $candidate]) }}" class="text-sm font-medium text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.polls.candidates.destroy', [$poll, $candidate]) }}" onsubmit="return confirm('Hapus kandidat {{ addslashes($candidate->name) }}? Suara untuk kandidat ini juga akan terhapus.')">
                                @csrf @method('DELETE')
                                <button class="cursor-pointer text-sm font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-slate-500">Belum ada kandidat. Tambahkan minimal dua kandidat.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-lg font-semibold">Tambah Kandidat</h2>
                <form method="POST" action="{{ route('admin.polls.candidates.store', $poll) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label for="number" class="label">No.</label>
                            <input id="number" type="number" min="1" name="number" value="{{ old('number') }}" class="input" placeholder="{{ ($poll->candidates->max('number') ?? 0) + 1 }}">
                        </div>
                        <div class="col-span-3">
                            <label for="name" class="label">Nama kandidat</label>
                            <input id="name" name="name" value="{{ old('name') }}" class="input" required>
                        </div>
                    </div>
                    @error('number') <p class="error">{{ $message }}</p> @enderror
                    @error('name') <p class="error">{{ $message }}</p> @enderror

                    <div>
                        <label for="description" class="label">Visi & misi / keterangan</label>
                        <textarea id="description" name="description" rows="3" class="input">{{ old('description') }}</textarea>
                        @error('description') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="photo" class="label">Foto <span class="font-normal text-slate-400">(opsional, maks. 2 MB)</span></label>
                        <input id="photo" type="file" name="photo" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('photo') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">Tambah Kandidat</button>
                </form>
            </div>
        </div>
    </div>
@endsection
