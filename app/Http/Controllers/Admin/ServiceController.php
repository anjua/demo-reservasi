<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Services/Index', [
            'services' => Service::ordered()->withCount('reservations')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Service::create($this->validated($request));

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $service->update($this->validated($request));

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->reservations()->exists()) {
            $service->update(['is_active' => false]);

            return back()->with('success', 'Layanan sudah punya riwayat reservasi, jadi dinonaktifkan (tidak dihapus).');
        }

        $service->delete();

        return back()->with('success', 'Layanan dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'nama layanan',
            'description' => 'deskripsi',
            'duration_minutes' => 'durasi',
            'price' => 'harga',
        ]) + ['sort_order' => 0];
    }
}
