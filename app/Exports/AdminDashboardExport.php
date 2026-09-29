<?php

namespace App\Exports;

use App\Queries\AdminScanLogQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminDashboardExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(private array $filters, private AdminScanLogQuery $logQuery) {}

    public function query(): Builder
    {
        return $this->logQuery->build($this->filters);
    }

    public function headings(): array
    {
        return ['NIP', 'Nama', 'Bagian', 'Gedung', 'Tipe', 'Waktu Scan', 'Waktu Keluar', 'Waktu Masuk', 'Durasi (menit)', 'Alasan'];
    }

    public function map($row): array
    {
        $log = $this->logQuery->format($row);

        return [
            $log['nip'],
            $log['nama'],
            $log['bagian'],
            $log['gedung'],
            $log['tipe'],
            $log['waktu_scan']->format('Y-m-d H:i:s'),
            $log['waktu_keluar']?->format('Y-m-d H:i:s'),
            $log['waktu_masuk']?->format('Y-m-d H:i:s'),
            $log['durasi_menit'],
            $log['alasan'],
        ];
    }
}
