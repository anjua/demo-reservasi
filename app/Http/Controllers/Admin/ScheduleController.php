<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\ClosedDate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Schedule', [
            'hours' => BusinessHour::orderBy('day_of_week')->get(),
            'closedDates' => ClosedDate::whereDate('date', '>=', today())->orderBy('date')->get(),
            'settings' => Setting::allWithDefaults(),
        ]);
    }

    public function updateHours(Request $request)
    {
        $data = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.is_open' => ['boolean'],
            'hours.*.open_time' => ['required', 'date_format:H:i'],
            'hours.*.close_time' => ['required', 'date_format:H:i', 'after:hours.*.open_time'],
        ], [
            'hours.*.close_time.after' => 'Jam tutup harus setelah jam buka.',
        ]);

        foreach ($data['hours'] as $row) {
            BusinessHour::updateOrCreate(['day_of_week' => $row['day_of_week']], $row);
        }

        return back()->with('success', 'Jam operasional disimpan.');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'slot_interval' => ['required', 'integer', 'in:15,30,45,60'],
            'capacity_per_slot' => ['required', 'integer', 'min:1', 'max:50'],
            'min_notice_minutes' => ['required', 'integer', 'min:0', 'max:2880'],
            'max_days_ahead' => ['required', 'integer', 'min:1', 'max:180'],
        ], [], [
            'slot_interval' => 'jarak slot',
            'capacity_per_slot' => 'kapasitas',
            'min_notice_minutes' => 'minimal waktu pemesanan',
            'max_days_ahead' => 'batas hari ke depan',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('success', 'Pengaturan slot disimpan.');
    }

    public function storeClosedDate(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today', 'unique:closed_dates,date'],
            'reason' => ['nullable', 'string', 'max:100'],
        ], [
            'date.unique' => 'Tanggal ini sudah ditandai libur.',
        ], ['date' => 'tanggal', 'reason' => 'keterangan']);

        ClosedDate::create($data);

        return back()->with('success', 'Tanggal libur ditambahkan.');
    }

    public function destroyClosedDate(ClosedDate $closedDate)
    {
        $closedDate->delete();

        return back()->with('success', 'Tanggal libur dihapus.');
    }
}
