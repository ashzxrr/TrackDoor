<?php

namespace Database\Seeders;

use App\Models\AlasanKeluar;
use App\Models\Gedung;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['LowRisk', 'HighRisk', 'Medium Risk'] as $namaGedung) {
            Gedung::firstOrCreate(['nama_gedung' => $namaGedung]);
        }

        foreach (['Ke toilet', 'Ke klinik', 'Ambil barang', 'Urusan pribadi','Mushollah (Sholat)' , 'Lainnya'] as $label) {
            AlasanKeluar::firstOrCreate(['label' => $label]);
        }
    }
}
