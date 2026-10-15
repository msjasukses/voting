<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan tampilan aplikasi (key-value) yang diubah admin lewat halaman Pengaturan.
 * Setiap nilai menimpa config("voting.<key>"), sehingga semua tampilan ikut berubah.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /**
     * Kunci config/voting.php yang boleh diubah lewat halaman Pengaturan.
     */
    public const KEYS = [
        'school_name',
        'school_year',
        'city',
        'header_subtitle',
        'title',
        'description',
        'motto',
        'motto_caption',
        'logo',
    ];

    private const CACHE_KEY = 'voting.settings';

    /**
     * @return array<string, string|null>
     */
    public static function semua(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->whereIn('key', self::KEYS)
            ->pluck('value', 'key')
            ->all());
    }

    /**
     * @param  array<string, string|null>  $nilai
     */
    public static function simpan(array $nilai): void
    {
        foreach (array_intersect_key($nilai, array_flip(self::KEYS)) as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
        static::terapkanKeConfig();
    }

    /**
     * Hapus pengaturan tersimpan sehingga nilai bawaan dari config/voting.php dipakai lagi.
     */
    public static function hapus(string $key): void
    {
        static::where('key', $key)->delete();

        Cache::forget(self::CACHE_KEY);
        config(['voting.'.$key => config('voting.default.'.$key)]);
        static::terapkanKeConfig();
    }

    /**
     * Timpa config("voting.*") dengan nilai yang disimpan admin.
     */
    public static function terapkanKeConfig(): void
    {
        foreach (static::semua() as $key => $value) {
            config(['voting.'.$key => $value]);
        }
    }
}
