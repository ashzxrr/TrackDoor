<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\Karyawan;
use App\Queries\AdminScanLogQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminRecapController extends Controller
{
    public function index(Request $request, AdminScanLogQuery $logQuery): View
    {
        $filters = $this->validatedFilters($request);
        $logs = $logQuery->build($filters)
            ->get()
            ->map(fn (object $log): array => $logQuery->format($log));
        $pairedLogs = $logs->filter(fn (array $log): bool => $log['durasi_menit'] !== null);
        $anomalyLogs = $logs->filter(fn (array $log): bool => $log['durasi_menit'] === null);

        return view('admin.recap', [
            'filters' => $filters,
            'bagians' => Karyawan::query()->select('bagian')->distinct()->orderBy('bagian')->pluck('bagian'),
            'gedungs' => Gedung::query()->orderBy('nama_gedung')->get(['id', 'nama_gedung']),
            'summary' => [
                'total_log' => $logs->count(),
                'karyawan_tercatat' => $logs->pluck('nip')->unique()->count(),
                'perjalanan_lengkap' => $pairedLogs->count(),
                'anomali' => $anomalyLogs->count(),
                'rata_rata_durasi' => $this->averageDuration($pairedLogs),
            ],
            'rekapBagian' => $this->groupedRecap($logs, 'bagian'),
            'rekapGedung' => $this->groupedRecap($logs, 'gedung'),
            'alasanTeratas' => $pairedLogs
                ->filter(fn (array $log): bool => $log['alasan'] !== null)
                ->countBy('alasan')
                ->sortDesc()
                ->take(5),
            'durasiTerpanjang' => $pairedLogs
                ->sortByDesc('durasi_menit')
                ->take(5)
                ->values(),
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $logs
     * @return Collection<int, array{label: string, total_log: int, perjalanan_lengkap: int, anomali: int, rata_rata_durasi: ?int}>
     */
    private function groupedRecap(Collection $logs, string $key): Collection
    {
        return $logs
            ->groupBy($key)
            ->map(function (Collection $group, string $label): array {
                $pairedLogs = $group->filter(fn (array $log): bool => $log['durasi_menit'] !== null);

                return [
                    'label' => $label,
                    'total_log' => $group->count(),
                    'perjalanan_lengkap' => $pairedLogs->count(),
                    'anomali' => $group->count() - $pairedLogs->count(),
                    'rata_rata_durasi' => $this->averageDuration($pairedLogs),
                ];
            })
            ->sortByDesc('total_log')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $logs
     */
    private function averageDuration(Collection $logs): ?int
    {
        if ($logs->isEmpty()) {
            return null;
        }

        return (int) round($logs->avg('durasi_menit'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'bagian' => ['nullable', 'string', 'max:255'],
            'gedung_id' => ['nullable', 'integer', 'exists:gedung,id'],
        ]);
    }
}
