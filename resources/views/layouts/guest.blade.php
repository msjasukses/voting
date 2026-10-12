<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-sky-50 px-4 py-10 text-slate-800 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 flex flex-col items-center gap-2 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><path d="M5 7c0-1.1.9-2 2-2h10a2 2 0 0 1 2 2v12H5V7Z"/><path d="M22 19H2"/></svg>
            </div>
            <h1 class="text-2xl font-bold">{{ config('app.name') }}</h1>
            <p class="text-sm text-slate-500">Satu orang, satu suara.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @include('partials.flash')
            @yield('content')
        </div>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('home') }}" class="text-slate-500 hover:text-indigo-600">&larr; Kembali ke beranda</a>
        </p>
    </div>
</body>
</html>
