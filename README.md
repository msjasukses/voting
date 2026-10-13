# Aplikasi Voting (Laravel 13 + MySQL)

Aplikasi pemungutan suara online: satu akun, satu suara per voting.

## Fitur

**Halaman awal** (`/`)
- Header berisi logo dan nama sekolah, serta jam & tanggal WIB yang berjalan langsung
- Tombol **Mulai Voting** (ke halaman Login Pemilih `/masuk`) dan **Admin** (ke login admin `/login`)
- Semua teks bisa diubah lewat `.env` (`VOTING_SCHOOL_NAME`, `VOTING_TITLE`, dst., lihat `config/voting.php`)
- Logo: ganti `public/images/logo.svg`, atau taruh file logo sendiri di `public/images` lalu isi `VOTING_LOGO`

**Pemilih**
- **Siswa** login dengan **NIS / NISN**, **guru** login dengan **NIP / NUPTK**. Data diambil dari database aplikasi **Data Center** (`datacenter_v2`), hanya siswa & guru aktif yang bisa masuk
- Tidak ada registrasi: akun pemilih otomatis dibuat/diperbarui (nama, NIS & kelas / NIP & jabatan) saat pertama kali login
- Melihat daftar voting beserta statusnya (Berlangsung / Belum dimulai / Selesai)
- Memilih kandidat (kartu dengan foto, nomor urut, visi & misi) — hanya sekali per voting
- Melihat hasil sementara/akhir dalam bentuk grafik batang & persentase

**Admin**
- Dashboard statistik (jumlah voting, pemilih, suara masuk)
- CRUD voting: judul, deskripsi, jadwal mulai/selesai, aktif/nonaktif, tampil/sembunyikan hasil
- CRUD kandidat termasuk upload foto
- Hasil perolehan suara, tingkat partisipasi, dan riwayat siapa memilih siapa
- Reset seluruh suara pada sebuah voting
- Kelola pengguna: cari, jadikan admin/pemilih, hapus

## Struktur Database (MySQL `voting`)

| Tabel        | Kolom penting                                                                           |
|--------------|------------------------------------------------------------------------------------------|
| `users`      | `datacenter_siswa_id`, `datacenter_guru_id`, `nis`, `nip`, `name`, `kelas`, `jabatan`, `email`, `password`, `is_admin` |
| `polls`      | `title`, `description`, `starts_at`, `ends_at`, `is_active`, `show_results`, `created_by` |
| `candidates` | `poll_id`, `number`, `name`, `description`, `photo`                                      |
| `votes`      | `poll_id`, `candidate_id`, `user_id`, `ip_address` — **UNIQUE(`poll_id`, `user_id`)**     |

Indeks unik pada `votes` menjamin satu pengguna hanya bisa memilih sekali per voting,
termasuk saat terjadi klik ganda atau request bersamaan.

## Instalasi (Laragon)

1. Pastikan MySQL di Laragon sudah berjalan.
2. Buat database: `CREATE DATABASE voting;` (atau lewat HeidiSQL).
3. Dari folder proyek jalankan:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

4. Klik **Reload** di Laragon, lalu buka http://voting.test
   (atau jalankan `php artisan serve` dan buka http://localhost:8000).

## Akun Demo

Akun admin dibuat oleh `database/seeders/DatabaseSeeder.php` (password: `password`).
Pemilih login dengan NIS (siswa) atau NIP (guru) yang ada di Data Center.

| Peran   | Email                 |
|---------|-----------------------|
| Admin   | `admin@voting.test`   |

Seeder juga membuat contoh voting Ketua OSIS dan Ketua MPK.
Untuk mengulang data dari awal: `php artisan migrate:fresh --seed`.

## Menjalankan Test

```bash
php artisan test
```

## Koneksi ke Data Center

Pengaturan di `.env` (koneksi `datacenter` di `config/database.php`, hanya **dibaca**, tidak pernah ditulis):

```
DATACENTER_DB_HOST=127.0.0.1
DATACENTER_DB_PORT=3306
DATACENTER_DB_DATABASE=datacenter_v2
DATACENTER_DB_USERNAME=root
DATACENTER_DB_PASSWORD=
```

- Tabel yang dibaca: `siswa`, `guru`, `siswa_rombel`, `rombongan_belajar`, `tahun_ajaran`
- Syarat boleh memilih — siswa: `is_aktif = 1`, `status_siswa = 'Aktif'`, `account_status = 'active'`; guru: `is_aktif = 1`, `account_status = 'active'`
- Kelas diambil dari rombel tahun ajaran aktif (atau rombel terakhir jika belum ada)
- Angka "Pemilih terdaftar" & "Partisipasi" di panel admin dihitung dari jumlah siswa + guru aktif di Data Center

### Cek koneksi Data Center di server

```bash
php artisan datacenter:cek
```

Menampilkan koneksi yang dipakai, memeriksa tabel & kolom yang dibutuhkan, dan menghitung siswa/guru
yang berhak memilih. Kolom yang ditambahkan belakangan di aplikasi Data Center (`guru.nuptk`,
`siswa.status_siswa`, `account_status`) bersifat opsional: jika belum ada, aplikasi voting tetap jalan
dan pencarian lewat kolom itu dilewati. Detail error login pemilih dicatat di `storage/logs/laravel.log`.
