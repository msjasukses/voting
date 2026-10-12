<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['datacenter_siswa_id', 'nis', 'name', 'kelas', 'email', 'password', 'is_admin'])]
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

    /**
     * Identitas singkat untuk ditampilkan: NIS & kelas untuk siswa, email untuk admin.
     */
    public function identitas(): string
    {
        return $this->isSiswa()
            ? 'NIS '.$this->nis.($this->kelas ? ' · '.$this->kelas : '')
            : (string) $this->email;
    }

    public function hasVotedIn(Poll $poll): bool
    {
        return $this->votes()->where('poll_id', $poll->id)->exists();
    }
}
