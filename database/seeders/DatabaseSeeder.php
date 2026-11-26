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

        $services = collect([
            ['Potong Rambut Wanita', 'Konsultasi gaya, cuci, potong & blow.', 60, 85000],
            ['Potong Rambut Pria', 'Potong rapi + cuci rambut.', 30, 50000],
            ['Creambath', 'Pijat kepala & bahu dengan krim pilihan, rambut lebih lembut.', 60, 120000],
            ['Hair Coloring', 'Pewarnaan rambut penuh, termasuk cuci & blow.', 120, 350000],
            ['Facial Brightening', 'Pembersihan wajah mendalam + masker pencerah.', 75, 175000],
            ['Manicure & Pedicure', 'Perawatan kuku tangan & kaki, termasuk kutek.', 90, 150000],
        ])->map(fn ($s, $i) => Service::updateOrCreate(['name' => $s[0]], [
            'description' => $s[1],
            'duration_minutes' => $s[2],
            'price' => $s[3],
            'is_active' => true,
            'sort_order' => $i,
        ]));

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
                    'notes' => random_int(1, 4) === 1 ? 'Mohon dengan kapster yang sama seperti sebelumnya.' : null,
                    'date' => $date->toDateString(),
                    'start_time' => $start->format('H:i'),
                    'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i'),
                    'price' => $service->price,
                    'status' => $status,
                ]);
            }
        }
    }

    private function nextOpenDay(CarbonImmutable $date): CarbonImmutable
    {
        return $date->dayOfWeek === 0 ? $date->addDay() : $date;
    }
}
