<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\AlasanKeluar;
use App\Models\Gedung;
use App\Models\Karyawan;
use App\Models\LogScan;
use App\Queries\AdminScanLogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_admin_session(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_recap_requires_admin_session(): void
    {
        $response = $this->get(route('admin.recap'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_recap_analysis_with_filters(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-500',
            'nama' => 'Karyawan Rekap',
            'bagian' => 'Keuangan',
            'barcode_value' => 'RECAP-TEST',
        ]);
        $gedung = Gedung::create(['nama_gedung' => 'Gedung Rekap']);
        $alasan = AlasanKeluar::create(['label' => 'Keperluan operasional']);
        LogScan::create([
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'out',
            'alasan_id' => $alasan->id,
            'scanned_at' => '2026-09-14 09:00:00',
        ]);
        LogScan::create([
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'in',
            'scanned_at' => '2026-09-14 09:45:00',
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get(route('admin.recap', ['date_from' => '2026-09-14', 'bagian' => 'Keuangan']));

        $response->assertOk()
            ->assertSee('Rekap analisa aktivitas')
            ->assertSee('Karyawan Rekap')
            ->assertSee('45 menit')
            ->assertSee('Keperluan operasional')
            ->assertSee('Gedung Rekap');
    }

    public function test_admin_can_view_paired_and_anomalous_logs(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-100',
            'nama' => 'Admin Test',
            'bagian' => 'Operasional',
            'barcode_value' => 'ADMIN-TEST',
        ]);
        $gedung = Gedung::create(['nama_gedung' => 'Gedung A']);
        $alasan = AlasanKeluar::create(['label' => 'Ke toilet']);
        $out = LogScan::create([
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'out',
            'alasan_id' => $alasan->id,
            'scanned_at' => '2026-09-11 08:00:00',
        ]);
        LogScan::create([
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'in',
            'scanned_at' => '2026-09-11 08:25:00',
        ]);

        $logs = app(AdminScanLogQuery::class)->build([])->get();

        $this->assertCount(1, $logs);
        $this->assertSame('out', $logs->first()->tipe);
        $this->assertSame(25, app(AdminScanLogQuery::class)->format($logs->first())['durasi_menit']);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Admin Test')
            ->assertSee('25 menit')
            ->assertSee('Ke toilet');

        LogScan::whereKey($out->id)->update(['scanned_at' => '2026-09-12 08:00:00']);

        $anomalyLog = app(AdminScanLogQuery::class)->build([])->get()->firstWhere('tipe', 'in');
        $formattedAnomaly = app(AdminScanLogQuery::class)->format($anomalyLog);

        $this->assertNull($formattedAnomaly['waktu_keluar']);
        $this->assertSame('2026-09-11 08:25:00', $formattedAnomaly['waktu_masuk']->format('Y-m-d H:i:s'));

        $anomalyResponse = $this->withSession(['admin_user_id' => $admin->id])
            ->get(route('admin.dashboard'));

        $anomalyResponse->assertOk()->assertSee('Anomali - masuk tanpa keluar');
    }

    public function test_admin_export_route_is_available_with_filters(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get(route('admin.dashboard.export', ['bagian' => 'Operasional']));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_dashboard_displays_in_scans_without_out_pair(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-101',
            'nama' => 'Scan Masuk Saja',
            'bagian' => 'Operasional',
            'barcode_value' => 'IN-ONLY',
        ]);
        $gedung = Gedung::create(['nama_gedung' => 'Gedung A']);
        LogScan::create([
            'karyawan_id' => $karyawan->id,
            'gedung_id' => $gedung->id,
            'tipe' => 'in',
            'scanned_at' => '2026-09-12 08:30:00',
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Scan Masuk Saja')
            ->assertSee('IN');
    }
}
