<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Datacenter\Guru;
use App\Models\Datacenter\Siswa;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Throwable;

#[Fillable(['datacenter_siswa_id', 'datacenter_guru_id', 'nis', 'nip', 'name', 'kelas', 'jabatan', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isSiswa(): bool
    {
        return $this->nis !== null;
    }

    public function isGuru(): bool
    {
        return $this->nip !== null;
    }

    /**
     * Pemilih yang datanya berasal dari Data Center (siswa atau guru), bukan akun admin lokal.
     */
    public function isPemilihDataCenter(): bool
    {
        return $this->isSiswa() || $this->isGuru();
    }

    /**
     * Identitas singkat untuk ditampilkan: NIS & kelas untuk siswa, NIP & jabatan untuk guru,
     * email untuk admin.
     */
    public function identitas(): string
    {
        if ($this->isSiswa()) {
            return 'NIS '.$this->nis.($this->kelas ? ' · '.$this->kelas : '');
        }

        if ($this->isGuru()) {
            return 'NIP '.$this->nip.' · '.($this->jabatan ?: 'Guru');
        }

        return (string) $this->email;
    }

    /**
     * Jumlah siswa dan guru yang berhak memilih menurut Data Center. Jika Data Center tidak
     * bisa dihubungi, pakai jumlah siswa dan guru yang pernah login ke aplikasi ini.
     */
    public static function jumlahPemilihTerdaftar(): int
    {
        try {
            return Siswa::bolehMemilih()->count() + Guru::bolehMemilih()->count();
        } catch (Throwable) {
            return static::where(fn ($query) => $query->whereNotNull('nis')->orWhereNotNull('nip'))->count();
        }
    }

    public function hasVotedIn(Poll $poll): bool
    {
        return $this->votes()->where('poll_id', $poll->id)->exists();
    }
}
