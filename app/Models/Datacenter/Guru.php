<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data guru / PTK dari aplikasi Data Center (koneksi "datacenter", READ-ONLY).
 */
class Guru extends DatacenterModel
{
    protected $table = 'guru';

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    /**
     * Guru yang berhak memilih: aktif dan akunnya tidak diblokir.
     */
    public function scopeBolehMemilih(Builder $query): Builder
    {
        return $query
            ->where('is_aktif', true)
            ->when(static::punyaKolom('account_status'), fn (Builder $query) => $query->where('account_status', 'active'));
    }

    /**
     * Cari guru berdasarkan NIP, atau NUPTK jika kolom itu ada di database Data Center.
     */
    public static function findByNomor(string $nomor): ?self
    {
        return static::query()
            ->where(fn (Builder $query) => $query
                ->where('nip', $nomor)
                ->when(static::punyaKolom('nuptk'), fn (Builder $query) => $query->orWhere('nuptk', $nomor)))
            ->first();
    }
}
