<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'starts_at', 'ends_at', 'is_active', 'show_results', 'created_by'])]
class Poll extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'show_results' => 'boolean',
        ];
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('number');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOpen(): bool
    {
        return $this->status() === 'open';
    }

    /**
     * Status voting: draft (nonaktif), upcoming (belum mulai), open (berlangsung), closed (selesai).
     */
    public function status(): string
    {
        if (! $this->is_active) {
            return 'draft';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'upcoming';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'closed';
        }

        return 'open';
    }

    public function statusLabel(): string
    {
        return match ($this->status()) {
            'draft' => 'Nonaktif',
            'upcoming' => 'Belum dimulai',
            'open' => 'Berlangsung',
            'closed' => 'Selesai',
        };
    }

    public function resultsVisibleTo(?User $user): bool
    {
        if ($user?->is_admin) {
            return true;
        }

        if (! $this->show_results) {
            return false;
        }

        return $this->status() === 'closed' || ($user && $user->hasVotedIn($this));
    }

    /**
     * Ringkasan angka partisipasi untuk panel admin dan file export.
     *
     * @return array{total: int, siswa: int, guru: int, lainnya: int, pemilih_terdaftar: int, partisipasi: float}
     */
    public function rekap(): array
    {
        $total = $this->votes()->count();
        $siswa = $this->votes()->whereHas('user', fn (Builder $query) => $query->whereNotNull('nis'))->count();
        $guru = $this->votes()->whereHas('user', fn (Builder $query) => $query->whereNotNull('nip'))->count();
        $pemilihTerdaftar = User::jumlahPemilihTerdaftar();

        return [
            'total' => $total,
            'siswa' => $siswa,
            'guru' => $guru,
            'lainnya' => $total - $siswa - $guru,
            'pemilih_terdaftar' => $pemilihTerdaftar,
            'partisipasi' => $pemilihTerdaftar > 0 ? min(round($total / $pemilihTerdaftar * 100, 1), 100) : 0,
        ];
    }

    /**
     * Kandidat beserta jumlah suara dan persentasenya, diurutkan dari suara terbanyak.
     */
    public function results()
    {
        $candidates = $this->candidates()->withCount('votes')->get();
        $total = $candidates->sum('votes_count');

        return $candidates
            ->map(function (Candidate $candidate) use ($total) {
                $candidate->percentage = $total > 0 ? round($candidate->votes_count / $total * 100, 1) : 0;

                return $candidate;
            })
            ->sortByDesc('votes_count')
            ->values();
    }
}
