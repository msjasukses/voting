<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Vote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index(Request $request)
    {
        $votedPollIds = $request->user()->votes()->pluck('poll_id');

        $polls = Poll::visible()
            ->withCount(['candidates', 'votes'])
            ->latest()
            ->get();

        return view('polls.index', compact('polls', 'votedPollIds'));
    }

    public function show(Request $request, Poll $poll)
    {
        abort_unless($poll->is_active || $request->user()->is_admin, 404);

        $user = $request->user();
        $myVote = $user->votes()->where('poll_id', $poll->id)->with('candidate')->first();
        $canSeeResults = $poll->resultsVisibleTo($user);

        return view('polls.show', [
            'poll' => $poll->load('candidates'),
            'myVote' => $myVote,
            'canSeeResults' => $canSeeResults,
            'results' => $canSeeResults ? $poll->results() : collect(),
            'totalVotes' => $poll->votes()->count(),
        ]);
    }

    public function vote(Request $request, Poll $poll)
    {
        $user = $request->user();

        if (! $poll->isOpen()) {
            return back()->with('error', 'Voting ini sedang tidak dibuka.');
        }

        if ($user->hasVotedIn($poll)) {
            return back()->with('error', 'Anda sudah memberikan suara pada voting ini.');
        }

        $data = $request->validate([
            'candidate_id' => ['required', 'integer'],
        ], [
            'candidate_id.required' => 'Silakan pilih salah satu kandidat.',
        ]);

        $candidate = $poll->candidates()->whereKey($data['candidate_id'])->first();

        if (! $candidate) {
            return back()->with('error', 'Kandidat tidak valid untuk voting ini.');
        }

        try {
            Vote::create([
                'poll_id' => $poll->id,
                'candidate_id' => $candidate->id,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Klik ganda / request bersamaan: indeks unik di database menolak suara kedua.
            return back()->with('error', 'Anda sudah memberikan suara pada voting ini.');
        }

        return redirect()
            ->route('polls.show', $poll)
            ->with('success', 'Terima kasih! Suara Anda untuk "'.$candidate->name.'" telah tercatat.');
    }
}
