<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Poll;
use App\Models\Vote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export hasil voting ke Excel (.xlsx) dan PDF untuk arsip / pengumuman panitia.
 */
class PollExportController extends Controller
{
    private const HEADER_FILL = '0384C7';

    public function excel(Poll $poll): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(config('voting.school_name'))
            ->setTitle('Hasil '.$poll->title);

        $this->isiSheetHasil($spreadsheet->getActiveSheet(), $poll);
        $this->isiSheetPemilih($spreadsheet->createSheet(), $poll);
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $this->namaFile($poll, 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function pdf(Poll $poll): Response
    {
        return Pdf::loadView('admin.polls.export-pdf', [
            'poll' => $poll,
            'results' => $this->peringkat($poll->results()),
            'rekap' => $poll->rekap(),
            'logo' => $this->logoDataUri(),
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->download($this->namaFile($poll, 'pdf'));
    }

    private function isiSheetHasil(Worksheet $sheet, Poll $poll): void
    {
        $sheet->setTitle('Hasil');
        $rekap = $poll->rekap();

        $sheet->setCellValue('A1', Str::upper(config('voting.school_name')));
        $sheet->setCellValue('A2', 'HASIL '.Str::upper($poll->title));
        $sheet->setCellValue('A3', 'Periode: '.$this->periode($poll).' · Status: '.$poll->statusLabel());
        $sheet->setCellValue('A4', 'Dicetak: '.now()->translatedFormat('d F Y, H:i').' WIB');
        foreach (['A1', 'A2', 'A3', 'A4'] as $cell) {
            $sheet->mergeCells($cell.':E'.substr($cell, 1));
        }
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A4')->getFont()->setItalic(true)->getColor()->setRGB('64748B');

        $sheet->setCellValue('A6', 'REKAPITULASI');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $baris = 7;
        $barisRekap = array_filter([
            ['Total suara masuk', $rekap['total']],
            ['Suara siswa', $rekap['siswa']],
            ['Suara guru', $rekap['guru']],
            $rekap['lainnya'] > 0 ? ['Suara lainnya (admin / akun non Data Center)', $rekap['lainnya']] : null,
            ['Pemilih terdaftar (Data Center)', $rekap['pemilih_terdaftar']],
            ['Partisipasi', $rekap['partisipasi'] / 100],
        ]);

        foreach ($barisRekap as [$label, $nilai]) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("C{$baris}", $nilai);
            $sheet->mergeCells("A{$baris}:B{$baris}");
            $baris++;
        }
        $sheet->getStyle('C'.($baris - 1))->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle('C7:C'.($baris - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->beriGaris($sheet, 'A7:C'.($baris - 1));

        $awalTabel = $baris + 1;
        $sheet->fromArray(['Peringkat', 'No. Urut', 'Nama Kandidat', 'Jumlah Suara', 'Persentase'], null, "A{$awalTabel}", true);
        $this->gayaHeader($sheet, "A{$awalTabel}:E{$awalTabel}");

        $baris = $awalTabel + 1;
        foreach ($this->peringkat($poll->results()) as $candidate) {
            $sheet->fromArray([
                $candidate->peringkat,
                $candidate->number,
                $candidate->name,
                $candidate->votes_count,
                $candidate->percentage / 100,
            ], null, "A{$baris}", true);
            $baris++;
        }

        $sheet->setCellValue("A{$baris}", 'TOTAL');
        $sheet->mergeCells("A{$baris}:C{$baris}");
        $sheet->setCellValue("D{$baris}", $rekap['total']);
        $sheet->setCellValue("E{$baris}", $rekap['total'] > 0 ? 1 : 0);
        $sheet->getStyle("A{$baris}:E{$baris}")->getFont()->setBold(true);

        $sheet->getStyle('E'.($awalTabel + 1).":E{$baris}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("A{$awalTabel}:B{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->beriGaris($sheet, "A{$awalTabel}:E{$baris}");

        foreach (['A' => 12, 'B' => 10, 'C' => 40, 'D' => 15, 'E' => 13] as $kolom => $lebar) {
            $sheet->getColumnDimension($kolom)->setWidth($lebar);
        }
    }

    private function isiSheetPemilih(Worksheet $sheet, Poll $poll): void
    {
        $sheet->setTitle('Daftar Pemilih');
        $sheet->fromArray(['No', 'Nama', 'Status', 'NIS / NIP', 'Kelas / Jabatan', 'Pilihan', 'Waktu Memilih'], null, 'A1');
        $this->gayaHeader($sheet, 'A1:G1');

        $votes = $poll->votes()->with(['user', 'candidate'])->oldest()->get();

        $baris = 2;
        foreach ($votes as $index => $vote) {
            /** @var Vote $vote */
            $user = $vote->user;
            $sheet->fromArray([
                $index + 1,
                $user->name,
                match (true) {
                    $user->isGuru() => 'Guru',
                    $user->isSiswa() => 'Siswa',
                    $user->is_admin => 'Admin',
                    default => 'Lainnya',
                },
                null,
                $user->isGuru() ? ($user->jabatan ?: 'Guru') : $user->kelas,
                '#'.$vote->candidate->number.' '.$vote->candidate->name,
                $vote->created_at->format('d/m/Y H:i:s'),
            ], null, "A{$baris}", true);
            // Ditulis sebagai teks agar nol di depan NIS dan 18 digit NIP tidak diubah Excel.
            $sheet->setCellValueExplicit("D{$baris}", $user->nis ?? $user->nip ?? '-', DataType::TYPE_STRING);
            $baris++;
        }

        if ($votes->isEmpty()) {
            $sheet->setCellValue('A2', 'Belum ada suara masuk.');
            $sheet->mergeCells('A2:G2');
        }

        $this->beriGaris($sheet, 'A1:G'.max($baris - 1, 2));
        $sheet->getStyle('A2:A'.max($baris - 1, 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D'.max($baris - 1, 2))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->freezePane('A2');

        foreach (['A' => 6, 'B' => 35, 'C' => 10, 'D' => 22, 'E' => 24, 'F' => 32, 'G' => 20] as $kolom => $lebar) {
            $sheet->getColumnDimension($kolom)->setWidth($lebar);
        }
    }

    /**
     * Beri nomor peringkat; kandidat dengan suara sama mendapat peringkat yang sama (1, 1, 3).
     *
     * @param  Collection<int, Candidate>  $results
     * @return Collection<int, Candidate>
     */
    private function peringkat(Collection $results): Collection
    {
        $sebelumnya = null;

        return $results->each(function (Candidate $candidate, int $index) use (&$sebelumnya): void {
            $candidate->peringkat = $sebelumnya && $sebelumnya->votes_count === $candidate->votes_count
                ? $sebelumnya->peringkat
                : $index + 1;
            $sebelumnya = $candidate;
        });
    }

    private function gayaHeader(Worksheet $sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::HEADER_FILL);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function beriGaris(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
    }

    private function periode(Poll $poll): string
    {
        return ($poll->starts_at?->translatedFormat('d M Y H:i') ?? '-').' s/d '.($poll->ends_at?->translatedFormat('d M Y H:i') ?? '-');
    }

    private function namaFile(Poll $poll, string $ekstensi): string
    {
        return 'hasil-'.Str::slug($poll->title).'-'.now()->format('Ymd-His').'.'.$ekstensi;
    }

    /**
     * Logo sekolah sebagai data URI, karena dompdf tidak mengambil gambar lewat URL lokal.
     */
    private function logoDataUri(): ?string
    {
        $path = public_path(config('voting.logo'));

        if (! is_file($path)) {
            return null;
        }

        $mime = str_ends_with(strtolower($path), '.svg') ? 'image/svg+xml' : (mime_content_type($path) ?: 'image/png');

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
