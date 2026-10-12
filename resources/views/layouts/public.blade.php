<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') — @endif{{ config('voting.title') }} · {{ config('voting.school_name') }}</title>
    <link rel="icon" href="{{ asset(config('voting.logo')) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#eef3f8] text-slate-800 antialiased">
    <header class="bg-gradient-to-r from-[#0b5c8e] to-[#0ea5e9] text-white shadow-md">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-5">
            <a href="{{ route('home') }}" class="flex items-center gap-4">
                <img src="{{ asset(config('voting.logo')) }}" alt="Logo {{ config('voting.school_name') }}" class="h-16 w-auto drop-shadow">
                <div>
                    <p class="text-lg font-bold tracking-wide uppercase sm:text-xl">{{ config('voting.school_name') }}</p>
                    <p class="text-sm text-sky-100">{{ config('voting.header_subtitle') }} {{ config('voting.school_year') }}</p>
                </div>
            </a>

            <div class="rounded-xl border border-white/30 bg-white/15 px-4 py-2 text-center backdrop-blur-sm">
                <p id="clock-time" class="text-lg font-bold tracking-wider tabular-nums">{{ now()->format('H:i:s') }} WIB</p>
                <p id="clock-date" class="text-xs text-sky-50">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-9">
        @yield('content')
    </main>

    <footer class="py-6 text-center">
        <p class="text-sm font-bold text-slate-500 uppercase">{{ config('voting.school_name') }}</p>
        <p class="text-sm text-slate-500">{{ config('voting.header_subtitle') }} {{ config('voting.school_year') }}</p>
    </footer>

    <script>
        (function () {
            const timeEl = document.getElementById('clock-time');
            const dateEl = document.getElementById('clock-date');
            const timeFmt = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            const dateFmt = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });

            function tick() {
                const now = new Date();
                timeEl.textContent = timeFmt.format(now).replaceAll('.', ':') + ' WIB';
                dateEl.textContent = dateFmt.format(now);
            }

            tick();
            setInterval(tick, 1000);
        })();
    </script>
</body>
</html>
