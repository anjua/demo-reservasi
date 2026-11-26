# Panduan Deploy Demo Online (3 versi: salon, klinik, bengkel)

Tujuan: punya 3 link demo yang bisa dibuka calon klien dari HP, misalnya:

- `https://salon.domainkamu.my.id`
- `https://klinik.domainkamu.my.id`
- `https://bengkel.domainkamu.my.id`

Ketiganya memakai kode yang sama. Bedanya hanya isi `.env` (nama, warna, alamat, jenis layanan), yang sudah disiapkan di folder `deploy/presets/`.

Perkiraan waktu: 1,5–2 jam (sebagian besar menunggu DNS dan instalasi).
Perkiraan biaya: domain ±Rp15–50 ribu/tahun + VPS ±Rp50–100 ribu/bulan.

---

## Langkah 1: Beli domain (15 menit)

Domain `.my.id` paling murah (±Rp15 ribu/tahun) dan cocok untuk portofolio. Bisa juga `.com` / `.id` kalau mau terlihat lebih profesional.

Penyedia lokal: Niagahoster/Hostinger, Rumahweb, IDCloudHost, Domainesia, dll.

## Langkah 2: Sewa VPS (15 menit)

Pilih VPS dengan spesifikasi minimal **1 vCPU, 1 GB RAM, Ubuntu 24.04**. Satu VPS cukup untuk ketiga demo.

Contoh penyedia: IDCloudHost, Biznet Gio, DewaWeb, Hostinger VPS, atau DigitalOcean/Vultr (server Singapura).

Setelah VPS jadi, catat **alamat IP** dan **password root** (atau kunci SSH) yang diberikan.

## Langkah 3: Arahkan subdomain ke VPS (10 menit + menunggu)

Di panel DNS domain kamu, tambahkan 3 record:

| Tipe | Nama | Nilai |
|---|---|---|
| A | `salon` | IP VPS kamu |
| A | `klinik` | IP VPS kamu |
| A | `bengkel` | IP VPS kamu |

DNS biasanya aktif dalam 5–30 menit. Cek dengan `ping salon.domainkamu.my.id` sampai muncul IP VPS kamu.

## Langkah 4: Siapkan VPS (15 menit, sekali saja)

Masuk ke VPS dari terminal (Windows bisa pakai PowerShell atau aplikasi Termius):

```bash
ssh root@IP_VPS_KAMU
```

Lalu jalankan:

```bash
git clone https://github.com/anjua/demo-reservasi.git /root/demo-reservasi
bash /root/demo-reservasi/deploy/siapkan-vps.sh
```

Skrip ini memasang PHP 8.3, Nginx, Composer, Node.js, Certbot (SSL gratis), dan firewall.

> Kalau repo GitHub kamu **private**, `git clone` akan minta login. Paling mudah: jadikan repo public (cocok untuk portofolio), atau buat *Personal Access Token* di GitHub dan pakai sebagai password.

## Langkah 5: Pasang 3 demo (10 menit per demo)

Ganti `domainkamu.my.id` dan email dengan milikmu (email dipakai untuk sertifikat SSL):

```bash
cd /root/demo-reservasi/deploy
bash pasang-demo.sh salon   salon.domainkamu.my.id   salon   emailkamu@gmail.com
bash pasang-demo.sh klinik  klinik.domainkamu.my.id  klinik  emailkamu@gmail.com
bash pasang-demo.sh bengkel bengkel.domainkamu.my.id bengkel emailkamu@gmail.com
```

Setiap perintah akan:
1. Mengambil kode ke `/var/www/<nama>`
2. Install dependency dan build tampilan
3. Membuat `.env` dari preset (nama usaha, warna, layanan) dengan `DEMO_MODE=true`
4. Membuat database SQLite dan mengisi data contoh
5. Membuat konfigurasi Nginx untuk subdomainnya
6. Memasang jadwal reset data otomatis setiap pukul 02.00
7. Memasang SSL (https) gratis dari Let's Encrypt

Selesai! Buka `https://salon.domainkamu.my.id` di HP. Dashboard admin ada di `/admin` dengan akun `admin@demo.test` / `password`.

## Mengubah isi demo

**Ganti nama, warna, alamat, atau nomor WA**: edit `deploy/presets/<nama>.env` di repo, push ke GitHub, lalu di VPS jalankan ulang perintah `pasang-demo.sh` yang sama. Atau edit langsung `/var/www/<nama>/.env`, lalu:

```bash
cd /var/www/salon && php artisan config:cache
```

> Ganti `BRAND_PHONE` dengan nomor WhatsApp kamu sendiri, supaya calon klien yang mencoba tombol "Kirim ke WhatsApp" langsung masuk ke kamu.

**Update ke kode terbaru**: jalankan ulang perintah `pasang-demo.sh` yang sama. Data tidak dihapus saat update.

**Reset data demo sekarang juga** (tanpa menunggu jam 02.00):

```bash
cd /var/www/salon && sudo -u www-data php artisan demo:reset
```

**Menambah versi baru** (mis. laundry): salin `deploy/presets/salon.env` menjadi `laundry.env`, ubah isinya, tambah A record `laundry` di DNS, lalu `bash pasang-demo.sh laundry laundry.domainkamu.my.id laundry email@kamu.com`. Untuk jenis usaha selain salon/klinik/bengkel, layanan contoh memakai data salon; ubah layanannya dari dashboard admin, atau tambahkan katalog baru di `database/seeders/DatabaseSeeder.php` (fungsi `catalog`).

## Memakai shared hosting (alternatif)

Bisa, asal hosting mendukung **PHP 8.3+ dan akses SSH/Terminal** (mis. paket cPanel yang ada menu Terminal). Langkah singkat:

1. Di komputer sendiri: `composer install --no-dev` dan `npm run build`, lalu upload seluruh folder (termasuk `vendor/` dan `public/build/`).
2. Buat subdomain di cPanel dan arahkan *document root* ke folder `public/`.
3. Buat `.env` (salin `.env.example`, isi `APP_KEY` lewat `php artisan key:generate`, tambahkan isi preset, `DEMO_MODE=true`).
4. `php artisan migrate --seed --force`.
5. Tambah Cron Job di cPanel: `* * * * * cd /home/USER/folder-demo && php artisan schedule:run`.

Untuk 3 versi, ulangi per subdomain dengan folder terpisah. VPS tetap lebih praktis karena semuanya otomatis dengan skrip.

## Masalah umum

| Masalah | Solusi |
|---|---|
| `SSL gagal` | DNS belum aktif. Tunggu beberapa menit, cek `ping subdomain`, lalu jalankan ulang skrip. |
| Halaman `500 Server Error` | Lihat log: `tail -50 /var/www/<nama>/storage/logs/laravel.log` |
| Tampilan berantakan / tanpa warna | Build ulang: `cd /var/www/<nama> && npm run build` |
| Perubahan `.env` tidak terlihat | `php artisan config:cache` |
| `npm ci` kehabisan memori | `siapkan-vps.sh` sudah membuat swap 1 GB otomatis untuk VPS < 2 GB RAM. Cek dengan `swapon --show`. |
