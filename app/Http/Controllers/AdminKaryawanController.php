<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminKaryawanController extends Controller
{
    public function index(): View
    {
        $karyawans = Karyawan::query()->orderBy('nama')->get();

        return view('admin.karyawan.index', compact('karyawans'));
    }

    public function create(): View
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Karyawan::create($this->validatedData($request));

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan): View
    {
        return view('admin.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update($this->validatedData($request, $karyawan));

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        if ($karyawan->foto_path !== null) {
            Storage::disk('public')->delete($karyawan->foto_path);
        }

        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }

    public function uploadPhoto(Request $request, Karyawan $karyawan): JsonResponse
    {
        $validated = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200,max_width=2000,max_height=2000'],
        ]);

        $oldPhotoPath = $karyawan->foto_path;
        $extension = $validated['foto']->getClientOriginalExtension() ?: 'jpg';
        $filename = uniqid('karyawan-', true).'.'.$extension;
        $photoPath = $validated['foto']->storeAs('karyawan-photos', $filename, 'public');

        if ($photoPath === false) {
            return response()->json([
                'message' => 'Foto tidak dapat disimpan. Periksa folder storage dan izinnya.',
            ], 500);
        }

        $karyawan->update([
            'foto_path' => $photoPath,
        ]);

        if ($oldPhotoPath !== null && $oldPhotoPath !== $photoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return response()->json([
            'message' => 'Foto karyawan berhasil disimpan.',
            'url' => asset('storage/'.$karyawan->foto_path),
        ]);
    }

    /**
     * @return array{nip: string, nama: string, bagian: string, barcode_value: string}
     */
    private function validatedData(Request $request, ?Karyawan $karyawan = null): array
    {
        return $request->validate([
            'nip' => ['required', 'string', 'max:255', Rule::unique('karyawan', 'nip')->ignore($karyawan)],
            'nama' => ['required', 'string', 'max:255'],
            'bagian' => ['required', 'string', 'max:255'],
            'barcode_value' => ['required', 'string', 'max:255', Rule::unique('karyawan', 'barcode_value')->ignore($karyawan)],
        ]);
    }
}
