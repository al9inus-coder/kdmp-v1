@component('layouts.kdmp')
    @section('title', 'Detail SPPD')

    @php
        $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
        $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
        $meta = $travelOrder->statusMeta();
        $editable = $travelOrder->isEditableBySubmitter();
        $isSubmitted = $travelOrder->status === \App\Models\TravelOrder::STATUS_SUBMITTED;
        $isApproved = $travelOrder->status === \App\Models\TravelOrder::STATUS_APPROVED;
        $isRejected = $travelOrder->status === \App\Models\TravelOrder::STATUS_REJECTED;
        $isRevision = $travelOrder->status === \App\Models\TravelOrder::STATUS_REVISION;
    @endphp

    <div class="space-y-6">
        <x-ui.toast />

        {{-- Header (tetap: badge lokasi, badge lama perjalanan, badge status) --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                    {{ $travelOrder->tempat_tujuan }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-emerald-500"></i>
                    {{ $days }} hari
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $meta['badge'] }}">
                    <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i>
                    {{ $meta['label'] }}
                </span>
            </div>
            <a href="{{ route('staf.sppd.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Daftar SPPD
            </a>
        </div>

        {{-- Area konten yang di-swap: detail SPPD <-> form SPJ (tanpa pindah URL) --}}
        <div id="travel-order-content">
            @include('staf.travel-orders.partials.detail')
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('travel-order-content');
            if (!container) return;

            function refreshUi() {
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(container);
                }
            }

            // Delegasi klik: Buat SPJ (muat form inline) & Kembali ke Detail.
            container.addEventListener('click', async function (e) {
                const spjBtn = e.target.closest('#btn-buat-spj');
                const backBtn = e.target.closest('#btn-back-detail');

                if (spjBtn) {
                    e.preventDefault();
                    const url = spjBtn.dataset.url;
                    const original = spjBtn.innerHTML;
                    spjBtn.disabled = true;
                    spjBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Memuat...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    try {
                        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        if (!res.ok) throw new Error('Gagal memuat SPJ');
                        container.innerHTML = await res.text();
                        refreshUi();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } catch (err) {
                        spjBtn.disabled = false;
                        spjBtn.innerHTML = original;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        alert('Tidak dapat memuat form SPJ. Silakan coba lagi.');
                    }
                    return;
                }

                if (backBtn) {
                    e.preventDefault();
                    // URL sudah di halaman detail — muat ulang untuk kembali ke tampilan detail.
                    window.location.reload();
                }
            });

            // Submit form SPJ (Simpan / Simpan & Ajukan) secara inline — URL tetap.
            container.addEventListener('submit', async function (e) {
                const form = e.target.closest('#spj-store-form');
                if (!form) return; // form lain (mis. tarik pengajuan) submit normal

                e.preventDefault();
                const btns = form.querySelectorAll('button[type="submit"]');
                btns.forEach(b => b.disabled = true);

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    });

                    if (res.status === 422) {
                        const data = await res.json().catch(() => ({}));
                        const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Data tidak valid.');
                        toast(msg, 'error');
                        btns.forEach(b => b.disabled = false);
                        return;
                    }
                    if (!res.ok) throw new Error('fail');

                    const html = await res.text();
                    const msg = res.headers.get('X-SPJ-Message') || 'Tersimpan.';
                    container.innerHTML = html;
                    refreshUi();
                    toast(msg, 'success');
                } catch (err) {
                    btns.forEach(b => b.disabled = false);
                    toast('Gagal menyimpan. Silakan coba lagi.', 'error');
                }
            });

            // Toast ringan (tanpa dependensi).
            function toast(message, type) {
                const ok = type !== 'error';
                const el = document.createElement('div');
                el.className = 'fixed top-5 right-5 z-[100] flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-bold text-white transition-all duration-300 ' +
                    (ok ? 'bg-emerald-600' : 'bg-rose-600');
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px)';
                el.innerHTML = '<i data-lucide="' + (ok ? 'check-circle-2' : 'alert-triangle') + '" class="w-4 h-4"></i>' +
                    '<span>' + message + '</span>';
                document.body.appendChild(el);
                if (typeof lucide !== 'undefined') lucide.createIcons();
                requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-8px)';
                    setTimeout(() => el.remove(), 300);
                }, 3200);
            }

            document.addEventListener('DOMContentLoaded', refreshUi);
        })();
    </script>
@endcomponent
