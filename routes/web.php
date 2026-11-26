<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Halaman publik pelanggan
Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::get('/slot', [BookingController::class, 'slots'])->name('booking.slots');
Route::post('/booking', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/booking/{code}', [BookingController::class, 'show'])->name('booking.show');
Route::get('/cek-booking', [BookingController::class, 'check'])->name('booking.check');

// Login admin
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:5,1');
});

// Dashboard admin
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/reservasi', [ReservationController::class, 'index'])->name('reservations.index');
    Route::patch('/reservasi/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.status');

    Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/layanan', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/layanan/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/layanan/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::put('/jadwal/jam', [ScheduleController::class, 'updateHours'])->name('schedule.hours');
    Route::put('/jadwal/pengaturan', [ScheduleController::class, 'updateSettings'])->name('schedule.settings');
    Route::post('/jadwal/libur', [ScheduleController::class, 'storeClosedDate'])->name('schedule.closed.store');
    Route::delete('/jadwal/libur/{closedDate}', [ScheduleController::class, 'destroyClosedDate'])->name('schedule.closed.destroy');
});
