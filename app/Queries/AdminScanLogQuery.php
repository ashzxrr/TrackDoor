<?php

namespace App\Queries;

use App\Models\LogScan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AdminScanLogQuery
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function build(array $filters): Builder
    {
        $incomingScan = LogScan::query()
            ->from('log_scan as incoming_scan')
            ->select('incoming_scan.scanned_at')
            ->whereColumn('incoming_scan.karyawan_id', 'outgoing_scan.karyawan_id')
            ->where('incoming_scan.tipe', 'in')
            ->whereColumn('incoming_scan.scanned_at', '>', 'outgoing_scan.scanned_at')
            ->orderBy('incoming_scan.scanned_at')
            ->orderBy('incoming_scan.id')
            ->limit(1);

        $outgoingQuery = LogScan::query()
            ->from('log_scan as outgoing_scan')
            ->join('karyawan', 'karyawan.id', '=', 'outgoing_scan.karyawan_id')
            ->join('gedung', 'gedung.id', '=', 'outgoing_scan.gedung_id')
            ->leftJoin('alasan_keluar', 'alasan_keluar.id', '=', 'outgoing_scan.alasan_id')
            ->where('outgoing_scan.tipe', 'out')
            ->select([
                'outgoing_scan.id',
                'outgoing_scan.tipe',
                'karyawan.nip',
                'karyawan.nama',
                'karyawan.bagian',
                'gedung.nama_gedung',
                'outgoing_scan.scanned_at as waktu_scan',
                'outgoing_scan.scanned_at as waktu_keluar',
            ])
            ->selectSub($incomingScan, 'waktu_masuk');

        if (in_array($outgoingQuery->getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $outgoingQuery->selectRaw(
                'TIMESTAMPDIFF(MINUTE, outgoing_scan.scanned_at, ('.$incomingScan->toSql().')) as durasi_menit',
                $incomingScan->getBindings(),
            );
        } else {
            $outgoingQuery->selectRaw('NULL as durasi_menit');
        }

        $outgoingQuery->selectRaw('alasan_keluar.label as alasan');

        $previousOutgoingScan = LogScan::query()
            ->from('log_scan as previous_outgoing_scan')
            ->selectRaw('1')
            ->whereColumn('previous_outgoing_scan.karyawan_id', 'incoming_scan.karyawan_id')
            ->where('previous_outgoing_scan.tipe', 'out')
            ->whereColumn('previous_outgoing_scan.scanned_at', '<', 'incoming_scan.scanned_at');

        $incomingQuery = LogScan::query()
            ->from('log_scan as incoming_scan')
            ->join('karyawan', 'karyawan.id', '=', 'incoming_scan.karyawan_id')
            ->join('gedung', 'gedung.id', '=', 'incoming_scan.gedung_id')
            ->where('incoming_scan.tipe', 'in')
            ->select([
                'incoming_scan.id',
                'incoming_scan.tipe',
                'karyawan.nip',
                'karyawan.nama',
                'karyawan.bagian',
                'gedung.nama_gedung',
                'incoming_scan.scanned_at as waktu_scan',
            ])
            ->selectRaw('NULL as waktu_keluar')
            ->selectRaw('incoming_scan.scanned_at as waktu_masuk')
            ->selectRaw('NULL as durasi_menit')
            ->selectRaw('NULL as alasan')
            ->whereNotExists($previousOutgoingScan);

        $this->applyFilters($outgoingQuery, $filters, 'outgoing_scan');
        $this->applyFilters($incomingQuery, $filters, 'incoming_scan');

        return $outgoingQuery
            ->unionAll($incomingQuery)
            ->orderByDesc('waktu_scan')
            ->orderByDesc('id');
    }

    private function applyFilters(Builder $query, array $filters, string $scanAlias): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate($scanAlias.'.scanned_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate($scanAlias.'.scanned_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['bagian'])) {
            $query->where('karyawan.bagian', $filters['bagian']);
        }

        if (! empty($filters['gedung_id'])) {
            $query->where($scanAlias.'.gedung_id', $filters['gedung_id']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function format(object $row): array
    {
        $waktuKeluar = $row->waktu_keluar !== null ? Carbon::parse($row->waktu_keluar) : null;
        $waktuMasuk = $row->waktu_masuk !== null ? Carbon::parse($row->waktu_masuk) : null;

        return [
            'nip' => $row->nip,
            'nama' => $row->nama,
            'bagian' => $row->bagian,
            'gedung' => $row->nama_gedung,
            'tipe' => $row->tipe,
            'waktu_scan' => Carbon::parse($row->waktu_scan),
            'waktu_keluar' => $waktuKeluar,
            'waktu_masuk' => $waktuMasuk,
            'durasi_menit' => $row->durasi_menit !== null
                ? (int) $row->durasi_menit
                : ($waktuMasuk !== null && $waktuKeluar !== null
                    ? (int) $waktuMasuk->diffInMinutes($waktuKeluar, true)
                    : null),
            'alasan' => $row->alasan,
        ];
    }
}
