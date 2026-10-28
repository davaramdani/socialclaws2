# SocialClaws

Website studio nail art berbasis **Laravel 12 / PHP 8.2+**, Blade, Eloquent, CSS dan JavaScript. Tidak membutuhkan Node.js, Tailwind CDN, atau proses build frontend.

## Menjalankan project yang sudah dimigrasikan

Nyalakan **Apache dan MySQL** di XAMPP, lalu buka:

- Website: http://localhost/socialclaws2/
- Admin: http://localhost/socialclaws2/admin/login

Database lokal menggunakan `socialclaws_db`, sesuai project sebelumnya. Akun pelanggan dan admin lama tetap digunakan. Nomor WhatsApp diatur melalui `BOOKING_WHATSAPP_NUMBER` pada `.env`. Password dan konfigurasi database hanya berada di `.env` yang tidak dilacak Git.

## Instalasi di komputer baru

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Buat database MySQL `socialclaws_db`, atur `DB_*` dalam `.env`, lalu:

```powershell
php artisan migrate
php artisan serve
```

Buka http://127.0.0.1:8000. Sesuaikan `APP_URL` pada `.env` jika menggunakan URL berbeda. Untuk database baru, `php artisan db:seed` mengisi katalog contoh **hanya pada tabel katalog kosong**; tidak membuat akun atau ulasan palsu. Seeder tidak perlu dijalankan pada database lama yang sudah berisi konten.

Untuk membuat admin pertama pada database baru:

```powershell
php artisan studio:admin namaadmin
```

Password diminta secara tersembunyi; tidak ada kredensial admin bawaan.

## Fitur

- Layanan dan galeri dari database, pencarian, kategori, pagination, dan preview desain.
- Registrasi/login pelanggan dan guard login admin terpisah, validasi, CSRF, session regeneration, dan pembatasan percobaan login.
- Keranjang tersimpan per pengguna dengan jumlah item yang dapat diubah.
- Booking dari keranjang atau langsung dari katalog, permintaan tanggal/jam, riwayat, dan referensi booking.
- Harga dan detail booking dihitung ulang dari database dalam transaksi. Booking langsung mempertahankan keranjang; checkout keranjang mengosongkannya setelah booking tersimpan.
- Tautan WhatsApp tersedia setelah booking tersimpan; pelanggan memilih sendiri untuk membuka/mengirim konfirmasi. Jadwal tetap menunggu konfirmasi studio.
- Ulasan pelanggan dengan rating 1–5 dan satu ulasan per akun.
- Admin mengelola layanan, galeri, pelanggan, ulasan, dan status booking.

## Data dan backup migrasi

Migrasi mengadopsi tabel lama (`users`, `admins`, `services`, `gallery`, `cart`, `bookings`, `reviews`) dan menambahkan kolom yang diperlukan tanpa menghapus data. Password hash lama tetap kompatibel. `down()` sengaja tidak menghapus tabel pelanggan.

Backup lokal sebelum migrasi tersimpan di:

- `storage/app/backups/socialclaws-before-laravel.sql`: dump database sebelum migrasi.
- `storage/app/backups/php-native-original.zip`: kode PHP asli.
- `storage/app/legacy-native/`: salinan sumber PHP asli yang dipindahkan dari root.

Backup tidak disajikan oleh web server. Hindari `migrate:fresh` atau `db:wipe` pada database lama karena perintah tersebut menghapus data. Simpan backup di lokasi terpisah sebelum perubahan database berikutnya.

## Pengujian

```powershell
php artisan test
php artisan view:cache
```

PHPUnit memakai SQLite `:memory:` secara paksa melalui `phpunit.xml`, terpisah dari MySQL project. Tes mencakup autentikasi, halaman publik/admin, otorisasi keranjang/booking, validasi ulasan, CRUD katalog, dan perhitungan total checkout.

## Hosting

Arahkan DocumentRoot web server ke folder **`public/`**, gunakan HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, dan kredensial database khusus. Root `index.php` dan `.htaccess` disediakan untuk kompatibilitas URL subfolder XAMPP; file internal dan backup diblokir. Upload gambar menerima JPG/PNG/WebP maksimal 4 MB.

Struktur utama: `routes/web.php`, `app/Http/Controllers/`, `app/Models/`, `database/migrations/`, `resources/views/`, `public/css/app.css`, dan `public/js/app.js`.
