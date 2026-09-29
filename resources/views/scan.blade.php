@extends('layouts.app')

@section('title', 'Scan Kehadiran - TrackDoor')

@section('content')
    <main class="mx-auto flex min-h-screen w-full max-w-2xl flex-col gap-5 bg-slate-950 px-4 py-5 sm:px-6">
        <header class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-yellow-400">TrackDoor</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">Scan kehadiran</h1>
                </div>
            <button type="button" class="shrink-0 rounded-lg border border-slate-600 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-yellow-400 hover:text-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-400/50" aria-label="Refresh halaman scan" onclick="showLoadingScreen('Memuat ulang...'); window.location.reload()">
                Refresh
            </button>
        </header>

        <section class="rounded-2xl border border-slate-800 bg-slate-800 p-4 shadow-2xl shadow-black/30 sm:p-6">
                <form id="scan-form" action="{{ route('scan.store') }}" method="POST" data-loading="manual" class="flex flex-col gap-6">
                    @csrf

                    <div id="scanner-status" class="rounded-xl bg-slate-900 px-4 py-3 text-sm text-slate-300" role="status">
                        Arahkan kamera ke barcode karyawan
                    </div>
                    <div id="reader" class="overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-950"></div>

                    <div id="scan-error" class="hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" role="alert"></div>

                    <section id="employee-card" class="hidden rounded-2xl border border-emerald-400/40 bg-emerald-400/10 p-5" aria-live="polite">
                        <div class="flex items-center gap-4">
                            <img id="employee-photo" class="hidden h-20 w-20 shrink-0 rounded-xl border border-emerald-300/40 object-cover" alt="Foto karyawan">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-emerald-300">Karyawan ditemukan</p>
                                <h2 id="employee-name" class="mt-2 text-2xl font-bold text-white"></h2>
                                <p id="employee-nip" class="mt-1 text-base text-slate-300"></p>
                                <p id="employee-section" class="mt-1 text-base text-slate-300"></p>
                            </div>
                        </div>
                    </section>

                    <fieldset id="building-fieldset" class="hidden">
                        <legend class="mb-3 text-base font-bold text-white">Pilih gedung</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @forelse ($gedungs as $gedung)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-600 bg-slate-900 p-4 text-base font-semibold text-slate-100 transition has-[:checked]:border-yellow-400 has-[:checked]:bg-yellow-400/10">
                                    <input class="h-5 w-5 accent-yellow-400" type="radio" name="gedung_id" value="{{ $gedung->id }}" required>
                                    <span>{{ $gedung->nama_gedung }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-rose-600">Belum ada data gedung.</p>
                            @endforelse
                        </div>
                    </fieldset>

                    <fieldset id="type-fieldset" class="hidden">
                        <legend class="mb-3 text-base font-bold text-white">Jenis scan</legend>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" data-type="in" class="type-button min-h-14 rounded-lg border border-emerald-700 bg-emerald-950/30 px-4 py-3 text-base font-semibold text-emerald-300 transition-colors hover:border-emerald-400 hover:bg-emerald-400/10 focus:outline-none focus:ring-2 focus:ring-emerald-400/50" aria-pressed="false">
                                Masuk
                            </button>
                            <button type="button" data-type="out" class="type-button min-h-14 rounded-lg border border-orange-700 bg-orange-950/30 px-4 py-3 text-base font-semibold text-orange-300 transition-colors hover:border-orange-400 hover:bg-orange-400/10 focus:outline-none focus:ring-2 focus:ring-orange-400/50" aria-pressed="false">
                                Keluar
                            </button>
                        </div>
                        <input type="hidden" name="tipe" id="tipe" value="">
                    </fieldset>

                    <div id="reason-field" class="hidden">
                        <label for="alasan_id" class="mb-2 block text-base font-bold text-white">Alasan keluar</label>
                        <select id="alasan_id" name="alasan_id" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-4 py-4 text-base text-white focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30">
                            <option value="">Pilih alasan</option>
                            @foreach ($alasanKeluars as $alasan)
                                <option value="{{ $alasan->id }}">{{ $alasan->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="karyawan_id" id="karyawan_id" value="">
                    <button id="save-button" type="submit" class="min-h-16 rounded-2xl bg-yellow-400 px-5 py-4 text-lg font-bold text-slate-950 shadow-lg shadow-yellow-400/20 transition hover:bg-yellow-300 disabled:cursor-not-allowed disabled:bg-slate-600 disabled:text-slate-400 disabled:shadow-none" disabled>
                        Simpan
                    </button>
                </form>
        </section>
    </main>

        <div id="toast" class="pointer-events-none fixed bottom-5 left-1/2 hidden -translate-x-1/2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-xl" role="status">
            Data tersimpan
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('scan-form');
                const status = document.getElementById('scanner-status');
                const error = document.getElementById('scan-error');
                const employeeCard = document.getElementById('employee-card');
                const employeePhoto = document.getElementById('employee-photo');
                const employeeName = document.getElementById('employee-name');
                const employeeNip = document.getElementById('employee-nip');
                const employeeSection = document.getElementById('employee-section');
                const employeeId = document.getElementById('karyawan_id');
                const buildingFieldset = document.getElementById('building-fieldset');
                const typeFieldset = document.getElementById('type-fieldset');
                const typeInput = document.getElementById('tipe');
                const reasonField = document.getElementById('reason-field');
                const reasonInput = document.getElementById('alasan_id');
                const saveButton = document.getElementById('save-button');
                const toast = document.getElementById('toast');
                const lookupUrl = @json(route('scan.lookup'));
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const scanner = new Html5Qrcode('reader');
                let scannerRunning = false;
                let processingScan = false;

                const showError = (message) => {
                    error.textContent = message;
                    error.classList.remove('hidden');
                };

                const clearError = () => {
                    error.textContent = '';
                    error.classList.add('hidden');
                };

                const stopScanner = async () => {
                    if (!scannerRunning) {
                        return;
                    }

                    await scanner.stop();
                    scannerRunning = false;
                };

                const startScanner = async () => {
                    if (scannerRunning) {
                        return;
                    }

                    try {
                        clearError();
                        status.textContent = 'Arahkan kamera ke barcode karyawan';
                        await scanner.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: { width: 250, height: 150 } },
                            handleBarcode,
                            () => {},
                        );
                        scannerRunning = true;
                    } catch (cameraError) {
                        showError('Kamera tidak dapat diaktifkan. Pastikan izin kamera sudah diberikan.');
                        status.textContent = 'Kamera belum aktif.';
                    }
                };

                const handleBarcode = async (barcodeValue) => {
                    if (processingScan) {
                        return;
                    }

                    processingScan = true;
                    await stopScanner();
                    status.textContent = 'Mencari data karyawan...';
                    showLoadingScreen('Mencari data karyawan...');

                    try {
                        const response = await fetch(lookupUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ barcode_value: barcodeValue }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Barcode tidak dikenali');
                        }

                        employeeId.value = data.karyawan_id;
                        employeeName.textContent = data.nama;
                        employeeNip.textContent = `NIP: ${data.nip}`;
                        employeeSection.textContent = data.bagian;
                        employeePhoto.classList.toggle('hidden', !data.foto_url);
                        employeePhoto.src = data.foto_url || '';
                        employeeCard.classList.remove('hidden');
                        buildingFieldset.classList.remove('hidden');
                        typeFieldset.classList.remove('hidden');
                        updateSaveButton();
                        status.textContent = 'Pilih gedung dan jenis scan.';
                        clearError();
                    } catch (lookupError) {
                        employeeId.value = '';
                        employeePhoto.classList.add('hidden');
                        employeePhoto.removeAttribute('src');
                        employeeCard.classList.add('hidden');
                        buildingFieldset.classList.add('hidden');
                        typeFieldset.classList.add('hidden');
                        updateSaveButton();
                        showError(lookupError.message === 'Barcode tidak dikenali' ? lookupError.message : 'Barcode tidak dikenali');
                        status.textContent = 'Silakan coba scan barcode lagi.';
                        await startScanner();
                    } finally {
                        hideLoadingScreen();
                        processingScan = false;
                    }
                };

                const updateSaveButton = () => {
                    saveButton.disabled = !employeeId.value
                        || !document.querySelector('input[name="gedung_id"]:checked')
                        || !typeInput.value
                        || (typeInput.value === 'out' && !reasonInput.value);
                };

                document.querySelectorAll('input[name="gedung_id"]').forEach((radio) => {
                    radio.addEventListener('change', updateSaveButton);
                });

                document.querySelectorAll('.type-button').forEach((button) => {
                    button.addEventListener('click', () => {
                        typeInput.value = button.dataset.type;
                        document.querySelectorAll('.type-button').forEach((item) => {
                            const active = item === button;
                            item.setAttribute('aria-pressed', active ? 'true' : 'false');
                            item.classList.toggle('border-emerald-300', active && button.dataset.type === 'in');
                            item.classList.toggle('bg-emerald-400/20', active && button.dataset.type === 'in');
                            item.classList.toggle('border-orange-300', active && button.dataset.type === 'out');
                            item.classList.toggle('bg-orange-400/20', active && button.dataset.type === 'out');
                        });
                        reasonField.classList.toggle('hidden', typeInput.value !== 'out');
                        reasonInput.required = typeInput.value === 'out';
                        updateSaveButton();
                    });
                });

                reasonInput.addEventListener('change', updateSaveButton);

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearError();

                    if (!employeeId.value) {
                        showError('Scan barcode karyawan terlebih dahulu.');
                        return;
                    }

                    if (!document.querySelector('input[name="gedung_id"]:checked')) {
                        showError('Pilih gedung terlebih dahulu.');
                        return;
                    }

                    if (!typeInput.value) {
                        showError('Pilih jenis scan terlebih dahulu.');
                        return;
                    }

                    if (typeInput.value === 'out' && !reasonInput.value) {
                        showError('Pilih alasan keluar terlebih dahulu.');
                        return;
                    }

                    saveButton.disabled = true;
                    showLoadingScreen('Menyimpan data scan...');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: new FormData(form),
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Data gagal disimpan');
                        }

                        toast.classList.remove('hidden');
                        window.setTimeout(() => toast.classList.add('hidden'), 2200);
                        typeInput.value = '';
                        reasonInput.value = '';
                        reasonInput.required = false;
                        reasonField.classList.add('hidden');
                        employeeId.value = '';
                        employeePhoto.classList.add('hidden');
                        employeePhoto.removeAttribute('src');
                        employeeCard.classList.add('hidden');
                        buildingFieldset.classList.add('hidden');
                        typeFieldset.classList.add('hidden');
                        document.querySelectorAll('input[name="gedung_id"]').forEach((radio) => {
                            radio.checked = false;
                        });
                        document.querySelectorAll('.type-button').forEach((item) => {
                            item.setAttribute('aria-pressed', 'false');
                            item.classList.remove('border-emerald-300', 'bg-emerald-400/20', 'border-orange-300', 'bg-orange-400/20');
                        });
                        updateSaveButton();
                        status.textContent = 'Arahkan kamera ke barcode karyawan';
                        await startScanner();
                    } catch (storeError) {
                        showError(storeError.message);
                        updateSaveButton();
                    } finally {
                        hideLoadingScreen();
                    }
                });

                employeePhoto.addEventListener('error', () => {
                    employeePhoto.classList.add('hidden');
                });

                startScanner();
            });
        </script>
@endsection

@push('head')
    <script src="https://unpkg.com/html5-qrcode" defer></script>
@endpush