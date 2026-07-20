@component('layouts.kdmp')
@section('title', 'Input Lembur')

@php
    $bulanPendek = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $bulanPanjang = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                     7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
@endphp

<x-ui.toast />

<x-ui.workspace title="Input Lembur" description="Paket lembur petugas kebersihan yang kehadirannya Anda kelola.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="calendar-clock" class="w-4 h-4 text-sky-500"></i>
            {{ $packages->count() }} Paket Lembur
        </div>
    </x-slot:actions>

    @if($packages->isEmpty())
        <x-ui.card padding="md">
            <x-ui.empty-state icon="calendar-off" title="Belum Ada Paket Lembur untuk Diinput"
                description="Paket lembur akan muncul di sini setelah Kabid/Admin menetapkan mode Petugas Kebersihan pada paketnya." />
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @foreach($packages as $item)
                @php $pkg = $item['package']; @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">

                    {{-- Header kartu --}}
                    <div class="px-5 py-4 flex items-center gap-3.5 border-b border-slate-100 bg-gradient-to-r from-sky-50/70 to-white">
                        <span class="w-11 h-11 rounded-xl bg-gradient-to-br from-sky-400 to-sky-600 text-white shadow-sm shadow-sky-200 flex items-center justify-center shrink-0">
                            <i data-lucide="spray-can" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-black text-slate-900 leading-snug truncate">{{ $pkg->nama_paket }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold mt-0.5 truncate">
                                <span class="font-mono text-sky-600">{{ $pkg->id_rup ?? '-' }}</span>
                                <span class="mx-1">&bull;</span>{{ $pkg->subActivity?->nama ?? '-' }}
                            </p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-sky-100 text-sky-700 border border-sky-200 shrink-0">
                            Petugas Kebersihan
                        </span>
                    </div>

                    {{-- Strip 12 bulan (klik untuk langsung ke bulannya) --}}
                    <div class="px-5 pt-4 pb-3">
                        <div class="grid grid-cols-6 sm:grid-cols-12 gap-1.5">
                            @foreach($item['months'] as $m => $status)
                                @php $isCurrent = $m === now()->month; @endphp
                                <a href="{{ route('staf.packages.overtimes.show', [$pkg, $m]) }}"
                                    title="{{ $bulanPanjang[$m] }} — {{ $status === 'terkunci' ? 'terkunci' : ($status === 'terisi' ? 'sudah ada kehadiran' : 'belum ada kehadiran') }}"
                                    class="flex flex-col items-center justify-center h-11 rounded-lg border text-[9.5px] font-extrabold uppercase tracking-wide transition-all hover:-translate-y-0.5 hover:shadow-sm
                                        {{ $status === 'terkunci'
                                            ? 'bg-emerald-500 border-emerald-500 text-white'
                                            : ($status === 'terisi'
                                                ? 'bg-amber-100 border-amber-200 text-amber-800'
                                                : 'bg-slate-50 border-slate-200 text-slate-400 hover:border-sky-300 hover:text-sky-600') }}
                                        {{ $isCurrent ? 'ring-2 ring-sky-400 ring-offset-1' : '' }}">
                                    <span>{{ $bulanPendek[$m] }}</span>
                                    @if($status === 'terkunci')
                                        <i data-lucide="lock" class="w-2.5 h-2.5 mt-0.5"></i>
                                    @elseif($status === 'terisi')
                                        <i data-lucide="check" class="w-2.5 h-2.5 mt-0.5"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5 text-[10px] font-semibold text-slate-400">
                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-amber-200 border border-amber-300 inline-block"></span>ada kehadiran</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-emerald-500 inline-block"></span>terkunci</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-slate-100 border border-slate-200 inline-block"></span>kosong</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm border-2 border-sky-400 inline-block"></span>bulan ini</span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 py-3.5 mt-auto border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                        <p class="text-xs font-bold text-slate-500">
                            {{ $item['bulanTerisi'] }} <span class="font-semibold text-slate-400">bulan terisi</span>
                            @if($item['terkunci'] > 0)
                                <span class="mx-1 text-slate-300">·</span>
                                {{ $item['terkunci'] }} <span class="font-semibold text-slate-400">terkunci</span>
                            @endif
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                data-nama="{{ $pkg->nama_paket }}"
                                data-url="{{ route('staf.packages.overtimes.spj', [$pkg, ':type']) }}"
                                data-locked="{{ implode(',', $item['lockedMonths']) }}"
                                onclick="openSpj(this)"
                                @disabled(count($item['lockedMonths']) === 0)
                                title="{{ count($item['lockedMonths']) === 0 ? 'Belum ada bulan terkunci — SPJ butuh bulan yang sudah dikunci' : 'Cetak dokumen SPJ' }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>Buat SPJ
                            </button>
                            <a href="{{ route('staf.packages.overtimes.show', [$pkg, now()->month]) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-sm shadow-sky-200 transition-colors">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>Kelola Kehadiran
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Buat SPJ (dipakai bersama semua kartu; diisi ulang per paket) --}}
    <div id="spjModal" class="hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupSpj()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/60 flex items-center justify-between">
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-800">Buat SPJ Lembur</h3>
                    <p id="spjNamaPaket" class="text-xs text-slate-500 mt-0.5 truncate"></p>
                </div>
                <button type="button" onclick="tutupSpj()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg font-black">✕</button>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Bulan</label>
                        <select id="spjDari" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Bulan</label>
                        <select id="spjSampai" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"></select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dibuat Oleh (Pegawai Dinas)</label>
                    <select id="spjPembuatId" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Pegawai Pembuat --</option>
                        @foreach($dinasEmployees ?? [] as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nama }} {{ $emp->jabatan ? '('.$emp->jabatan.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-[11px] text-slate-500 leading-relaxed">
                    <p>Hanya bulan yang <b>sudah dikunci</b> yang dapat masuk SPJ — angka dokumen dijamin sama dengan sistem. Seluruh bulan dalam rentang harus terkunci.</p>
                    <p class="mt-1">Rekap jam lembur, tanda terima &amp; kwitansi berisi <b>total gabungan periode</b>.</p>
                </div>
                <div class="space-y-2">
                    <button type="button" onclick="cetakSpj('rekap')"
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">
                        <i data-lucide="table" class="w-4 h-4 text-blue-500"></i>Rekap Jam Lembur <span class="ml-auto text-[10px] text-slate-400 font-bold">gabungan periode</span>
                    </button>
                    <button type="button" onclick="cetakSpj('tanda_terima')"
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-500"></i>Tanda Terima <span class="ml-auto text-[10px] text-slate-400 font-bold">gabungan periode</span>
                    </button>
                    <button type="button" onclick="cetakSpj('kwitansi')"
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">
                        <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>Kwitansi <span class="ml-auto text-[10px] text-slate-400 font-bold">total periode</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const NAMA_BULAN = @json($bulanPanjang);
        let spjUrl = null;

        function openSpj(btn) {
            spjUrl = btn.dataset.url;
            const locked = btn.dataset.locked.split(',').filter(Boolean).map(Number);
            document.getElementById('spjNamaPaket').textContent = btn.dataset.nama;

            ['spjDari', 'spjSampai'].forEach(function (id) {
                const sel = document.getElementById(id);
                sel.innerHTML = '';
                for (let m = 1; m <= 12; m++) {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = NAMA_BULAN[m] + (locked.includes(m) ? '' : ' — belum dikunci');
                    opt.disabled = !locked.includes(m);
                    sel.appendChild(opt);
                }
            });
            // Bawaan: seluruh rentang terkunci yang tersedia.
            document.getElementById('spjDari').value = Math.min(...locked);
            document.getElementById('spjSampai').value = Math.max(...locked);

            const modal = document.getElementById('spjModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function tutupSpj() {
            const modal = document.getElementById('spjModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function cetakSpj(type) {
            const dari = parseInt(document.getElementById('spjDari').value, 10);
            const sampai = parseInt(document.getElementById('spjSampai').value, 10);
            const pembuatId = document.getElementById('spjPembuatId').value;
            if (sampai < dari) { alert('"Sampai Bulan" tidak boleh mendahului "Dari Bulan".'); return; }
            let finalUrl = spjUrl.replace(':type', type) + '?dari=' + dari + '&sampai=' + sampai;
            if (pembuatId) {
                finalUrl += '&pembuat_id=' + pembuatId;
            }
            window.open(finalUrl, '_blank');
        }

        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupSpj(); });
    </script>
</x-ui.workspace>
@endcomponent
