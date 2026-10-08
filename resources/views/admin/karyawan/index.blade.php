@extends('layouts.app')

@section('title', 'Data Karyawan - TrackDoor')

@section('content')
    @php
        $bagianList = $karyawans->pluck('bagian')->filter()->unique()->sort()->values();
        $withPhoto = $karyawans->filter(fn ($karyawan) => $karyawan->foto_path !== null)->count();
    @endphp

    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-yellow-400 transition hover:text-yellow-300">&larr; Dashboard</a><h1 class="mt-2 text-3xl font-bold tracking-tight text-white">Data karyawan</h1><p class="mt-1 text-sm text-slate-400">Kelola identitas dan foto karyawan untuk proses scan.</p></div>
                <div class="flex gap-2"><a href="{{ route('admin.karyawan.create') }}" class="inline-flex min-h-11 items-center rounded-xl bg-yellow-400 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-yellow-300">+ Tambah karyawan</a><form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="min-h-11 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-500 hover:text-white" type="submit">Logout</button></form></div>
            </header>

            @if (session('success'))<div role="status" class="rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('success') }}</div>@endif
            @if ($errors->any())<div role="alert" class="rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-300">{{ $errors->first() }}</div>@endif

            <section class="grid gap-3 sm:grid-cols-3"><div class="rounded-2xl border border-slate-800 bg-slate-900 p-4"><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Total karyawan</p><p id="totalCount" class="mt-2 text-2xl font-bold text-white">{{ $karyawans->count() }}</p></div><div class="rounded-2xl border border-slate-800 bg-slate-900 p-4"><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Sudah ada foto</p><p id="photoCount" class="mt-2 text-2xl font-bold text-emerald-300">{{ $withPhoto }}</p></div><div class="rounded-2xl border border-slate-800 bg-slate-900 p-4"><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Dipilih</p><p id="selectedCount" class="mt-2 text-2xl font-bold text-yellow-300">0</p></div></section>

            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="flex flex-col gap-3 border-b border-slate-800 p-4 sm:flex-row sm:items-end sm:p-5"><div class="min-w-0 flex-1"><label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-slate-500">Cari cepat</label><input id="search" type="search" placeholder="Nama, NIP, bagian, atau barcode..." class="min-h-11 w-full rounded-xl border border-slate-700 bg-slate-800 px-4 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/20"></div><div class="sm:w-56"><label for="filterBagian" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-slate-500">Bagian</label><select id="filterBagian" class="min-h-11 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white outline-none focus:border-yellow-400"><option value="">Semua bagian</option>@foreach ($bagianList as $bagian)<option value="{{ $bagian }}">{{ $bagian }}</option>@endforeach</select></div><div class="sm:w-44"><label for="filterFoto" class="mb-2 block text-xs font-semibold uppercase tracking-widest text-slate-500">Foto</label><select id="filterFoto" class="min-h-11 w-full rounded-xl border border-slate-700 bg-slate-800 px-3 text-sm text-white outline-none focus:border-yellow-400"><option value="">Semua status</option><option value="ada">Sudah ada</option><option value="belum">Belum ada</option></select></div><button type="button" id="btnUpload" disabled class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-400 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-emerald-300 disabled:cursor-not-allowed disabled:opacity-40">Upload foto <span id="uploadCount" class="ml-1"></span></button></div>

                <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-800/80 text-xs uppercase tracking-widest text-slate-400"><tr><th class="w-12 px-4 py-4"><input type="checkbox" id="checkAll" class="h-4 w-4 rounded border-slate-600 bg-slate-800 accent-yellow-400"></th><th class="px-4 py-4">Karyawan</th><th class="px-4 py-4">NIP</th><th class="px-4 py-4">Bagian</th><th class="px-4 py-4">Barcode</th><th class="px-4 py-4 text-right">Aksi</th></tr></thead><tbody id="karyawanRows" class="divide-y divide-slate-800">
                    @forelse ($karyawans as $karyawan)
                        @php $fotoUrl = $karyawan->foto_path ? asset('storage/'.$karyawan->foto_path) : ''; @endphp
                        <tr class="karyawan-row cursor-pointer text-slate-300 transition hover:bg-slate-800/60" data-id="{{ $karyawan->id }}" data-search="{{ strtolower($karyawan->nip.' '.$karyawan->nama.' '.$karyawan->bagian.' '.$karyawan->barcode_value) }}" data-bagian="{{ strtolower($karyawan->bagian) }}" data-photo="{{ $fotoUrl }}" data-upload-url="{{ route('admin.karyawan.photo', $karyawan) }}"><td class="px-4 py-3"><input type="checkbox" class="karyawan-check h-4 w-4 rounded border-slate-600 bg-slate-800 accent-yellow-400" aria-label="Pilih {{ $karyawan->nama }}"></td><td class="px-4 py-3"><div class="flex items-center gap-3"><div class="photo-thumb flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-700 bg-slate-800 text-xs font-bold text-slate-500">@if ($fotoUrl)<img src="{{ $fotoUrl }}" alt="Foto {{ $karyawan->nama }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($karyawan->nama, 0, 1)) }}@endif</div><span class="font-semibold text-white">{{ $karyawan->nama }}</span></div></td><td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-400">{{ $karyawan->nip }}</td><td class="whitespace-nowrap px-4 py-3">{{ $karyawan->bagian }}</td><td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-400">{{ $karyawan->barcode_value }}</td><td class="whitespace-nowrap px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-bold text-slate-200 transition hover:border-yellow-400 hover:text-yellow-300">Edit</a><form method="POST" action="{{ route('admin.karyawan.destroy', $karyawan) }}" onsubmit="return confirm('Hapus data karyawan ini?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-rose-500/40 px-3 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500/10">Hapus</button></form></div></td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">Belum ada data karyawan.</td></tr>
                    @endforelse
                </tbody></table></div>
                <div class="flex flex-col gap-3 border-t border-slate-800 p-4 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between"><span id="paginationInfo"></span><div id="paginationButtons" class="flex gap-1"></div></div>
            </section>
        </div>
    </main>

    <div id="photoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="photoModalTitle"><div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl"><div class="flex items-center justify-between border-b border-slate-800 px-5 py-4"><div><p class="text-xs font-semibold uppercase tracking-widest text-emerald-300">Upload foto</p><h2 id="photoModalTitle" class="mt-1 font-semibold text-white"></h2><p id="photoProgress" class="mt-1 text-xs text-slate-500"></p></div><button type="button" id="closePhotoModal" class="text-2xl leading-none text-slate-400 hover:text-white" aria-label="Tutup">&times;</button></div><div id="photoSlide" class="p-5" data-touch-start=""><div class="flex justify-center"><div id="photoPreview" class="flex h-64 w-64 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-600 bg-slate-800 text-sm text-slate-500">Belum ada foto</div></div><label class="mt-5 block cursor-pointer rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-center text-sm font-semibold text-slate-200 transition hover:border-emerald-400"><span>Pilih foto dari perangkat</span><input id="photoInput" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"></label><p id="photoStatus" class="mt-3 min-h-5 text-center text-xs text-slate-400"></p></div><div class="flex items-center justify-between border-t border-slate-800 px-5 py-4"><button type="button" id="prevPhoto" class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white">Sebelumnya</button><button type="button" id="nextPhoto" class="rounded-lg bg-emerald-400 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-emerald-300">Simpan & berikutnya</button></div></div></div>

    <script>
        const rows = Array.from(document.querySelectorAll('.karyawan-row'));
        const selectedIds = new Set();
        const pendingFiles = new Map();
        const photoUrls = new Map(rows.map(row => [row.dataset.id, row.dataset.photo]));
        const rowsPerPage = 25;
        let filteredRows = [...rows]; let currentPage = 1; let photoRows = []; let photoIndex = 0; let photoSaving = false;
        const get = id => document.getElementById(id);

        function compressImage(file, { maxDimension = 1200, quality = 0.8 } = {}) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => {
                    const img = new Image();
                    img.onload = () => {
                        const scale = Math.min(1, maxDimension / Math.max(img.width, img.height));
                        const width = Math.max(1, Math.round(img.width * scale));
                        const height = Math.max(1, Math.round(img.height * scale));
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;

                        const context = canvas.getContext('2d');
                        context.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(blob => {
                            if (!blob) {
                                reject(new Error('Foto gagal dikompres. Coba foto lain.'));
                                return;
                            }

                            const compressed = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            });
                            resolve(compressed);
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = () => reject(new Error('Format foto tidak valid.'));
                    img.src = reader.result;
                };
                reader.onerror = () => reject(new Error('Foto tidak dapat dibaca.'));
                reader.readAsDataURL(file);
            });
        }

        function applyFilters() { const query = get('search').value.toLowerCase().trim(); const bagian = get('filterBagian').value.toLowerCase(); const foto = get('filterFoto').value; filteredRows = rows.filter(row => { const hasPhoto = Boolean(photoUrls.get(row.dataset.id)); return (!query || row.dataset.search.includes(query)) && (!bagian || row.dataset.bagian === bagian) && (!foto || (foto === 'ada' ? hasPhoto : !hasPhoto)); }); renderPage(1); }
        function renderPage(page) { const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage)); currentPage = Math.min(Math.max(page, 1), totalPages); const start = (currentPage - 1) * rowsPerPage; rows.forEach(row => { const visible = filteredRows.slice(start, start + rowsPerPage).includes(row); row.classList.toggle('hidden', !visible); const check = row.querySelector('.karyawan-check'); check.checked = selectedIds.has(row.dataset.id); row.classList.toggle('bg-yellow-400/10', check.checked); }); const first = filteredRows.length ? start + 1 : 0; const last = Math.min(start + rowsPerPage, filteredRows.length); get('paginationInfo').textContent = `Menampilkan ${first}-${last} dari ${filteredRows.length} karyawan`; renderPagination(totalPages); updateSelectionUi(); }
        function renderPagination(totalPages) { const container = get('paginationButtons'); container.innerHTML = ''; if (totalPages <= 1) return; [['Sebelumnya', currentPage - 1], ...Array.from({ length: totalPages }, (_, index) => [String(index + 1), index + 1]), ['Berikutnya', currentPage + 1]].forEach(([label, page]) => { if (page < 1 || page > totalPages) return; const button = document.createElement('button'); button.type = 'button'; button.textContent = label; button.className = page === currentPage ? 'rounded-lg bg-yellow-400 px-3 py-2 text-xs font-bold text-slate-950' : 'rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:text-white'; button.onclick = () => renderPage(page); container.appendChild(button); }); }
        function updateSelectionUi() { const count = selectedIds.size; get('selectedCount').textContent = count; get('uploadCount').textContent = count ? `(${count})` : ''; get('btnUpload').disabled = count === 0; const visibleChecks = filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).map(row => row.querySelector('.karyawan-check')); get('checkAll').checked = visibleChecks.length > 0 && visibleChecks.every(check => selectedIds.has(check.closest('tr').dataset.id)); get('checkAll').indeterminate = visibleChecks.some(check => selectedIds.has(check.closest('tr').dataset.id)) && !get('checkAll').checked; }
        function setRowSelection(row, checked) { checked ? selectedIds.add(row.dataset.id) : selectedIds.delete(row.dataset.id); row.classList.toggle('bg-yellow-400/10', checked); updateSelectionUi(); }
        function renderPhotoSlide() { const row = photoRows[photoIndex]; if (!row) return; const name = row.querySelector('td:nth-child(2) span').textContent.trim(); get('photoModalTitle').textContent = name; get('photoProgress').textContent = `Foto ${photoIndex + 1} dari ${photoRows.length}`; get('photoInput').value = ''; const url = pendingFiles.has(row.dataset.id) ? URL.createObjectURL(pendingFiles.get(row.dataset.id)) : photoUrls.get(row.dataset.id); get('photoPreview').innerHTML = url ? `<img src="${url}" alt="Preview foto ${name}" class="h-full w-full object-cover">` : 'Belum ada foto'; get('prevPhoto').disabled = photoIndex === 0 || photoSaving; get('nextPhoto').textContent = photoIndex === photoRows.length - 1 ? 'Simpan & selesai' : 'Simpan & berikutnya'; get('nextPhoto').disabled = photoSaving; }
        async function saveCurrentPhoto() { const row = photoRows[photoIndex]; const file = pendingFiles.get(row.dataset.id); if (!file) return true; photoSaving = true; get('photoStatus').textContent = 'Mengompres dan mengirim foto...'; const safeFile = await compressImage(file, { maxDimension: 1200, quality: 0.8 }); const body = new FormData(); body.append('foto', safeFile, safeFile.name); try { const response = await fetch(row.dataset.uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', Accept: 'application/json' } }); const data = await response.json().catch(() => ({})); if (!response.ok) { const validationMessage = data.errors?.foto?.[0] || data.message || `Upload gagal (HTTP ${response.status})`; throw new Error(validationMessage); } photoUrls.set(row.dataset.id, data.url); row.dataset.photo = data.url; pendingFiles.delete(row.dataset.id); const thumb = row.querySelector('.photo-thumb'); thumb.innerHTML = `<img src="${data.url}" alt="Foto ${row.querySelector('td:nth-child(2) span').textContent.trim()}" class="h-full w-full object-cover">`; get('photoCount').textContent = rows.filter(item => photoUrls.get(item.dataset.id)).length; get('photoStatus').textContent = 'Foto berhasil disimpan.'; return true; } catch (error) { get('photoStatus').textContent = error.message || 'Foto gagal disimpan. Coba lagi.'; return false; } finally { photoSaving = false; renderPhotoSlide(); } }
        async function nextPhoto() { if (!(await saveCurrentPhoto())) return; if (photoIndex < photoRows.length - 1) { photoIndex++; renderPhotoSlide(); } else closePhotoModal(); }
        function openPhotoModal() { photoRows = rows.filter(row => selectedIds.has(row.dataset.id)); if (!photoRows.length) return; photoIndex = 0; get('photoStatus').textContent = ''; get('photoModal').classList.remove('hidden'); get('photoModal').classList.add('flex'); renderPhotoSlide(); }
        function closePhotoModal() { if (photoSaving) return; get('photoModal').classList.add('hidden'); get('photoModal').classList.remove('flex'); }

        rows.forEach(row => {
            const check = row.querySelector('.karyawan-check');
            check.addEventListener('change', event => setRowSelection(row, event.target.checked));
            row.addEventListener('click', event => {
                if (event.target.closest('a, button, input, select, textarea, form, label')) return;
                check.checked = !check.checked;
                setRowSelection(row, check.checked);
            });
        });
        get('checkAll').addEventListener('change', event => filteredRows.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage).forEach(row => { const check = row.querySelector('.karyawan-check'); check.checked = event.target.checked; setRowSelection(row, event.target.checked); }));
        ['search', 'filterBagian', 'filterFoto'].forEach(id => get(id).addEventListener(id === 'search' ? 'input' : 'change', applyFilters));
        get('btnUpload').addEventListener('click', openPhotoModal); get('closePhotoModal').addEventListener('click', closePhotoModal); get('nextPhoto').addEventListener('click', nextPhoto); get('prevPhoto').addEventListener('click', async () => { if (photoIndex > 0 && await saveCurrentPhoto()) { photoIndex--; renderPhotoSlide(); } });
        get('photoInput').addEventListener('change', async event => { if (!event.target.files[0]) return; const file = event.target.files[0]; get('photoStatus').textContent = 'Mempersiapkan foto...'; try { const compressed = await compressImage(file, { maxDimension: 1200, quality: 0.8 }); pendingFiles.set(photoRows[photoIndex].dataset.id, compressed); get('photoStatus').textContent = ''; renderPhotoSlide(); } catch (error) { get('photoStatus').textContent = error.message || 'Foto tidak dapat diproses.'; } });
        get('photoSlide').addEventListener('touchstart', event => get('photoSlide').dataset.touchStart = event.changedTouches[0].screenX, { passive: true }); get('photoSlide').addEventListener('touchend', event => { const delta = event.changedTouches[0].screenX - Number(get('photoSlide').dataset.touchStart); if (delta < -50) nextPhoto(); if (delta > 50 && photoIndex > 0) get('prevPhoto').click(); }, { passive: true });
        document.addEventListener('keydown', event => { if (event.key === 'Escape') closePhotoModal(); if (get('photoModal').classList.contains('flex') && event.key === 'ArrowRight') nextPhoto(); if (get('photoModal').classList.contains('flex') && event.key === 'ArrowLeft') get('prevPhoto').click(); });
        renderPage(1);
    </script>
@endsection