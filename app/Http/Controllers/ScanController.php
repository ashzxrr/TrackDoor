<?php

namespace App\Http\Controllers;

use App\Models\AlasanKeluar;
use App\Models\Gedung;
use App\Models\Karyawan;
use App\Models\LogScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        return view('scan', [
            'gedungs' => Gedung::query()->orderBy('nama_gedung')->get(),
            'alasanKeluars' => AlasanKeluar::query()->orderBy('label')->get(),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode_value' => ['required', 'string'],
        ]);

        $karyawan = Karyawan::query()
            ->where('barcode_value', $validated['barcode_value'])
            ->first();

        if ($karyawan === null) {
            return response()->json(['message' => 'Barcode tidak dikenali'], 404);
        }

        return response()->json([
            'karyawan_id' => $karyawan->id,
            'nip' => $karyawan->nip,
            'nama' => $karyawan->nama,
            'bagian' => $karyawan->bagian,
            'foto_url' => $karyawan->foto_path !== null ? asset('storage/'.$karyawan->foto_path) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'karyawan_id' => ['required', 'integer', 'exists:karyawan,id'],
            'gedung_id' => ['required', 'integer', 'exists:gedung,id'],
            'tipe' => ['required', 'in:in,out'],
            'alasan_id' => ['nullable', 'integer', 'required_if:tipe,out', 'exists:alasan_keluar,id'],
        ]);

        LogScan::create([
            'karyawan_id' => $validated['karyawan_id'],
            'gedung_id' => $validated['gedung_id'],
            'tipe' => $validated['tipe'],
            'alasan_id' => $validated['alasan_id'] ?? null,
            'scanned_at' => now(),
        ]);

        return response()->json(['message' => 'Data tersimpan']);
    }
}
