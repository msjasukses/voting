@extends('layouts.app')

@section('title', 'Kelola Voting')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Kelola Voting</h1>
            <p class="text-slate-500">Buat voting, atur kandidat, dan pantau hasilnya.</p>
        </div>
        <a href="{{ route('admin.polls.create') }}" class="btn-primary">+ Buat Voting</a>
    </div>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Total Voting', $stats['polls']],
            ['Sedang Berlangsung', $stats['open']],
            ['Pemilih (Data Center)', $stats['voters']],
            ['Total Suara Masuk', $stats['votes']],
        ] as [$label, $value])
            <div class="card p-5">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-3xl font-bold">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-center">Kandidat</th>
                        <th class="px-5 py-3 text-center">Suara</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($polls as $poll)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.polls.show', $poll) }}" class="font-semibold hover:text-indigo-600">{{ $poll->title }}</a>
                            </td>
                            <td class="px-5 py-3">@include('partials.status-badge')</td>
                            <td class="px-5 py-3 whitespace-nowrap text-slate-500">
                                {{ $poll->starts_at?->format('d/m/Y H:i') ?? '—' }} s/d {{ $poll->ends_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-center">{{ $poll->candidates_count }}</td>
                            <td class="px-5 py-3 text-center font-semibold">{{ number_format($poll->votes_count) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.polls.show', $poll) }}" class="font-medium text-indigo-600 hover:underline">Detail</a>
                                    <a href="{{ route('admin.polls.edit', $poll) }}" class="font-medium text-slate-600 hover:underline">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada voting. Klik "Buat Voting" untuk memulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
