<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\ClosedDate;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    private CarbonImmutable $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // test tidak perlu hasil build aset frontend

        foreach (range(0, 6) as $day) {
            BusinessHour::create(['day_of_week' => $day, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '12:00']);
        }
        Setting::put('slot_interval', 60);
        Setting::put('capacity_per_slot', 1);

        $this->service = Service::create(['name' => 'Potong Rambut', 'duration_minutes' => 60, 'price' => 50000]);
        $this->date = CarbonImmutable::tomorrow();
    }

    private function book(array $override = [])
    {
        return $this->post('/booking', array_merge([
            'service_id' => $this->service->id,
            'date' => $this->date->toDateString(),
            'time' => '09:00',
            'customer_name' => 'Siti',
            'customer_phone' => '081234567890',
        ], $override));
    }

    public function test_halaman_booking_bisa_dibuka(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_slot_dibuat_sesuai_jam_operasional(): void
    {
        $this->getJson("/slot?service_id={$this->service->id}&date={$this->date->toDateString()}")
            ->assertOk()
            ->assertJsonPath('slots.*.time', ['09:00', '10:00', '11:00']);
    }

    public function test_pelanggan_bisa_booking_dan_nomor_dinormalisasi(): void
    {
        $this->book()->assertRedirect();

        $reservation = Reservation::first();
        $this->assertSame('pending', $reservation->status);
        $this->assertSame('6281234567890', $reservation->customer_phone);
        $this->assertSame('10:00', $reservation->end_time);
    }

    public function test_tidak_bisa_double_booking_saat_slot_penuh(): void
    {
        $this->book()->assertRedirect();
        $this->book(['customer_name' => 'Budi'])->assertSessionHasErrors('time');

        $this->assertSame(1, Reservation::count());
    }

    public function test_reservasi_yang_dibatalkan_membuka_slot_lagi(): void
    {
        $this->book();
        Reservation::first()->update(['status' => 'cancelled']);

        $this->book(['customer_name' => 'Budi'])->assertRedirect();
        $this->assertSame(2, Reservation::count());
    }

    public function test_tidak_bisa_booking_di_tanggal_libur(): void
    {
        ClosedDate::create(['date' => $this->date->toDateString(), 'reason' => 'Libur']);

        $this->book()->assertSessionHasErrors('time');
    }

    public function test_dashboard_admin_wajib_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_bisa_konfirmasi_reservasi(): void
    {
        $this->book();
        $reservation = Reservation::first();

        $this->actingAs(User::factory()->create())
            ->patch("/admin/reservasi/{$reservation->id}/status", ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame('confirmed', $reservation->fresh()->status);
    }
}
