<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function store(Request $request, Poll $poll)
    {
        $data = $this->validated($request);
        $data['number'] ??= ($poll->candidates()->max('number') ?? 0) + 1;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $poll->candidates()->create($data);

        return redirect()->route('admin.polls.show', $poll)->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function edit(Poll $poll, Candidate $candidate)
    {
        return view('admin.candidates.edit', compact('poll', 'candidate'));
    }

    public function update(Request $request, Poll $poll, Candidate $candidate)
    {
        $data = $this->validated($request);
        $data['number'] ??= $candidate->number;

        if ($request->hasFile('photo')) {
            if ($candidate->photo) {
                Storage::disk('public')->delete($candidate->photo);
            }
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        } elseif ($request->boolean('remove_photo') && $candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
            $data['photo'] = null;
        }

        $candidate->update($data);

        return redirect()->route('admin.polls.show', $poll)->with('success', 'Kandidat berhasil diperbarui.');
    }

    public function destroy(Poll $poll, Candidate $candidate)
    {
        if ($candidate->photo) {
            Storage::disk('public')->delete($candidate->photo);
        }

        $candidate->delete();

        return redirect()->route('admin.polls.show', $poll)->with('success', 'Kandidat berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
