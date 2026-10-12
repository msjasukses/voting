<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Data siswa dari aplikasi Data Center (koneksi "datacenter", READ-ONLY).
 */
class Siswa extends Model
{
    protected $connection = 'datacenter';

    protected $table = 'siswa';

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Siswa yang berhak memilih: aktif dan akunnya tidak diblokir.
     */
    public function scopeBolehMemilih(Builder $query): Builder
    {
        return $query
            ->where('is_aktif', true)
            ->where('status_siswa', 'Aktif')
            ->where('account_status', 'active');
    }

    public static function findByNomor(string $nomor): ?self
    {
        return static::query()
            ->where(fn ($q) => $q->where('nis', $nomor)->orWhere('nisn', $nomor))
            ->first();
    }

    /**
     * Nama rombel (kelas) siswa pada tahun ajaran aktif; jika siswa belum dimasukkan
     * ke rombel tahun ini, pakai rombel tahun ajaran terakhirnya.
     */
    public function kelasSekarang(): ?string
    {
        return $this->getConnection()->table('siswa_rombel as sr')
            ->join('rombongan_belajar as rb', 'rb.id', '=', 'sr.rombongan_belajar_id')
            ->join('tahun_ajaran as ta', 'ta.id', '=', 'sr.tahun_ajaran_id')
            ->where('sr.siswa_id', $this->id)
            ->orderByDesc('ta.is_aktif')
            ->orderByDesc('ta.id')
            ->value('rb.nama_rombel');
    }

    public function save(array $options = []): bool
    {
        throw new \LogicException('Data siswa Data Center bersifat read-only.');
    }

    public function delete(): ?bool
    {
        throw new \LogicException('Data siswa Data Center bersifat read-only.');
    }
}
