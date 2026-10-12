<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    private function openPoll(array $attributes = []): Poll
    {
        $poll = Poll::create($attributes + ['title' => 'Pemilihan Ketua', 'is_active' => true, 'show_results' => true]);
        $poll->candidates()->create(['number' => 1, 'name' => 'Kandidat A']);
        $poll->candidates()->create(['number' => 2, 'name' => 'Kandidat B']);

        return $poll;
    }

    public function test_landing_page_is_public(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(config('voting.title'))
            ->assertSee(route('voter.login'))
            ->assertSee(route('admin.polls.index'));
    }

    public function test_guests_are_redirected_to_the_right_login_page(): void
    {
        $this->get(route('polls.index'))->assertRedirect(route('voter.login'));
        $this->get(route('admin.polls.index'))->assertRedirect(route('login'));
    }

    public function test_voter_login_page_is_shown(): void
    {
        $this->get(route('voter.login'))
            ->assertOk()
            ->assertSee('Login Pemilih')
            ->assertSee('NIS / Nomor Peserta');
    }

    public function test_student_from_datacenter_can_login_with_nis(): void
    {
        $dc = DB::connection('datacenter');
        $tahunId = $dc->table('tahun_ajaran')->insertGetId(['nama_tahun_ajaran' => '2026/2027', 'is_aktif' => true]);
        $rombelId = $dc->table('rombongan_belajar')->insertGetId(['nama_rombel' => 'IX-A', 'tahun_ajaran_id' => $tahunId]);
        $siswaId = $dc->table('siswa')->insertGetId(['nisn' => '0012345678', 'nis' => '88001', 'nama_siswa' => 'Budi Santoso']);
        $dc->table('siswa_rombel')->insert(['siswa_id' => $siswaId, 'rombongan_belajar_id' => $rombelId, 'tahun_ajaran_id' => $tahunId]);

        $this->post(route('voter.login'), ['nis' => ' 88001 '])->assertRedirect(route('polls.index'));

        $user = User::firstWhere('datacenter_siswa_id', $siswaId);
        $this->assertAuthenticatedAs($user);
        $this->assertSame('88001', $user->nis);
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame('IX-A', $user->kelas);
        $this->assertFalse($user->is_admin);

        // Login kedua memakai akun lokal yang sama (tidak membuat duplikat).
        $this->post(route('logout'));
        $this->post(route('voter.login'), ['nis' => '0012345678'])->assertRedirect(route('polls.index'));
        $this->assertSame(1, User::where('datacenter_siswa_id', $siswaId)->count());

        $this->openPoll();
        $this->get(route('polls.index'))->assertOk()->assertSee('Pemilihan Ketua');
    }

    public function test_unknown_or_inactive_student_cannot_login(): void
    {
        DB::connection('datacenter')->table('siswa')->insert([
            ['nisn' => '1', 'nis' => '88002', 'nama_siswa' => 'Lulus', 'status_siswa' => 'Lulus', 'is_aktif' => true, 'account_status' => 'active'],
            ['nisn' => '2', 'nis' => '88003', 'nama_siswa' => 'Nonaktif', 'status_siswa' => 'Aktif', 'is_aktif' => false, 'account_status' => 'active'],
        ]);

        foreach (['99999', '88002', '88003'] as $nis) {
            $this->post(route('voter.login'), ['nis' => $nis])->assertSessionHasErrors('nis');
        }

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_voter_can_vote_once(): void
    {
        $user = User::factory()->create();
        $poll = $this->openPoll();
        $candidate = $poll->candidates->first();

        $this->actingAs($user)
            ->post(route('polls.vote', $poll), ['candidate_id' => $candidate->id])
            ->assertRedirect(route('polls.show', $poll));

        $this->assertDatabaseHas('votes', ['poll_id' => $poll->id, 'user_id' => $user->id, 'candidate_id' => $candidate->id]);

        $this->actingAs($user)
            ->post(route('polls.vote', $poll), ['candidate_id' => $poll->candidates->last()->id])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('votes', 1);

        $this->get(route('polls.show', $poll))->assertOk()->assertSee('Hasil Sementara')->assertSee('100%');
    }

    public function test_cannot_vote_for_candidate_of_another_poll(): void
    {
        $user = User::factory()->create();
        $poll = $this->openPoll();
        $other = $this->openPoll(['title' => 'Lain']);

        $this->actingAs($user)
            ->post(route('polls.vote', $poll), ['candidate_id' => $other->candidates->first()->id])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_cannot_vote_on_closed_or_upcoming_poll(): void
    {
        $user = User::factory()->create();
        $closed = $this->openPoll(['ends_at' => now()->subHour()]);
        $upcoming = $this->openPoll(['starts_at' => now()->addHour()]);

        foreach ([$closed, $upcoming] as $poll) {
            $this->actingAs($user)
                ->post(route('polls.vote', $poll), ['candidate_id' => $poll->candidates->first()->id])
                ->assertSessionHas('error');
        }

        $this->assertDatabaseCount('votes', 0);
    }

    public function test_results_hidden_before_voting(): void
    {
        $user = User::factory()->create();
        $poll = $this->openPoll();

        $this->actingAs($user)->get(route('polls.show', $poll))
            ->assertOk()
            ->assertSee('Kirim Suara')
            ->assertDontSee('Hasil Sementara');
    }

    public function test_non_admin_cannot_access_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.polls.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_poll_and_candidates(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.polls.store'), [
            'title' => 'Voting Baru',
            'is_active' => '1',
            'show_results' => '1',
        ])->assertRedirect();

        $poll = Poll::firstWhere('title', 'Voting Baru');

        $this->post(route('admin.polls.candidates.store', $poll), ['name' => 'Calon Satu'])->assertRedirect();
        $this->post(route('admin.polls.candidates.store', $poll), ['name' => 'Calon Dua'])->assertRedirect();

        $this->assertEquals([1, 2], $poll->candidates()->pluck('number')->all());

        $candidate = $poll->candidates()->first();
        $this->get(route('admin.polls.candidates.edit', [$poll, $candidate]))->assertOk();
        $this->get(route('admin.polls.show', $poll))->assertOk()->assertSee('Calon Dua');
    }

    public function test_candidate_routes_are_scoped_to_poll(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $poll = $this->openPoll();
        $other = $this->openPoll(['title' => 'Lain']);

        $this->actingAs($admin)
            ->get(route('admin.polls.candidates.edit', [$poll, $other->candidates->first()]))
            ->assertNotFound();
    }
}
