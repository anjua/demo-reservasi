# Demo Sistem Reservasi Online (Laravel + Vue)

Aplikasi booking/reservasi online untuk UMKM jasa: **salon, klinik, barbershop, bengkel, laundry, studio foto**, dan sejenisnya. Pelanggan memilih layanan, tanggal, dan jam yang masih kosong, lalu booking dari HP. Pemilik usaha mengelola semuanya dari dashboard admin.

Dibangun dengan **Laravel 13 + Inertia.js + Vue 3 + Tailwind CSS 4**. Tampilan berbahasa Indonesia, mobile-friendly, dan mudah di-rebrand untuk klien yang berbeda hanya lewat file `.env`.

## Fitur

**Halaman pelanggan**
- Pilih layanan (nama, deskripsi, durasi, harga)
- Pilih tanggal; hari tutup dan tanggal libur otomatis tidak bisa dipilih
- Pilih jam dari slot yang **benar-benar tersedia** (memperhitungkan durasi layanan, kapasitas, dan booking lain)
- Isi data diri (nama, nomor WhatsApp, email opsional, catatan)
- Halaman bukti booking dengan kode unik + tombol **kirim ke WhatsApp** usaha
- Menu **Cek Booking** untuk melihat status dengan kode + nomor WA

**Dashboard admin** (`/admin`)
- Ringkasan: reservasi hari ini, menunggu konfirmasi, total bulan ini, pendapatan bulan ini
- Daftar reservasi dengan filter (status, tanggal, layanan, pencarian)
- Konfirmasi / tandai selesai / batalkan (dengan alasan) reservasi
- Tombol **WA** untuk mengirim pesan konfirmasi/pembatalan ke pelanggan (pesan sudah terisi otomatis)
- Kelola layanan (tambah, ubah, nonaktifkan, hapus)
- Atur jam operasional per hari, tanggal libur, jarak slot, kapasitas per slot, minimal waktu pemesanan, dan batas hari ke depan

**Lainnya**
- Pencegahan double booking (dicek ulang di server saat menyimpan)
- Validasi & pesan error berbahasa Indonesia
- Data contoh (seeder) siap demo
- Feature test untuk alur utama

## Kebutuhan

- PHP 8.3+ (ekstensi: pdo_sqlite atau pdo_mysql, mbstring, xml, curl)
- Composer 2
- Node.js 20+ dan npm

## Cara menjalankan (lokal)

```bash
git clone https://github.com/anjua/demo-reservasi.git
cd demo-reservasi

composer install
npm install

cp .env.example .env
php artisan key:generate

# Database SQLite (paling mudah untuk demo)
touch database/database.sqlite
php artisan migrate --seed

# Jalankan
npm run build
php artisan serve
```

Buka:
- Halaman pelanggan: http://localhost:8000
- Dashboard admin: http://localhost:8000/admin
  - Email: `admin@demo.test`
  - Kata sandi: `password`

Saat mengembangkan, jalankan `npm run dev` (di terminal terpisah) agar perubahan tampilan langsung terlihat.

Untuk mengulang data contoh dari awal: `php artisan migrate:fresh --seed`.

### Memakai MySQL

Ubah bagian database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=demo_reservasi
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan `php artisan migrate --seed`.

## Rebrand untuk klien

Semua identitas usaha ada di `.env` (dibaca oleh `config/brand.php`). Ganti, lalu jalankan `php artisan config:clear`:

```env
BRAND_NAME="Klinik Gigi Senyum"
BRAND_TAGLINE="Periksa gigi tanpa antre, booking dari rumah"
BRAND_BUSINESS_TYPE=klinik
BRAND_LOGO=/logo.png              # taruh file di folder public/, kosongkan untuk logo inisial
BRAND_COLOR="#0d9488"             # warna utama, semua tombol & aksen ikut berubah
BRAND_ADDRESS="Jl. Sudirman No. 5, Yogyakarta"
BRAND_PHONE=6281298765432         # nomor WA usaha, format 62..., tanpa + dan spasi
BRAND_EMAIL=halo@klinik-senyum.test
BRAND_INSTAGRAM=klinikgigisenyum
BRAND_MAPS_URL="https://maps.app.goo.gl/xxxx"
```

Setelah itu, dari dashboard admin:
1. **Layanan**: ganti daftar layanan sesuai klien (mis. "Scaling", "Tambal Gigi", "Servis Ringan", "Cuci Kering per kg").
2. **Jadwal & Slot**: atur jam buka, hari libur, jarak slot, dan **kapasitas per slot** (jumlah kapster/dokter/mekanik yang bisa melayani bersamaan).
3. Ganti akun admin: ubah email & kata sandi di `database/seeders/DatabaseSeeder.php` sebelum seeding, atau lewat `php artisan tinker`.

Contoh pengaturan per jenis usaha:

| Jenis usaha | Jarak slot | Kapasitas | Contoh layanan |
|---|---|---|---|
| Salon / barbershop | 30 menit | jumlah kapster | Potong rambut 30–60 mnt |
| Klinik / dokter gigi | 15–30 menit | jumlah dokter | Konsultasi 15 mnt, scaling 45 mnt |
| Bengkel | 60 menit | jumlah lift/mekanik | Servis ringan 60 mnt, ganti oli 30 mnt |
| Laundry (antar-jemput) | 60 menit | jumlah kurir | Jemput cucian 30 mnt |

## Cara kerja slot

Logika ada di `app/Services/SlotService.php`:
- Slot dibuat setiap *jarak slot* menit dari jam buka, dan layanan harus selesai sebelum jam tutup.
- Sebuah slot tersedia jika jumlah reservasi aktif (menunggu, dikonfirmasi, selesai) yang waktunya bertumpang-tindih **kurang dari kapasitas**.
- Reservasi yang dibatalkan otomatis membuka slotnya lagi.
- Slot yang kurang dari *minimal waktu pemesanan* dari sekarang tidak ditampilkan.

## Struktur penting

```
app/
  Http/Controllers/BookingController.php      # halaman pelanggan
  Http/Controllers/Admin/*                    # dashboard admin
  Models/                                     # Service, Reservation, BusinessHour, ClosedDate, Setting
  Services/SlotService.php                    # perhitungan slot tersedia
config/brand.php                              # identitas usaha (rebrand)
database/seeders/DatabaseSeeder.php           # data contoh
lang/id/validation.php                        # pesan validasi Bahasa Indonesia
resources/js/Pages/Booking/*                  # halaman Vue pelanggan
resources/js/Pages/Admin/*                    # halaman Vue admin
resources/js/lib/format.js                    # format Rupiah, tanggal, link WhatsApp
tests/Feature/BookingTest.php                 # pengujian alur utama
```

## Menjalankan test

```bash
php artisan test
```

## Deploy singkat (shared hosting / VPS)

1. Upload kode, jalankan `composer install --no-dev --optimize-autoloader` dan `npm ci && npm run build`.
2. Atur `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, database, dan variabel `BRAND_*`).
3. `php artisan key:generate`, `php artisan migrate --force`, lalu buat akun admin (seeder atau tinker).
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
5. Arahkan document root web server ke folder `public/`.

## Ide pengembangan untuk klien berbayar

- Notifikasi WhatsApp otomatis (via WA gateway seperti Fonnte/Wablas)
- Pilih staf/kapster tertentu
- Pembayaran DP online (Midtrans/Xendit)
- Pengingat H-1 otomatis
- Laporan pendapatan per layanan/bulan dan ekspor Excel
- Multi-cabang
