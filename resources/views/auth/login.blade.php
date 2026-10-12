@extends('layouts.guest')

@section('title', 'Login Admin')

@section('content')
    <h2 class="mb-6 text-xl font-semibold">Login Admin / Panitia</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="email">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600">
            Ingat saya
        </label>

        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Siswa / pemilih?
        <a href="{{ route('voter.login') }}" class="font-semibold text-indigo-600 hover:underline">Login dengan NIS</a>
    </p>
@endsection
