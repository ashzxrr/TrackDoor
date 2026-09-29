<?php

namespace Tests\Feature;

use App\Models\AlasanKeluar;
use App\Models\Gedung;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_requires_security_session(): void
    {
        $response = $this->get(route('scan'));

        $response->assertRedirect(route('login'));
    }

    public function test_lookup_returns_employee_data_for_known_barcode(): void
    {
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-001',
            'nama' => 'Sari Dewi',
            'bagian' => 'Operasional',
            'barcode_value' => 'BARCODE-001',
            'foto_path' => 'karyawan-photos/sari-dewi.jpg',
        ]);

        $response = $this->withoutMiddleware()
            ->withSession(['security_account_id' => 1])
            ->postJson(route('scan.lookup'), ['barcode_value' => $karyawan->barcode_value]);

        $response->assertOk()->assertJson([
            'karyawan_id' => $karyawan->id,
            'nip' => 'LMG-2026-001',
            'nama' => 'Sari Dewi',
            'bagian' => 'Operasional',
            'foto_url' => asset('storage/karyawan-photos/sari-dewi.jpg'),
        ]);
    }

    public function test_lookup_returns_not_found_for_unknown_barcode(): void
    {
        $response = $this->withoutMiddleware()
            ->withSession(['security_account_id' => 1])
            ->postJson(route('scan.lookup'), ['barcode_value' => 'UNKNOWN']);

        $response->assertNotFound()->assertJson(['message' => 'Barcode tidak dikenali']);
    }

    public function test_out_scan_requires_reason_and_valid_scan_is_persisted(): void
    {
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-002',
            'nama' => 'Budi Santoso',
            'bagian' => 'Gudang',
            'barcode_value' => 'BARCODE-002',
        ]);
        $gedung = Gedung::create(['nama_gedung' => 'Gedung A']);
        $alasan = AlasanKeluar::create(['label' => 'Ke toilet']);

        $missingReasonResponse = $this->withoutMiddleware()
            ->withSession(['security_account_id' => 1])
            ->postJson(route('scan.store'), [
                'karyawan_id' => $karyawan->id,
                'gedung_id' => $gedung->id,
                'tipe' => 'out',
            ]);

        $missingReasonResponse->assertUnprocessable()->assertJsonValidationErrors('alasan_id');

        $response = $this->withoutMiddleware()
            ->withSession(['security_account_id' => 1])
            ->postJson(route('scan.store'), [
                'karyawan_id' => $karyawan->id,
                'gedung_id' => $gedung->id,
                'tipe' => 'out',
                'alasan_id' => $alasan->id,
            ]);

        $response->assertOk()->assertJson(['message' => 'Data tersimpan']);
        $this->assertDatabaseHas('log_scan', [
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'out',
            'alasan_id' => $alasan->id,
        ]);
    }
}
