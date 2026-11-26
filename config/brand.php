<?php

/*
|--------------------------------------------------------------------------
| Identitas Usaha (Rebrand)
|--------------------------------------------------------------------------
| Ubah nilai di file .env untuk menyesuaikan demo dengan klien baru
| tanpa menyentuh kode. Lihat README bagian "Rebrand untuk klien".
*/

return [
    'name' => env('BRAND_NAME', 'Salon Cantik Ayu'),
    'tagline' => env('BRAND_TAGLINE', 'Perawatan rambut & kecantikan, booking online tanpa antre'),
    'business_type' => env('BRAND_BUSINESS_TYPE', 'salon'), // dipakai untuk teks kecil, mis. "salon", "klinik", "bengkel"
    'logo' => env('BRAND_LOGO'), // URL/path logo (opsional). Kosong = pakai inisial nama
    'color' => env('BRAND_COLOR', '#db2777'), // warna utama (hex)
    'address' => env('BRAND_ADDRESS', 'Jl. Melati No. 12, Bandung'),
    'phone' => env('BRAND_PHONE', '6281234567890'), // format internasional tanpa +, untuk tautan WhatsApp
    'email' => env('BRAND_EMAIL', 'halo@salon-cantik.test'),
    'instagram' => env('BRAND_INSTAGRAM', 'saloncantikayu'),
    'maps_url' => env('BRAND_MAPS_URL'),
];
