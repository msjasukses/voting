<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <nav class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('polls.index') }}" class="flex items-center gap-2 text-lg font-bold text-indigo-600">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><path d="M5 7c0-1.1.9-2 2-2h10a2 2 0 0 1 2 2v12H5V7Z"/><path d="M22 19H2"/></svg>
                {{ config('app.name') }}
            </a>

            <div class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('polls.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-100', 'bg-slate-100 text-indigo-600' => request()->routeIs('polls.*')])>Voting</a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.polls.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-100', 'bg-slate-100 text-indigo-600' => request()->routeIs('admin.polls.*')])>Kelola Voting</a>
                    <a href="{{ route('admin.users.index') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-100', 'bg-slate-100 text-indigo-600' => request()->routeIs('admin.users.*')])>Pengguna</a>
                    <a href="{{ route('admin.settings.edit') }}" @class(['rounded-lg px-3 py-2 font-medium hover:bg-slate-100', 'bg-slate-100 text-indigo-600' => request()->routeIs('admin.settings.*')])>Pengaturan</a>
                @endif

                <span class="mx-2 hidden h-6 w-px bg-slate-200 sm:block"></span>
                <span class="px-2 text-slate-500">
                    {{ auth()->user()->name }}
                    @if (auth()->user()->is_admin)
                        <span class="ml-1 rounded bg-indigo-100 px-1.5 py-0.5 text-xs font-semibold text-indigo-700">Admin</span>
                    @endif
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg px-3 py-2 font-medium text-red-600 hover:bg-red-50">Keluar</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @include('partials.flash')
        @yield('content')
    </main>
</body>
</html>
