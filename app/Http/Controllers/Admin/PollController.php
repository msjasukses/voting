<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Datacenter\Siswa;
use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Throwable;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::withCount(['candidates', 'votes'])->latest()->get();

        $stats = [
            'polls' => $polls->count(),
            'open' => $polls->filter->isOpen()->count(),
            'voters' => $this->jumlahPemilih(),
            'votes' => Vote::count(),
        ];

        return view('admin.polls.index', compact('polls', 'stats'));
    }

    public function create()
    {
        return view('admin.polls.form', ['poll' => new Poll(['is_active' => true, 'show_results' => true])]);
    }

    public function store(Request $request)
    {
        $poll = Poll::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()
            ->route('admin.polls.show', $poll)
            ->with('success', 'Voting berhasil dibuat. Sekarang tambahkan kandidat.');
    }

    public function show(Poll $poll)
    {
        return view('admin.polls.show', [
            'poll' => $poll->load('candidates'),
            'results' => $poll->results(),
            'totalVotes' => $poll->votes()->count(),
            'eligibleVoters' => $this->jumlahPemilih(),
            'voters' => $poll->votes()->with(['user', 'candidate'])->latest()->paginate(20),
        ]);
    }

    public function edit(Poll $poll)
    {
        return view('admin.polls.form', compact('poll'));
    }

    public function update(Request $request, Poll $poll)
    {
        $poll->update($this->validated($request));

        return redirect()->route('admin.polls.show', $poll)->with('success', 'Voting berhasil diperbarui.');
    }

    public function destroy(Poll $poll)
    {
        $poll->delete();

        return redirect()->route('admin.polls.index')->with('success', 'Voting berhasil dihapus.');
    }

    public function resetVotes(Poll $poll)
    {
        $poll->votes()->delete();

        return back()->with('success', 'Semua suara pada voting ini telah direset.');
    }

    /**
     * Jumlah siswa yang berhak memilih menurut Data Center. Jika Data Center tidak bisa
     * dihubungi, pakai jumlah siswa yang pernah login ke aplikasi ini.
     */
    private function jumlahPemilih(): int
    {
        try {
            return Siswa::bolehMemilih()->count();
        } catch (Throwable) {
            return User::whereNotNull('nis')->count();
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ], [
            'ends_at.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['show_results'] = $request->boolean('show_results');

        return $data;
    }
}
