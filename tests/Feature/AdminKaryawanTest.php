<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminKaryawanTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_crud_requires_admin_session(): void
    {
        $response = $this->get(route('admin.karyawan.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_create_update_read_and_delete_karyawan(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        $session = ['admin_user_id' => $admin->id];

        $createResponse = $this->withSession($session)->post(route('admin.karyawan.store'), [
            'nip' => 'LMG-2026-200',
            'nama' => 'Karyawan Baru',
            'bagian' => 'Operasional',
            'barcode_value' => 'BARCODE-200',
        ]);

        $karyawan = Karyawan::query()->where('nip', 'LMG-2026-200')->firstOrFail();
        $createResponse->assertRedirect(route('admin.karyawan.index'));
        $this->withSession($session)->get(route('admin.karyawan.index'))->assertOk()->assertSee('Karyawan Baru');

        $this->withSession($session)->put(route('admin.karyawan.update', $karyawan), [
            'nip' => 'LMG-2026-201',
            'nama' => 'Karyawan Diperbarui',
            'bagian' => 'HRD',
            'barcode_value' => 'BARCODE-201',
        ])->assertRedirect(route('admin.karyawan.index'));

        $this->assertDatabaseHas('karyawan', [
            'id' => $karyawan->id,
            'nip' => 'LMG-2026-201',
            'nama' => 'Karyawan Diperbarui',
        ]);

        $this->withSession($session)->delete(route('admin.karyawan.destroy', $karyawan))
            ->assertRedirect(route('admin.karyawan.index'));
        $this->assertDatabaseMissing('karyawan', ['id' => $karyawan->id]);
    }

    public function test_karyawan_unique_fields_are_validated(): void
    {
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        Karyawan::create([
            'nip' => 'LMG-2026-300',
            'nama' => 'Karyawan Lama',
            'bagian' => 'Operasional',
            'barcode_value' => 'BARCODE-300',
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.karyawan.store'), [
                'nip' => 'LMG-2026-300',
                'nama' => 'Karyawan Duplikat',
                'bagian' => 'HRD',
                'barcode_value' => 'BARCODE-300',
            ]);

        $response->assertSessionHasErrors(['nip', 'barcode_value']);
    }

    public function test_admin_can_upload_and_replace_a_karyawan_photo(): void
    {
        Storage::fake('public');
        $admin = AdminUser::create(['username' => 'admin', 'password' => 'admin123']);
        $karyawan = Karyawan::create([
            'nip' => 'LMG-2026-400',
            'nama' => 'Karyawan Foto',
            'bagian' => 'HRD',
            'barcode_value' => 'BARCODE-400',
        ]);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.karyawan.photo', $karyawan), [
                'foto' => UploadedFile::fake()->image('pertama.jpg', 400, 400),
            ]);

        $response->assertOk()->assertJsonStructure(['message', 'url']);
        $karyawan->refresh();
        Storage::disk('public')->assertExists($karyawan->foto_path);
        $firstPath = $karyawan->foto_path;

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.karyawan.photo', $karyawan), [
                'foto' => UploadedFile::fake()->image('kedua.jpg', 400, 400),
            ])->assertOk();

        $karyawan->refresh();
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($karyawan->foto_path);
    }
}
