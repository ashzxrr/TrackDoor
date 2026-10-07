@extends('layouts.app')

@section('title', 'Dashboard Admin - TrackDoor')

@section('content')
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-yellow-400">TrackDoor</p>
                    <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl">Dashboard log scan</h1>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.recap') }}" class="inline-flex min-h-12 items-center rounded-xl border border-sky-400/50 px-4 py-3 text-sm font-semibold text-sky-300 transition hover:bg-sky-400/10">Rekap analisa</a>
                    <a href="{{ route('admin.karyawan.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-yellow-400/50 px-4 py-3 text-sm font-semibold text-yellow-300 transition hover:bg-yellow-400/10">Data karyawan</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="min-h-12 rounded-xl border border-slate-700 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" type="submit">Logout admin</button>
                    </form>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-4 shadow-xl sm:p-6">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="grid gap-4 md:grid-cols-5 md:items-end">
                    <div>
                        <label for="date_from" class="mb-2 block text-sm font-semibold text-slate-300">Dari tanggal</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20">
                    </div>
                    <div>
                        <label for="date_to" class="mb-2 block text-sm font-semibold text-slate-300">Sampai tanggal</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20">
                    </div>
                    <div>
                        <label for="bagian" class="mb-2 block text-sm font-semibold text-slate-300">Bagian</label>
                        <select id="bagian" name="bagian" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20">
                            <option value="">Semua bagian</option>
                            @foreach ($bagians as $bagian)
                                <option value="{{ $bagian }}" @selected(($filters['bagian'] ?? '') === $bagian)>{{ $bagian }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="gedung_id" class="mb-2 block text-sm font-semibold text-slate-300">Gedung</label>
                        <select id="gedung_id" name="gedung_id" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20">
                            <option value="">Semua gedung</option>
                            @foreach ($gedungs as $gedung)
                                <option value="{{ $gedung->id }}" @selected((string) ($filters['gedung_id'] ?? '') === (string) $gedung->id)>{{ $gedung->nama_gedung }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="min-h-12 flex-1 rounded-xl bg-yellow-400 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-yellow-300">Filter</button>
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-700 px-4 py-3 text-sm font-semibold text-slate-300 hover:text-white">Reset</a>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="flex flex-col gap-3 border-b border-slate-800 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <h2 class="text-lg font-bold text-white">Riwayat keluar-masuk</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $logs->total() }} data ditemukan</p>
                    </div>
                    <a href="{{ route('admin.dashboard.export', request()->query()) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-emerald-400">Export Excel</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="sticky top-0 bg-slate-800 text-xs uppercase tracking-wider text-slate-300">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-4">NIP</th><th class="whitespace-nowrap px-4 py-4">Nama</th><th class="whitespace-nowrap px-4 py-4">Bagian</th><th class="whitespace-nowrap px-4 py-4">Gedung</th><th class="whitespace-nowrap px-4 py-4">Tipe</th><th class="whitespace-nowrap px-4 py-4">Waktu Scan</th><th class="whitespace-nowrap px-4 py-4">Waktu Keluar</th><th class="whitespace-nowrap px-4 py-4">Waktu Masuk</th><th class="whitespace-nowrap px-4 py-4">Durasi</th><th class="whitespace-nowrap px-4 py-4">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($logs as $log)
                                <tr class="text-slate-300 hover:bg-slate-800/60">
                                    <td class="whitespace-nowrap px-4 py-4 font-mono text-xs text-slate-400">{{ $log['nip'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-white">{{ $log['nama'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4">{{ $log['bagian'] }}</td><td class="whitespace-nowrap px-4 py-4">{{ $log['gedung'] }}</td><td class="whitespace-nowrap px-4 py-4 font-bold {{ $log['tipe'] === 'out' ? 'text-orange-300' : 'text-emerald-300' }}">{{ strtoupper($log['tipe']) }}</td><td class="whitespace-nowrap px-4 py-4">{{ $log['waktu_scan']->format('d/m/Y H:i') }}</td><td class="whitespace-nowrap px-4 py-4">{{ $log['waktu_keluar']?->format('d/m/Y H:i') ?? '-' }}</td><td class="whitespace-nowrap px-4 py-4">{{ $log['waktu_masuk']?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        @if ($log['durasi_menit'] === null)
                                            <span class="rounded-full bg-rose-500/20 px-3 py-1 text-xs font-bold text-rose-300">Anomali - masuk tanpa keluar</span>
                                        @else
                                            {{ $log['durasi_menit'] }} menit
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">{{ $log['alasan'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-12 text-center text-slate-400">Belum ada log scan yang sesuai filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())<div class="border-t border-slate-800 p-4">{{ $logs->links() }}</div>@endif
            </section>
        </div>
    </main>
@endsection
