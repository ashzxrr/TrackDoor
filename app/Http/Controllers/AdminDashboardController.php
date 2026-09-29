<?php

namespace App\Http\Controllers;

use App\Exports\AdminDashboardExport;
use App\Models\Gedung;
use App\Models\Karyawan;
use App\Queries\AdminScanLogQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminDashboardController extends Controller
{
    public function index(Request $request, AdminScanLogQuery $logQuery): View
    {
        $filters = $this->validatedFilters($request);
        $logs = $logQuery->build($filters)->paginate(25)->withQueryString();
        $logs->through(fn (object $log): array => $logQuery->format($log));

        return view('admin.dashboard', [
            'logs' => $logs,
            'bagians' => Karyawan::query()->select('bagian')->distinct()->orderBy('bagian')->pluck('bagian'),
            'gedungs' => Gedung::query()->orderBy('nama_gedung')->get(['id', 'nama_gedung']),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, AdminScanLogQuery $logQuery): BinaryFileResponse
    {
        return Excel::download(
            new AdminDashboardExport($this->validatedFilters($request), $logQuery),
            'trackdoor-log-scan.xlsx',
        );
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
