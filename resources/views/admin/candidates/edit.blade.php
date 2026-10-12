@extends('layouts.app')

@section('title', 'Edit Kandidat')

@section('content')
    <a href="{{ route('admin.polls.show', $poll) }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">&larr; Kembali ke {{ $poll->title }}</a>

    <div class="card mx-auto max-w-xl p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold">Edit Kandidat</h1>

        <form method="POST" action="{{ route('admin.polls.candidates.update', [$poll, $candidate]) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-4 gap-3">
                <div>
                    <label for="number" class="label">No.</label>
                    <input id="number" type="number" min="1" name="number" value="{{ old('number', $candidate->number) }}" class="input">
                </div>
                <div class="col-span-3">
                    <label for="name" class="label">Nama kandidat</label>
                    <input id="name" name="name" value="{{ old('name', $candidate->name) }}" class="input" required>
                </div>
            </div>
            @error('number') <p class="error">{{ $message }}</p> @enderror
            @error('name') <p class="error">{{ $message }}</p> @enderror

            <div>
                <label for="description" class="label">Visi & misi / keterangan</label>
                <textarea id="description" name="description" rows="5" class="input">{{ old('description', $candidate->description) }}</textarea>
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Foto</label>
                <div class="flex items-center gap-4">
                    @include('partials.avatar', ['size' => 'h-16 w-16 text-xl'])
                    <div class="flex-1 space-y-2">
                        <input type="file" name="photo" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                        @if ($candidate->photo)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300"> Hapus foto
                            </label>
                        @endif
                    </div>
                </div>
                @error('photo') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.polls.show', $poll) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
