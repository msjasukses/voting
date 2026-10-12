@php($size = $size ?? 'h-16 w-16 text-xl')
@if ($candidate->photoUrl())
    <img src="{{ $candidate->photoUrl() }}" alt="{{ $candidate->name }}" class="{{ $size }} shrink-0 rounded-full object-cover ring-2 ring-white">
@else
    <div class="{{ $size }} flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 font-bold text-white ring-2 ring-white">
        {{ $candidate->initials() }}
    </div>
@endif
