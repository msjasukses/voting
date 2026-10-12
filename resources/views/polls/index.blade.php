@extends('layouts.app')

@section('title', 'Daftar Voting')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold">Daftar Voting</h1>
        <p class="text-slate-500">Pilih voting di bawah ini untuk melihat kandidat dan memberikan suara Anda.</p>
    </div>

    @if ($polls->isEmpty())
        <div class="card p-12 text-center text-slate-500">
            Belum ada voting yang tersedia saat ini.
            @if (auth()->user()->is_admin)
                <div class="mt-4"><a href="{{ route('admin.polls.create') }}" class="btn-primary">Buat voting pertama</a></div>
            @endif
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($polls as $poll)
                @php($voted = $votedPollIds->contains($poll->id))
                <a href="{{ route('polls.show', $poll) }}" class="card group flex flex-col p-5 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        @include('partials.status-badge')
                        @if ($voted)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                                Sudah memilih
                            </span>
                        @endif
                    </div>

                    <h2 class="mb-1 text-lg font-semibold group-hover:text-indigo-600">{{ $poll->title }}</h2>
                    <p class="mb-4 line-clamp-2 flex-1 text-sm text-slate-500">{{ $poll->description ?: 'Tanpa deskripsi.' }}</p>

                    <div class="flex flex-wrap gap-x-4 gap-y-1 border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <span>{{ $poll->candidates_count }} kandidat</span>
                        <span>{{ number_format($poll->votes_count) }} suara</span>
                        @if ($poll->ends_at)
                            <span>Berakhir {{ $poll->ends_at->translatedFormat('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
