@extends('layouts.app')

@section('title', $poll->title)

@section('content')
    <a href="{{ route('polls.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">&larr; Kembali ke daftar voting</a>

    <div class="card mb-6 p-6">
        <div class="mb-3 flex flex-wrap items-center gap-3">
            @include('partials.status-badge')
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.polls.show', $poll) }}" class="text-sm font-medium text-indigo-600 hover:underline">Kelola voting ini &rarr;</a>
            @endif
        </div>
        <h1 class="mb-2 text-2xl font-bold">{{ $poll->title }}</h1>
        @if ($poll->description)
            <p class="whitespace-pre-line text-slate-600">{{ $poll->description }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-500">
            @if ($poll->starts_at)
                <span>Mulai: <strong class="text-slate-700">{{ $poll->starts_at->translatedFormat('d F Y, H:i') }}</strong></span>
            @endif
            @if ($poll->ends_at)
                <span>Selesai: <strong class="text-slate-700">{{ $poll->ends_at->translatedFormat('d F Y, H:i') }}</strong></span>
            @endif
        </div>
    </div>

    @if ($myVote)
        <div class="mb-6 flex items-center gap-4 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
            @include('partials.avatar', ['candidate' => $myVote->candidate, 'size' => 'h-12 w-12 text-base'])
            <div>
                <p class="text-sm text-indigo-700">Anda telah memberikan suara untuk</p>
                <p class="text-lg font-semibold text-indigo-900">#{{ $myVote->candidate->number }} {{ $myVote->candidate->name }}</p>
                <p class="text-xs text-indigo-600">pada {{ $myVote->created_at->translatedFormat('d F Y, H:i') }}</p>
            </div>
        </div>
    @endif

    @if (! $myVote && $poll->isOpen())
        {{-- Formulir pemungutan suara --}}
        <form method="POST" action="{{ route('polls.vote', $poll) }}" id="vote-form">
            @csrf
            <h2 class="mb-4 text-lg font-semibold">Pilih kandidat Anda</h2>
            @error('candidate_id') <p class="error mb-4">{{ $message }}</p> @enderror

            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($poll->candidates as $candidate)
                    <label class="card relative flex cursor-pointer flex-col items-center p-6 text-center transition hover:border-indigo-300 has-[:checked]:border-indigo-600 has-[:checked]:ring-2 has-[:checked]:ring-indigo-200">
                        <input type="radio" name="candidate_id" value="{{ $candidate->id }}" data-name="{{ $candidate->name }}" class="peer sr-only" required>
                        <span class="absolute top-3 left-3 rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-bold text-slate-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $candidate->number }}</span>
                        <span class="absolute top-3 right-3 hidden h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white peer-checked:flex">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                        </span>
                        @include('partials.avatar', ['candidate' => $candidate, 'size' => 'h-24 w-24 text-3xl'])
                        <span class="mt-4 text-lg font-semibold">{{ $candidate->name }}</span>
                        @if ($candidate->description)
                            <span class="mt-2 text-sm whitespace-pre-line text-slate-500">{{ $candidate->description }}</span>
                        @endif
                    </label>
                @empty
                    <div class="card col-span-full p-8 text-center text-slate-500">Belum ada kandidat pada voting ini.</div>
                @endforelse
            </div>

            @if ($poll->candidates->isNotEmpty())
                <div class="flex flex-col items-center gap-2">
                    <button type="submit" class="btn-primary px-8 py-3 text-base">Kirim Suara</button>
                    <p class="text-xs text-slate-500">Suara yang sudah dikirim tidak dapat diubah.</p>
                </div>
            @endif
        </form>

        <script>
            document.getElementById('vote-form').addEventListener('submit', function (e) {
                const selected = this.querySelector('input[name="candidate_id"]:checked');
                if (selected && ! confirm('Yakin memilih "' + selected.dataset.name + '"? Suara tidak dapat diubah setelah dikirim.')) {
                    e.preventDefault();
                    return;
                }
                this.querySelector('button[type="submit"]').disabled = true;
            });
        </script>
    @elseif (! $myVote)
        <div class="card mb-6 p-6 text-center text-slate-600">
            @switch($poll->status())
                @case('upcoming') Voting belum dimulai. Silakan kembali pada {{ $poll->starts_at->translatedFormat('d F Y, H:i') }}. @break
                @case('closed') Voting telah berakhir. @break
                @default Voting ini sedang tidak aktif.
            @endswitch
        </div>

        @unless ($canSeeResults)
            <h2 class="mb-4 text-lg font-semibold">Kandidat</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($poll->candidates as $candidate)
                    <div class="card flex items-center gap-4 p-4">
                        @include('partials.avatar', ['candidate' => $candidate, 'size' => 'h-12 w-12 text-base'])
                        <p class="font-semibold"><span class="text-slate-400">#{{ $candidate->number }}</span> {{ $candidate->name }}</p>
                    </div>
                @endforeach
            </div>
        @endunless
    @endif

    @if ($canSeeResults)
        <div class="card p-6">
            <h2 class="mb-5 text-lg font-semibold">{{ $poll->status() === 'closed' ? 'Hasil Akhir' : 'Hasil Sementara' }}</h2>
            @include('partials.results')
        </div>
    @elseif ($myVote)
        <div class="card p-6 text-center text-slate-500">Hasil voting disembunyikan oleh panitia.</div>
    @endif
@endsection
