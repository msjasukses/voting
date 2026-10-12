@php
    $classes = match ($poll->status()) {
        'open' => 'bg-emerald-100 text-emerald-700',
        'upcoming' => 'bg-amber-100 text-amber-700',
        'closed' => 'bg-slate-200 text-slate-700',
        'draft' => 'bg-rose-100 text-rose-700',
    };
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    @if ($poll->isOpen())
        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
    @endif
    {{ $poll->statusLabel() }}
</span>
