# NikahStudio: migrasi data MySQL ke PostgreSQL (Laragon)

Patch ini ditujukan untuk repository NikahStudio yang sudah ada. Patch tidak menghapus MySQL dan tidak menjalankan migrasi secara otomatis.

## Isi patch

- `config/database.php`: menambahkan koneksi baca `mysql_source` agar Laravel dapat membaca MySQL lama ketika koneksi utama diarahkan ke PostgreSQL.
- `app/Console/Commands/CopyMysqlToPostgres.php`: perintah preflight dan penyalinan data. Secara default hanya memeriksa; data hanya disalin jika opsi `--execute` diberikan dan pengguna menyetujui konfirmasi.
- `database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php`: menghapus modifier posisi kolom `after()` yang khusus MySQL dan tidak diperlukan untuk PostgreSQL.

## 1. Backup MySQL terlebih dahulu

Di HeidiSQL, klik kanan database `nikahstudio` > Export database as SQL. Pilih struktur dan data, lalu simpan ke file di luar folder project, misalnya `D:\Laragon\backup\nikahstudio_mysql_backup.sql`.

Jangan lanjut sebelum backup selesai.

## 2. Salin file patch

Salin folder `app`, `config`, dan `database` dari patch ke folder project `D:\Laragon\www\NikahStudio`, lalu izinkan penggabungan folder. Jangan mengganti `.env` dengan `.env.example`.

## 3. Ubah `.env`

Sebelum mengganti nilai DB_ lama, pindahkan nilai koneksi MySQL lama ke variabel MYSQL_SOURCE_ berikut. Password MySQL lama harus dipindahkan ke `MYSQL_SOURCE_PASSWORD`.

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nikahstudio
DB_USERNAME=postgres
DB_PASSWORD=PASSWORD_POSTGRESQL

MYSQL_SOURCE_HOST=127.0.0.1
MYSQL_SOURCE_PORT=3306
MYSQL_SOURCE_DATABASE=nikahstudio
MYSQL_SOURCE_USERNAME=root
MYSQL_SOURCE_PASSWORD=PASSWORD_MYSQL_LAMA
```

Gunakan password yang benar dari instalasi lokal masing-masing. Jangan membagikan `.env`.

## 4. Buat database target PostgreSQL

Jalankan dari PowerShell:

```powershell
& "D:\laragon\bin\postgresql\postgresql\bin\psql.exe" -h 127.0.0.1 -p 5432 -U postgres -d postgres -c "CREATE DATABASE nikahstudio OWNER postgres;"
```

Jika database sudah ada, jangan jalankan perintah CREATE DATABASE lagi. Periksa dulu isinya.

## 5. Jalankan migration pada PostgreSQL

Dari terminal project:

```powershell
php artisan config:clear
php artisan migrate --database=pgsql
```

Pastikan migration selesai dan jangan gunakan `migrate:fresh`.

## 6. Preflight penyalinan data

```powershell
php artisan nikahstudio:copy-mysql-to-postgres
```

Perintah ini hanya membaca kedua database, memeriksa daftar migration, tabel, kolom, dan jumlah baris. Tidak menyalin atau menghapus data.

Jika preflight lolos dan backup MySQL sudah dibuat:

```powershell
php artisan nikahstudio:copy-mysql-to-postgres --execute
```

Jawab `yes` pada konfirmasi hanya jika backup sudah benar-benar tersedia.

## 7. Verifikasi

Bandingkan jumlah baris di tabel-tabel MySQL dan PostgreSQL. Uji login, daftar template, undangan, acara, media, RSVP, ucapan, dan dashboard. Pertahankan MySQL dan backup sampai seluruh pengujian berhasil.

## Catatan

- Perintah menyalin 21 tabel aplikasi dan tabel Laravel selain `migrations`.
- Tabel `migrations` tidak disalin karena Laravel membuat riwayat migration pada PostgreSQL ketika migration dijalankan. Batch migration kemudian disamakan dengan sumber.
- Perintah menolak menyalin jika tabel target sudah berisi data.
- Penyalinan menggunakan transaksi PostgreSQL agar kegagalan insert membatalkan transaksi data.
- File upload di `storage/app/public` tidak termasuk database dan perlu dipertahankan terpisah jika ada.
