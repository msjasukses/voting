@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Pengguna</h1>
            <p class="text-slate-500">Admin, serta siswa &amp; guru dari Data Center yang sudah pernah login untuk memilih.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, NIP, atau email…" class="input w-64">
            <button class="btn-secondary">Cari</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Peran</th>
                        <th class="px-5 py-3 text-center">Suara diberikan</th>
                        <th class="px-5 py-3">Terdaftar</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->identitas() }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @if ($user->is_admin)
                                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">Admin</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Pemilih</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">{{ $user->votes_count }}</td>
                            <td class="px-5 py-3 whitespace-nowrap text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                @unless ($user->is(auth()->user()))
                                    <div class="flex justify-end gap-3">
                                        @unless ($user->isPemilihDataCenter())
                                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                                @csrf @method('PATCH')
                                                <button class="cursor-pointer font-medium text-indigo-600 hover:underline">{{ $user->is_admin ? 'Jadikan pemilih' : 'Jadikan admin' }}</button>
                                            </form>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini beserta suaranya?')">
                                            @csrf @method('DELETE')
                                            <button class="cursor-pointer font-medium text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-right text-xs text-slate-400">Akun Anda</p>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Tidak ada pengguna ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
