@extends('layouts.app')

@section('title', 'Rekap Analisa - TrackDoor')

@section('content')
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-yellow-400">TrackDoor</p>
                    <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl">Rekap analisa aktivitas</h1>
                    <p class="mt-2 text-sm text-slate-400">Ringkasan pola keluar-masuk karyawan berdasarkan filter yang dipilih.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-12 items-center rounded-xl border border-sky-400/50 px-4 py-3 text-sm font-semibold text-sky-300 transition hover:bg-sky-400/10">Dashboard log</a>
                    <a href="{{ route('admin.karyawan.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-yellow-400/50 px-4 py-3 text-sm font-semibold text-yellow-300 transition hover:bg-yellow-400/10">Data karyawan</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="min-h-12 rounded-xl border border-slate-700 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" type="submit">Logout admin</button>
                    </form>
                </div>
            </header>

            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-4 shadow-xl sm:p-6">
                <form method="GET" action="{{ route('admin.recap') }}" class="grid gap-4 md:grid-cols-5 md:items-end">
                    <div><label for="date_from" class="mb-2 block text-sm font-semibold text-slate-300">Dari tanggal</label><input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"></div>
                    <div><label for="date_to" class="mb-2 block text-sm font-semibold text-slate-300">Sampai tanggal</label><input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"></div>
                    <div><label for="bagian" class="mb-2 block text-sm font-semibold text-slate-300">Bagian</label><select id="bagian" name="bagian" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"><option value="">Semua bagian</option>@foreach ($bagians as $bagian)<option value="{{ $bagian }}" @selected(($filters['bagian'] ?? '') === $bagian)>{{ $bagian }}</option>@endforeach</select></div>
                    <div><label for="gedung_id" class="mb-2 block text-sm font-semibold text-slate-300">Gedung</label><select id="gedung_id" name="gedung_id" class="min-h-12 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400/20"><option value="">Semua gedung</option>@foreach ($gedungs as $gedung)<option value="{{ $gedung->id }}" @selected((string) ($filters['gedung_id'] ?? '') === (string) $gedung->id)>{{ $gedung->nama_gedung }}</option>@endforeach</select></div>
                    <div class="flex gap-2"><button type="submit" class="min-h-12 flex-1 rounded-xl bg-yellow-400 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-yellow-300">Tampilkan</button><a href="{{ route('admin.recap') }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-700 px-4 py-3 text-sm font-semibold text-slate-300 hover:text-white">Reset</a></div>
                </form>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"><p class="text-sm font-semibold text-slate-400">Total log</p><p class="mt-3 text-3xl font-bold text-white">{{ $summary['total_log'] }}</p></article>
                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"><p class="text-sm font-semibold text-slate-400">Karyawan tercatat</p><p class="mt-3 text-3xl font-bold text-white">{{ $summary['karyawan_tercatat'] }}</p></article>
                <article class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-5 shadow-xl"><p class="text-sm font-semibold text-emerald-200">Perjalanan lengkap</p><p class="mt-3 text-3xl font-bold text-emerald-300">{{ $summary['perjalanan_lengkap'] }}</p></article>
                <article class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-5 shadow-xl"><p class="text-sm font-semibold text-rose-200">Perlu ditinjau</p><p class="mt-3 text-3xl font-bold text-rose-300">{{ $summary['anomali'] }}</p></article>
                <article class="rounded-2xl border border-sky-500/30 bg-sky-500/10 p-5 shadow-xl"><p class="text-sm font-semibold text-sky-200">Rata-rata durasi</p><p class="mt-3 text-3xl font-bold text-sky-300">{{ $summary['rata_rata_durasi'] === null ? '-' : $summary['rata_rata_durasi'].' menit' }}</p></article>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                @foreach (['Bagian' => $rekapBagian, 'Gedung' => $rekapGedung] as $judul => $rekap)
                    <article class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                        <div class="border-b border-slate-800 p-5"><h2 class="text-lg font-bold text-white">Rekap per {{ strtolower($judul) }}</h2></div>
                        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-800 text-xs uppercase tracking-wider text-slate-300"><tr><th class="px-4 py-3">{{ $judul }}</th><th class="px-4 py-3">Log</th><th class="px-4 py-3">Lengkap</th><th class="px-4 py-3">Anomali</th><th class="px-4 py-3">Rata-rata</th></tr></thead><tbody class="divide-y divide-slate-800">
                            @forelse ($rekap as $item)
                                <tr class="text-slate-300"><td class="whitespace-nowrap px-4 py-4 font-semibold text-white">{{ $item['label'] }}</td><td class="px-4 py-4">{{ $item['total_log'] }}</td><td class="px-4 py-4 text-emerald-300">{{ $item['perjalanan_lengkap'] }}</td><td class="px-4 py-4 text-rose-300">{{ $item['anomali'] }}</td><td class="whitespace-nowrap px-4 py-4">{{ $item['rata_rata_durasi'] === null ? '-' : $item['rata_rata_durasi'].' menit' }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada data untuk direkap.</td></tr>
                            @endforelse
                        </tbody></table></div>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"><h2 class="text-lg font-bold text-white">Alasan keluar terbanyak</h2><div class="mt-4 space-y-3">@forelse ($alasanTeratas as $alasan => $jumlah)<div class="flex items-center justify-between gap-4 rounded-xl bg-slate-800/70 px-4 py-3"><span class="font-medium text-slate-200">{{ $alasan }}</span><span class="rounded-full bg-yellow-400/15 px-3 py-1 text-sm font-bold text-yellow-300">{{ $jumlah }} kali</span></div>@empty<p class="py-6 text-center text-sm text-slate-400">Belum ada alasan keluar pada data ini.</p>@endforelse</div></article>
                <article class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl"><div class="border-b border-slate-800 p-5"><h2 class="text-lg font-bold text-white">Durasi keluar terlama</h2></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-800 text-xs uppercase tracking-wider text-slate-300"><tr><th class="px-4 py-3">Karyawan</th><th class="px-4 py-3">Bagian</th><th class="px-4 py-3">Durasi</th><th class="px-4 py-3">Alasan</th></tr></thead><tbody class="divide-y divide-slate-800">@forelse ($durasiTerpanjang as $log)<tr class="text-slate-300"><td class="whitespace-nowrap px-4 py-4 font-semibold text-white">{{ $log['nama'] }}</td><td class="px-4 py-4">{{ $log['bagian'] }}</td><td class="whitespace-nowrap px-4 py-4 font-bold text-orange-300">{{ $log['durasi_menit'] }} menit</td><td class="px-4 py-4">{{ $log['alasan'] ?? '-' }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada perjalanan lengkap untuk dianalisa.</td></tr>@endforelse</tbody></table></div></article>
            </section>
        </div>
    </main>
@endsection
