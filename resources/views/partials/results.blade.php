{{-- Daftar hasil suara: $results (koleksi kandidat dengan votes_count & percentage), $totalVotes --}}
<div class="space-y-4">
    @forelse ($results as $index => $candidate)
        @php($leader = $index === 0 && $candidate->votes_count > 0)
        <div>
            <div class="mb-1.5 flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    @include('partials.avatar', ['candidate' => $candidate, 'size' => 'h-9 w-9 text-sm'])
                    <div class="min-w-0">
                        <p class="truncate font-semibold">
                            <span class="text-slate-400">#{{ $candidate->number }}</span> {{ $candidate->name }}
                            @if ($leader)
                                <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-700">Teratas</span>
                            @endif
                        </p>
                    </div>
                </div>
                <p class="shrink-0 text-sm"><span class="font-bold">{{ $candidate->percentage }}%</span> <span class="text-slate-500">({{ number_format($candidate->votes_count) }} suara)</span></p>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                <div @class(['h-full rounded-full transition-all', 'bg-indigo-600' => $leader, 'bg-indigo-300' => ! $leader]) style="width: {{ $candidate->percentage }}%"></div>
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500">Belum ada kandidat.</p>
    @endforelse

    <p class="pt-2 text-sm text-slate-500">Total suara masuk: <span class="font-semibold text-slate-700">{{ number_format($totalVotes) }}</span></p>
</div>
