<?php

/*
|--------------------------------------------------------------------------
| Identitas Pemilihan
|--------------------------------------------------------------------------
|
| Teks yang tampil di halaman awal. Ubah lewat file .env tanpa menyentuh kode.
| Logo: ganti file public/images/logo.svg, atau taruh logo sekolah Anda di
| folder public/images lalu isi VOTING_LOGO (contoh: images/logo.png).
|
*/

return [
    'school_name' => env('VOTING_SCHOOL_NAME', 'SMP Negeri 88 Jakarta'),
    'school_year' => env('VOTING_SCHOOL_YEAR', '2026/2027'),
    'city' => env('VOTING_CITY', 'Jakarta'),
    'header_subtitle' => env('VOTING_HEADER_SUBTITLE', 'Pemilihan Ketua OSIS & MPK'),
    'title' => env('VOTING_TITLE', 'Pemilihan OSIS & MPK'),
    'description' => env('VOTING_DESCRIPTION', 'Pemilihan Ketua OSIS dan Ketua MPK'),
    'motto' => env('VOTING_MOTTO', 'Suaramu Menentukan Masa Depan Sekolah'),
    'motto_caption' => env('VOTING_MOTTO_CAPTION', 'Pilih kandidat sesuai hati nurani dan gunakan hak pilihmu dengan bijak.'),
    'logo' => env('VOTING_LOGO', 'images/logo.svg'),
];
