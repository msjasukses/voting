<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Data guru / PTK dari aplikasi Data Center (koneksi "datacenter", READ-ONLY).
 */
class Guru extends Model
{
    protected $connection = 'datacenter';

    protected $table = 'guru';

    protected $hidden = ['password', 'remember_token'];

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
            ->where('account_status', 'active');
    }

    public static function findByNomor(string $nomor): ?self
    {
        return static::query()
            ->where(fn (Builder $query) => $query->where('nip', $nomor)->orWhere('nuptk', $nomor))
            ->first();
    }

    public function save(array $options = []): bool
    {
        throw new \LogicException('Data guru Data Center bersifat read-only.');
    }

    public function delete(): ?bool
    {
        throw new \LogicException('Data guru Data Center bersifat read-only.');
    }
}
