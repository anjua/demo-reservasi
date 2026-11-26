<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\ClosedDate;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@demo.test'], [
            'name' => 'Admin Demo',
            'password' => Hash::make('password'),
        ]);

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::put($key, $value);
        }

        // Jam operasional: Senin-Sabtu 09.00-18.00, Minggu tutup, Jumat istirahat panjang dibuat sederhana.
        foreach (range(0, 6) as $day) {
            BusinessHour::updateOrCreate(['day_of_week' => $day], [
                'is_open' => $day !== 0,
                'open_time' => $day === 6 ? '08:00' : '09:00',
                'close_time' => $day === 6 ? '16:00' : '18:00',
            ]);
        }

        $catalog = $this->catalog(config('brand.business_type'));

        $services = collect($catalog['services'])->map(fn ($s, $i) => Service::updateOrCreate(['name' => $s[0]], [
            'description' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
            'is_active' => true,
            'sort_order' => $i,
        ]));

        Setting::put('slot_interval', $catalog['slot_interval']);
        Setting::put('capacity_per_slot', $catalog['capacity']);

        $today = CarbonImmutable::today();

        // Contoh hari libur
        ClosedDate::updateOrCreate(['date' => $this->nextOpenDay($today->addDays(10))->toDateString()], [
            'reason' => 'Libur karyawan',
        ]);

        if (Reservation::exists()) {
            return;
        }

        $names = ['Siti Rahma', 'Dewi Lestari', 'Rina Wulandari', 'Andi Pratama', 'Budi Santoso', 'Maya Sari',
            'Putri Ayu', 'Indah Permata', 'Fajar Nugroho', 'Nurul Hidayah', 'Lina Marlina', 'Rizky Ramadhan'];
        $times = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:30'];

        // 14 hari ke belakang s.d. 7 hari ke depan
        for ($offset = -14; $offset <= 7; $offset++) {
            $date = $today->addDays($offset);
            if ($date->dayOfWeek === 0) {
                continue;
            }

            $count = $offset === 0 ? 4 : random_int(1, 3);
            foreach (collect($times)->shuffle()->take($count)->sort() as $time) {
                $service = $services->random();
                $start = $date->setTimeFromTimeString($time);

                $status = match (true) {
                    $offset < 0 => random_int(1, 10) <= 8 ? 'completed' : 'cancelled',
                    $offset === 0 => random_int(0, 1) ? 'confirmed' : 'pending',
                    default => random_int(1, 10) <= 5 ? 'pending' : 'confirmed',
                };

                Reservation::create([
                    'service_id' => $service->id,
                    'customer_name' => $names[array_rand($names)],
                    'customer_phone' => '628'.random_int(1000000000, 9999999999),
                    'customer_email' => null,
                    'notes' => random_int(1, 4) === 1 ? $catalog['notes'][array_rand($catalog['notes'])] : null,
                    'date' => $date->toDateString(),
                    'start_time' => $start->format('H:i'),
                    'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i'),
                    'price' => $service->price,
                    'status' => $status,
                ]);
            }
        }
    }

    /**
     * Contoh layanan per jenis usaha. Pilih lewat BRAND_BUSINESS_TYPE di .env
     * (salon, klinik, bengkel). Jenis lain memakai data salon.
     */
    private function catalog(string $type): array
    {
        $catalogs = [
            'salon' => [
                'slot_interval' => 30,
                'capacity' => 2,
                'services' => [
                    ['Potong Rambut Wanita', 'Konsultasi gaya, cuci, potong & blow.', 60, 85000],
                    ['Potong Rambut Pria', 'Potong rapi + cuci rambut.', 30, 50000],
                    ['Creambath', 'Pijat kepala & bahu dengan krim pilihan, rambut lebih lembut.', 60, 120000],
                    ['Hair Coloring', 'Pewarnaan rambut penuh, termasuk cuci & blow.', 120, 350000],
                    ['Facial Brightening', 'Pembersihan wajah mendalam + masker pencerah.', 75, 175000],
                    ['Manicure & Pedicure', 'Perawatan kuku tangan & kaki, termasuk kutek.', 90, 150000],
                ],
                'notes' => ['Mohon dengan kapster yang sama seperti sebelumnya.', 'Rambut saya cukup panjang.', 'Datang berdua dengan teman.'],
            ],
            'klinik' => [
                'slot_interval' => 15,
                'capacity' => 2,
                'services' => [
                    ['Konsultasi & Pemeriksaan Gigi', 'Pemeriksaan menyeluruh dan rencana perawatan.', 15, 100000],
                    ['Scaling (Pembersihan Karang Gigi)', 'Membersihkan karang gigi + polishing.', 45, 350000],
                    ['Tambal Gigi', 'Tambalan sewarna gigi (komposit) per gigi.', 30, 300000],
                    ['Cabut Gigi', 'Pencabutan gigi biasa dengan bius lokal.', 30, 250000],
                    ['Bleaching', 'Pemutihan gigi di klinik.', 60, 1500000],
                    ['Konsultasi Behel', 'Pemeriksaan dan konsultasi perawatan ortodonti.', 30, 150000],
                ],
                'notes' => ['Gigi geraham kanan bawah terasa ngilu.', 'Kontrol lanjutan dari kunjungan sebelumnya.', 'Pasien anak, usia 8 tahun.'],
            ],
            'bengkel' => [
                'slot_interval' => 60,
                'capacity' => 3,
                'services' => [
                    ['Servis Ringan Motor', 'Cek & setel rem, rantai, busi, filter udara.', 60, 75000],
                    ['Ganti Oli Motor', 'Ganti oli mesin (harga termasuk oli standar).', 30, 65000],
                    ['Servis Berkala Mobil', 'Ganti oli, filter oli, cek kaki-kaki & kelistrikan.', 120, 450000],
                    ['Tune Up Mobil', 'Bersihkan throttle body, injektor, cek busi & sensor.', 120, 550000],
                    ['Spooring & Balancing', 'Penyetelan roda agar mobil stabil dan ban awet.', 60, 200000],
                    ['Cek AC Mobil', 'Pemeriksaan & isi freon AC.', 60, 250000],
                ],
                'notes' => ['Motor Honda Vario 2019.', 'Mobil Avanza 2017, ada bunyi di roda depan.', 'Minta sekalian cek aki.'],
            ],
        ];

        return $catalogs[$type] ?? $catalogs['salon'];
    }

    private function nextOpenDay(CarbonImmutable $date): CarbonImmutable
    {
        return $date->dayOfWeek === 0 ? $date->addDay() : $date;
    }
}
