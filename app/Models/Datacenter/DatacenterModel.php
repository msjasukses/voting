<?php

namespace App\Models\Datacenter;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Model dasar untuk tabel aplikasi Data Center (koneksi "datacenter", READ-ONLY).
 *
 * Database Data Center di tiap server bisa berbeda versi migrasinya, jadi kolom yang
 * ditambahkan belakangan (mis. guru.nuptk, siswa.status_siswa) dicek dulu sebelum dipakai.
 */
abstract class DatacenterModel extends Model
{
    protected $connection = 'datacenter';

    protected $hidden = ['password', 'remember_token'];

    /**
     * @var array<string, list<string>>
     */
    private static array $daftarKolom = [];

    public static function punyaKolom(string $kolom): bool
    {
        $model = new static;
        $kunci = $model->getConnection()->getName().'.'.$model->getConnection()->getDatabaseName().'.'.$model->getTable();

        self::$daftarKolom[$kunci] ??= $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        return in_array($kolom, self::$daftarKolom[$kunci], true);
    }

    /**
     * Lupakan daftar kolom yang tersimpan, mis. setelah struktur tabel berubah saat testing.
     */
    public static function lupakanDaftarKolom(): void
    {
        self::$daftarKolom = [];
    }

    public function save(array $options = []): bool
    {
        throw new LogicException('Data Data Center bersifat read-only.');
    }

    public function delete(): ?bool
    {
        throw new LogicException('Data Data Center bersifat read-only.');
    }
}
