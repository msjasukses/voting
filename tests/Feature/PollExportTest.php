<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PollExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Voting dengan 3 kandidat: A mendapat 2 suara (siswa + guru), B 1 suara, C 0 suara.
     */
    private function pollWithVotes(): Poll
    {
        $poll = Poll::create(['title' => 'Pemilihan Ketua OSIS', 'is_active' => true, 'show_results' => true]);
        $a = $poll->candidates()->create(['number' => 1, 'name' => 'Kandidat A']);
        $b = $poll->candidates()->create(['number' => 2, 'name' => 'Kandidat B']);
        $poll->candidates()->create(['number' => 3, 'name' => 'Kandidat C']);

        $siswa = User::factory()->create(['name' => 'Budi', 'email' => null, 'nis' => '000123', 'kelas' => 'IX-A']);
        $guru = User::factory()->create(['name' => 'Ibu Sari', 'email' => null, 'nip' => '198001012005011001', 'jabatan' => 'Guru']);
        $siswa2 = User::factory()->create(['name' => 'Ani', 'email' => null, 'nis' => '000124', 'kelas' => 'IX-B']);

        Vote::create(['poll_id' => $poll->id, 'candidate_id' => $a->id, 'user_id' => $siswa->id]);
        Vote::create(['poll_id' => $poll->id, 'candidate_id' => $a->id, 'user_id' => $guru->id]);
        Vote::create(['poll_id' => $poll->id, 'candidate_id' => $b->id, 'user_id' => $siswa2->id]);

        return $poll;
    }

    public function test_admin_can_export_results_to_excel(): void
    {
        $this->travelTo('2026-10-12 09:30:00');
        $poll = $this->pollWithVotes();

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.polls.export.excel', $poll));

        $response->assertOk()->assertDownload('hasil-pemilihan-ketua-osis-20261012-093000.xlsx');

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = IOFactory::load($path);
        unlink($path);

        $hasil = $spreadsheet->getSheetByName('Hasil')->toArray(null, false, false);
        $baris = collect($hasil)->keyBy(fn (array $row) => $row[2]);

        $this->assertSame([1, 1, 'Kandidat A', 2], array_slice($baris['Kandidat A'], 0, 4));
        $this->assertEqualsWithDelta(2 / 3, $baris['Kandidat A'][4], 0.001);
        $this->assertSame([2, 2, 'Kandidat B', 1], array_slice($baris['Kandidat B'], 0, 4));
        $this->assertSame([3, 3, 'Kandidat C', 0], array_slice($baris['Kandidat C'], 0, 4));

        $rekap = collect($hasil)->mapWithKeys(fn (array $row) => [(string) $row[0] => $row[2]]);
        $this->assertSame(3, $rekap['Total suara masuk']);
        $this->assertSame(2, $rekap['Suara siswa']);
        $this->assertSame(1, $rekap['Suara guru']);

        $pemilih = $spreadsheet->getSheetByName('Daftar Pemilih')->toArray(null, false, false);
        $this->assertSame(['No', 'Nama', 'Status', 'NIS / NIP', 'Kelas / Jabatan', 'Pilihan', 'Waktu Memilih'], $pemilih[0]);
        $this->assertSame([1, 'Budi', 'Siswa', '000123', 'IX-A', '#1 Kandidat A'], array_slice($pemilih[1], 0, 6));
        $this->assertSame([2, 'Ibu Sari', 'Guru', '198001012005011001', 'Guru', '#1 Kandidat A'], array_slice($pemilih[2], 0, 6));
    }

    public function test_admin_can_export_results_to_pdf(): void
    {
        $this->travelTo('2026-10-12 09:30:00');
        $poll = $this->pollWithVotes();

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.polls.export.pdf', $poll));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('hasil-pemilihan-ketua-osis-20261012-093000.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    #[TestWith(['admin.polls.export.excel'])]
    #[TestWith(['admin.polls.export.pdf'])]
    public function test_voters_cannot_export_results(string $route): void
    {
        $poll = $this->pollWithVotes();

        $this->actingAs(User::factory()->create(['email' => null, 'nis' => '000999']))
            ->get(route($route, $poll))
            ->assertForbidden();
    }
}
