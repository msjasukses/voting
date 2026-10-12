<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil {{ $poll->title }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
        .kop { width: 100%; border-bottom: 3px double #0b5c8e; padding-bottom: 10px; margin-bottom: 16px; }
        .kop td { vertical-align: middle; }
        .kop .sekolah { font-size: 17px; font-weight: bold; text-transform: uppercase; color: #0b5c8e; }
        .kop .sub { font-size: 11px; color: #475569; }
        h1 { font-size: 15px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        .meta { text-align: center; color: #64748b; margin-bottom: 16px; }
        h2 { font-size: 12px; margin: 18px 0 6px; text-transform: uppercase; color: #0b5c8e; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0384c7; color: #fff; padding: 6px; font-size: 10.5px; }
        table.data td { border: 1px solid #cbd5e1; padding: 6px; }
        table.data tr.total td { font-weight: bold; background: #f1f5f9; }
        .tengah { text-align: center; }
        .kanan { text-align: right; }
        .rekap td { border: 1px solid #cbd5e1; padding: 5px 8px; }
        .rekap td.label { background: #f8fafc; width: 55%; }
        .bar { background: #e2e8f0; height: 9px; width: 100%; }
        .bar div { background: #0384c7; height: 9px; }
        .pemenang { background: #fef3c7; }
        .badge { background: #f59e0b; color: #fff; font-size: 8.5px; padding: 1px 5px; border-radius: 3px; }
        .ttd { width: 100%; margin-top: 40px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 9px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <table class="kop">
        <tr>
            @if ($logo)
                <td style="width: 70px;"><img src="{{ $logo }}" style="height: 62px;"></td>
            @endif
            <td>
                <div class="sekolah">{{ config('voting.school_name') }}</div>
                <div class="sub">{{ config('voting.header_subtitle') }} · Tahun Pelajaran {{ config('voting.school_year') }}</div>
            </td>
        </tr>
    </table>

    <h1>Hasil {{ $poll->title }}</h1>
    <div class="meta">
        Periode: {{ $poll->starts_at?->translatedFormat('d F Y H:i') ?? '-' }} s/d {{ $poll->ends_at?->translatedFormat('d F Y H:i') ?? '-' }}
        · Status: {{ $poll->statusLabel() }}
    </div>

    <h2>Perolehan Suara</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 9%;">Peringkat</th>
                <th style="width: 8%;">No. Urut</th>
                <th>Nama Kandidat</th>
                <th style="width: 11%;">Suara</th>
                <th style="width: 11%;">Persentase</th>
                <th style="width: 22%;">Grafik</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($results as $candidate)
                @php($menang = $candidate->peringkat === 1 && $candidate->votes_count > 0)
                <tr @class(['pemenang' => $menang])>
                    <td class="tengah">{{ $candidate->peringkat }}</td>
                    <td class="tengah">{{ $candidate->number }}</td>
                    <td>
                        {{ $candidate->name }}
                        @if ($menang) <span class="badge">SUARA TERBANYAK</span> @endif
                    </td>
                    <td class="kanan">{{ number_format($candidate->votes_count) }}</td>
                    <td class="kanan">{{ number_format($candidate->percentage, 1, ',', '.') }}%</td>
                    <td><div class="bar"><div style="width: {{ $candidate->percentage }}%;"></div></div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="tengah">Belum ada kandidat.</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="3" class="kanan">TOTAL</td>
                <td class="kanan">{{ number_format($rekap['total']) }}</td>
                <td class="kanan">{{ $rekap['total'] > 0 ? '100%' : '0%' }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <h2>Rekapitulasi</h2>
    <table class="data rekap" style="width: 60%;">
        <tr><td class="label">Total suara masuk</td><td class="kanan">{{ number_format($rekap['total']) }}</td></tr>
        <tr><td class="label">Suara siswa</td><td class="kanan">{{ number_format($rekap['siswa']) }}</td></tr>
        <tr><td class="label">Suara guru</td><td class="kanan">{{ number_format($rekap['guru']) }}</td></tr>
        @if ($rekap['lainnya'] > 0)
            <tr><td class="label">Suara lainnya (admin / akun non Data Center)</td><td class="kanan">{{ number_format($rekap['lainnya']) }}</td></tr>
        @endif
        <tr><td class="label">Pemilih terdaftar (Data Center)</td><td class="kanan">{{ number_format($rekap['pemilih_terdaftar']) }}</td></tr>
        <tr><td class="label">Tingkat partisipasi</td><td class="kanan">{{ number_format($rekap['partisipasi'], 1, ',', '.') }}%</td></tr>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>Kepala Sekolah
                <br><br><br><br><br>
                ( ................................................ )
            </td>
            <td>
                {{ config('voting.city') }}, {{ now()->translatedFormat('d F Y') }}<br>Ketua Panitia Pemilihan
                <br><br><br><br><br>
                ( ................................................ )
            </td>
        </tr>
    </table>

    <div class="footer">Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB · {{ config('app.name') }}</div>
</body>
</html>
