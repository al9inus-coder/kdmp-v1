@component('layouts.kdmp')
@section('title', 'Detail Perjalanan Dinas')

@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
    $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
    $nights = max(0, $days - 1);
    $totalPerkiraan = 0;
    $totalRampung = 0;

    // Konteks review pengajuan SPPD dari staf.
    $isSppdSubmission = filled($travelOrder->created_by);
    $meta = $travelOrder->statusMeta();
    $isDiajukan = $travelOrder->status === \App\Models\TravelOrder::STATUS_SUBMITTED;

    // Konteks review SPJ (biaya rampung).
    $spjMeta = $travelOrder->spjStatusMeta();
    $isSpjDiajukan = $travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_SUBMITTED;
@endphp

<div class="space-y-6" x-data="{ showRevisiSpj: false }">
    <x-ui.toast />

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>
                {{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                {{ $travelOrder->tempat_tujuan }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                {{ $days }} hari
            </span>
            @if($isSppdSubmission)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $meta['badge'] }}">
                    <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i>
                    {{ $meta['label'] }}
                </span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @unless($isSppdSubmission)
                <a href="{{ route('kabid.packages.travel-orders.edit', [$package, $travelOrder]) }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-white bg-slate-900 rounded-lg hover:bg-black shadow-sm">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    Edit
                </a>
            @endunless
            <a href="{{ route('kabid.procurement-packages.show', $package) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </div>

    {{-- Info: review SPPD dilakukan di menu Pengajuan SPPD --}}
    @if($isDiajukan)
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-5 h-5"></i>
                </span>
                <div>
                    <p class="font-bold text-slate-800 text-sm">Pengajuan SPPD Menunggu Ditinjau</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($travelOrder->creator) Diajukan oleh <span class="font-semibold text-slate-700">{{ $travelOrder->creator->name }}</span> @endif
                        {{ $travelOrder->submitted_at ? '· ' . $travelOrder->submitted_at->locale('id')->diffForHumans() : '' }}. Persetujuan dilakukan di menu Pengajuan SPPD.
                    </p>
                </div>
            </div>
            <a href="{{ route('kabid.sppd.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors shrink-0">
                <i data-lucide="plane" class="w-4 h-4"></i> Ke Pengajuan SPPD
            </a>
        </div>
    @elseif($isSppdSubmission && $travelOrder->catatan_review)
        {{-- Sudah ditinjau: tampilkan catatan --}}
        <div class="rounded-2xl border p-4 flex items-start gap-3 {{ $travelOrder->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'border-rose-200 bg-rose-50/50' : 'border-amber-200 bg-amber-50/50' }}">
            <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 shrink-0 mt-0.5 {{ $travelOrder->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'text-rose-500' : 'text-amber-500' }}"></i>
            <div>
                <p class="text-sm font-bold text-slate-800">Catatan tinjauan ({{ $meta['label'] }})</p>
                <p class="text-sm text-slate-600 mt-0.5">{{ $travelOrder->catatan_review }}</p>
            </div>
        </div>
    @endif

    {{-- Panel review SPJ (biaya rampung) — muncul saat SPJ diajukan --}}
    @if($isSpjDiajukan)
        <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="receipt-text" class="w-5 h-5"></i>
                </span>
                <div>
                    <p class="font-bold text-slate-800 text-sm">SPJ (Biaya Rampung) Menunggu Persetujuan Anda</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Diajukan {{ $travelOrder->spj_submitted_at ? $travelOrder->spj_submitted_at->locale('id')->diffForHumans() : '' }}. Periksa rincian biaya rampung di bawah sebelum menyetujui.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" @click="showRevisiSpj = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-amber-700 bg-white border border-amber-200 rounded-xl hover:bg-amber-50 shadow-sm transition-colors">
                    <i data-lucide="file-warning" class="w-4 h-4"></i> Minta Revisi
                </button>
                <form method="POST" action="{{ route('kabid.packages.travel-orders.spj.approve', [$package, $travelOrder]) }}"
                    onsubmit="return confirm('Setujui SPJ (biaya rampung) ini?');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Setujui SPJ
                    </button>
                </form>
            </div>
        </div>

        {{-- Modal Revisi SPJ --}}
        <div x-show="showRevisiSpj" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="showRevisiSpj = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRevisiSpj = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <form method="POST" action="{{ route('kabid.packages.travel-orders.spj.revise', [$package, $travelOrder]) }}">
                    @csrf
                    <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/60 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600"><i data-lucide="file-warning" class="w-4 h-4"></i></span>
                        <h3 class="font-bold text-slate-800">Minta Revisi SPJ</h3>
                    </div>
                    <div class="p-5">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan Revisi <span class="text-rose-500">*</span></label>
                        <textarea name="spj_catatan" rows="4" required placeholder="Jelaskan biaya rampung yang perlu diperbaiki..."
                            class="w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm"></textarea>
                    </div>
                    <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                        <button type="button" @click="showRevisiSpj = false" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl shadow-sm shadow-amber-200 transition-colors whitespace-nowrap">
                            <i data-lucide="send" class="w-4 h-4 shrink-0"></i> Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @elseif(filled($travelOrder->spj_status))
        {{-- Ringkasan status SPJ --}}
        <div class="rounded-2xl border p-4 flex items-center gap-3 {{ $spjMeta['badge'] }}">
            <i data-lucide="{{ $spjMeta['icon'] }}" class="w-4 h-4 shrink-0"></i>
            <p class="text-sm font-bold">SPJ (Biaya Rampung): {{ $spjMeta['label'] }}</p>
            @if($travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_REVISION && $travelOrder->spj_catatan)
                <span class="text-sm font-normal opacity-90">&mdash; {{ $travelOrder->spj_catatan }}</span>
            @endif
        </div>
    @endif

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 lg:p-7 border-b border-slate-100 bg-slate-50/60">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Detail Perjalanan Dinas</p>
                    <h1 class="mt-2 text-2xl font-black text-slate-900 leading-tight">{{ $travelOrder->maksud_perjalanan }}</h1>
                    <p class="mt-3 text-sm text-slate-500 leading-relaxed max-w-4xl">
                        {{ $package->nama_paket }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-2 w-full xl:w-auto">
                    @if($isLuarDaerah)
                        <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'permohonan-bupati']) }}"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                            <i data-lucide="file-text" class="w-4 h-4 text-amber-500"></i>
                            Nota Dinas
                        </a>
                        <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'surat-tugas-bupati']) }}"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                            <i data-lucide="file-output" class="w-4 h-4 text-amber-500"></i>
                            Surat Tugas
                        </a>
                    @else
                        <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'surat-tugas-kadis']) }}"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                            <i data-lucide="file-output" class="w-4 h-4 text-amber-500"></i>
                            Surat Tugas
                        </a>
                    @endif
                    <button type="button" onclick="window.open('{{ route('packages.travel-orders.print-html', [$package, $travelOrder, 'sppd']) }}', '_blank')"
                        class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-lg hover:bg-black shadow-sm">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Cetak SPPD
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tipe</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $travelOrder->tipe_perjalanan }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tanggal Berangkat</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $travelOrder->tanggal_berangkat->format('d/m/Y') }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tanggal Kembali</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $travelOrder->tanggal_kembali->format('d/m/Y') }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tanggal Surat</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $travelOrder->tanggal_surat?->format('d/m/Y') ?? '-' }}</p>
            </div>
        </div>

        @if($travelOrder->dasar_pelaksanaan)
            <div class="px-6 py-5 border-t border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Dasar Pelaksanaan</p>
                <p class="mt-2 text-sm font-semibold text-slate-700 leading-relaxed">{{ $travelOrder->dasar_pelaksanaan }}</p>
            </div>
        @endif
    </section>

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="receipt-text" class="w-4 h-4 text-emerald-500"></i>
                    Biaya Perjalanan
                </h2>
                <p class="mt-1 text-sm text-slate-500">Bandingkan standar biaya, perkiraan, dan biaya rampung/SPJ.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg bg-slate-100 text-slate-600">
                {{ $travelOrder->personnels->count() }} pegawai
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Pegawai</th>
                        <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Standar / Koefisien</th>
                        <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-wide text-slate-500">Perkiraan</th>
                        <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Biaya Rampung</th>
                        <th class="px-5 py-3 text-center text-xs font-black uppercase tracking-wide text-slate-500">Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($travelOrder->personnels as $personnel)
                        @php
                            $estimate = $estimates[$personnel->id] ?? [];
                            $isEselon2 = ($personnel->employee?->kategori_biaya === 'Eselon II') || str_contains(strtolower($personnel->employee?->jabatan ?? ''), 'kepala dinas');
                            $perkiraan = ($estimate['uang_harian'] ?? 0)
                                + ($estimate['biaya_transport'] ?? 0)
                                + ($estimate['biaya_penginapan'] ?? 0)
                                + ($estimate['biaya_representasi'] ?? 0)
                                + ($isLuarDaerah ? ($estimate['biaya_taksi'] ?? 0) : 0);
                            $rampung = (float) $personnel->uang_harian
                                + (float) $personnel->biaya_transport
                                + (float) $personnel->biaya_penginapan
                                + (float) $personnel->biaya_representasi
                                + ($isLuarDaerah ? (float) ($personnel->biaya_taksi ?? 0) : 0);
                            $totalPerkiraan += $perkiraan;
                            $totalRampung += $rampung;
                        @endphp
                        <tr class="align-top">
                            <td class="px-5 py-4 min-w-72">
                                <p class="font-black text-slate-900 leading-snug">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                                    {{ $personnel->employee?->jabatan ?? '-' }}<br>
                                    NIP {{ $personnel->employee?->nip ?? '-' }} &bull; Gol. {{ $personnel->employee?->golongan ?? '-' }}
                                </p>
                                <span class="mt-2 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-bold">
                                    <i data-lucide="car" class="w-3 h-3"></i>
                                    {{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 min-w-64">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                    <span class="text-slate-500">Uang harian</span>
                                    <span class="font-bold text-slate-800 text-right">{{ $money($estimate['base_uang_harian'] ?? 0) }} x {{ $estimate['days'] ?? $days }} hari</span>
                                    <span class="text-slate-500">Transport</span>
                                    <span class="font-bold text-slate-800 text-right">{{ $money($estimate['biaya_transport'] ?? 0) }} x 1</span>
                                    @if($isLuarDaerah)
                                        <span class="text-slate-500">Taksi</span>
                                        <span class="font-bold text-slate-800 text-right">{{ $money($estimate['biaya_taksi'] ?? 0) }} x 1</span>
                                    @endif
                                    <span class="text-slate-500">Penginapan</span>
                                    <span class="font-bold text-slate-800 text-right">{{ $money($estimate['base_penginapan'] ?? 0) }} x {{ $estimate['nights'] ?? $nights }} malam</span>
                                    @if($isEselon2)
                                        <span class="text-slate-500">Representasi</span>
                                        <span class="font-bold text-slate-800 text-right">{{ $money($estimate['base_representasi'] ?? 0) }} x {{ $estimate['days'] ?? $days }} hari</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <p class="text-lg font-black text-slate-900">{{ $money($perkiraan) }}</p>
                            </td>
                            <td class="px-5 py-4 min-w-72">
                                <form method="POST" action="{{ route('packages.travel-orders.personnels.update-biaya', [$package, $travelOrder, $personnel]) }}" class="grid grid-cols-2 gap-2">
                                    @csrf
                                    @method('PUT')
                                    <label class="text-xs font-bold text-slate-500">
                                        Uang Harian
                                        <input type="number" name="uang_harian" value="{{ old('uang_harian', (int) $personnel->uang_harian) }}" min="0" required
                                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                    <label class="text-xs font-bold text-slate-500">
                                        Transport
                                        <input type="number" name="biaya_transport" value="{{ old('biaya_transport', (int) $personnel->biaya_transport) }}" min="0" required
                                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                    @if($isLuarDaerah)
                                        <label class="text-xs font-bold text-slate-500">
                                            Taksi
                                            <input type="number" name="biaya_taksi" value="{{ old('biaya_taksi', (int) ($personnel->biaya_taksi ?? 0)) }}" min="0" required
                                                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        </label>
                                    @else
                                        <input type="hidden" name="biaya_taksi" value="0">
                                    @endif
                                    <label class="text-xs font-bold text-slate-500">
                                        Penginapan
                                        <input type="number" name="biaya_penginapan" value="{{ old('biaya_penginapan', (int) $personnel->biaya_penginapan) }}" min="0" required
                                            class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </label>
                                    @if($isEselon2)
                                        <label class="text-xs font-bold text-slate-500">
                                            Representasi
                                            <input type="number" name="biaya_representasi" value="{{ old('biaya_representasi', (int) $personnel->biaya_representasi) }}" min="0" required
                                                class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        </label>
                                    @else
                                        <input type="hidden" name="biaya_representasi" value="0">
                                    @endif
                                    <div class="col-span-2 flex items-center justify-between gap-2 pt-1">
                                        <span class="text-sm font-black text-emerald-700">{{ $money($rampung) }}</span>
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-black text-white bg-slate-900 rounded-lg hover:bg-black">
                                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button type="button" onclick="window.open('{{ route('packages.travel-orders.personnels.print-kuitansi', [$package, $travelOrder, $personnel]) }}', '_blank')"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                    title="Cetak kuitansi">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <th colspan="2" class="px-5 py-4 text-right text-sm font-black text-slate-700">Total</th>
                        <th class="px-5 py-4 text-right text-lg font-black text-slate-900">{{ $money($totalPerkiraan) }}</th>
                        <th class="px-5 py-4 text-lg font-black text-emerald-700">{{ $money($totalRampung) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endcomponent
