@extends('layouts.public')

@section('content')
    <section class="mb-6 rounded-3xl bg-gradient-to-br from-[#065a8c] to-[#0384c7] px-6 py-14 text-center text-white shadow-xl shadow-sky-900/15">
        <h1 class="mb-5 text-3xl font-extrabold tracking-wide uppercase sm:text-4xl">{{ config('voting.title') }}</h1>
        <div class="mb-8 space-y-1.5 text-lg text-sky-50">
            <p>{{ config('voting.description') }}</p>
            <p>{{ config('voting.school_name') }}</p>
            <p>Tahun Pelajaran {{ config('voting.school_year') }}</p>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ auth()->check() ? route('polls.index') : route('voter.login') }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold tracking-wide text-[#065a8c] uppercase shadow transition hover:-translate-y-0.5 hover:shadow-lg">Mulai Voting</a>
            <a href="{{ route('admin.polls.index') }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold tracking-wide text-[#065a8c] uppercase shadow transition hover:-translate-y-0.5 hover:shadow-lg">Admin</a>
        </div>

        @auth
            <p class="mt-6 text-sm text-sky-100">Masuk sebagai <strong>{{ auth()->user()->name }}</strong></p>
        @endauth
    </section>

    <section class="rounded-2xl bg-white px-6 py-6 text-center shadow-sm">
        <h2 class="mb-1 text-lg font-bold">🗳️ {{ config('voting.motto') }}</h2>
        <p class="text-sm text-slate-500">{{ config('voting.motto_caption') }}</p>
    </section>
@endsection
