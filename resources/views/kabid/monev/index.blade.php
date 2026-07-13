@component('layouts.kdmp')
@section('title', 'Monitoring & Evaluasi')

@php
    $summary = [
        'pagu' => 0,
        'realisasi' => 0,
        'penyedia' => 0,
        'swakelola' => 0,
        'sub_kegiatan' => 0,
    ];

    $packageRealisasi = function ($pkg) use ($sbuRates) {
        $total = 0;

        if ($pkg->procurementPackage) {
            $total += (float) $pkg->procurementPackage->realisasi;
        }

        foreach ($pkg->travelOrders ?? [] as $travelOrder) {
            // Hanya SPJ (biaya rampung) yang sudah disetujui yang masuk realisasi.
            if ($travelOrder->spjStatus() !== \App\Models\TravelOrder::SPJ_APPROVED) { continue; }
            foreach ($travelOrder->personnels ?? [] as $personnel) {
                $total += (float) $personnel->uang_harian
                    + (float) $personnel->biaya_penginapan
                    + (float) $personnel->biaya_representasi
                    + (float) $personnel->biaya_transport
                    + (float) ($personnel->biaya_taksi ?? 0);
            }
        }

        foreach ($pkg->overtimes ?? [] as $overtime) {
            if ($overtime->is_locked) {
                $total += (float) $overtime->calculateTotalRealisasi($sbuRates);
            }
        }

        return $total;
    };

    foreach ($programs as $program) {
        foreach ($program->activities as $activity) {
            foreach ($activity->subActivities as $subActivity) {
                $summary['sub_kegiatan']++;

                foreach ($subActivity->packages as $pkg) {
                    $summary['pagu'] += (float) $pkg->pagu;
                    $summary['realisasi'] += $packageRealisasi($pkg);

                    $jenis = strtolower(($pkg->jenis_pengadaan ?? '') . ' ' . ($pkg->metode_pengadaan ?? ''));
                    if (str_contains($jenis, 'swakelola')) {
                        $summary['swakelola']++;
                    } else {
                        $summary['penyedia']++;
                    }
                }
            }
        }
    }

    $summary['sisa'] = $summary['pagu'] - $summary['realisasi'];
    $summary['serapan'] = $summary['pagu'] > 0 ? min(100, $summary['realisasi'] / $summary['pagu'] * 100) : 0;
    $rupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="space-y-6">
    <x-ui.toast />

    <x-ui.workspace title="Monitoring & Evaluasi" description="Pantau serapan anggaran per program, kegiatan, dan sub kegiatan.">
        <x-slot:actions>
            <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
                <i data-lucide="layers" class="w-4 h-4 text-emerald-500"></i>
                {{ $summary['sub_kegiatan'] }} sub kegiatan
            </div>
            <x-ui.button variant="outline" size="md" href="{{ route('dashboard.kabid') }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Dashboard
            </x-ui.button>
        </x-slot:actions>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-ui.card padding="md" class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pagu</p>
                        <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $rupiah($summary['pagu']) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </span>
                </div>
            </x-ui.card>

            <x-ui.card padding="md" class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Realisasi</p>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $rupiah($summary['realisasi']) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="hand-coins" class="w-5 h-5"></i>
                    </span>
                </div>
            </x-ui.card>

            <x-ui.card padding="md" class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisa Dana</p>
                        <p class="text-2xl font-extrabold {{ $summary['sisa'] < 0 ? 'text-rose-600' : 'text-slate-900' }} mt-2">{{ $rupiah($summary['sisa']) }}</p>
                    </div>
                    <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </span>
                </div>
            </x-ui.card>

            <x-ui.card padding="md" class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Serapan</p>
                        <p class="text-2xl font-extrabold text-orange-600 mt-2">{{ number_format($summary['serapan'], 1, ',', '.') }}%</p>
                        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden mt-3">
                            <div class="h-full rounded-full bg-orange-500" style="width: {{ $summary['serapan'] }}%;"></div>
                        </div>
                    </div>
                    <span class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <i data-lucide="chart-pie" class="w-5 h-5"></i>
                    </span>
                </div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_280px] gap-6">
            <div class="space-y-6 min-w-0">
                @forelse($programs as $program)
                    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                            <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                                <i data-lucide="folder-kanban" class="w-5 h-5 text-blue-500"></i>
                                {{ $program->kode }} - {{ $program->nama }}
                            </h2>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($program->activities as $activity)
                                <div class="p-5">
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 mb-4">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kegiatan</p>
                                            <h3 class="text-sm font-bold text-slate-800 mt-1">{{ $activity->kode }} - {{ $activity->nama }}</h3>
                                        </div>
                                        <button type="button" onclick="printHidden('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'control-cards.print', $activity) }}')"
                                            class="inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm shrink-0">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            Cetak Kendali
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                                        @foreach($activity->subActivities as $subActivity)
                                            @php
                                                $totalPagu = 0;
                                                $totalRealisasi = 0;
                                                $paketSwakelola = 0;
                                                $paketPenyedia = 0;

                                                foreach($subActivity->packages as $pkg) {
                                                    $totalPagu += (float) $pkg->pagu;
                                                    $totalRealisasi += $packageRealisasi($pkg);

                                                    $jenis = strtolower(($pkg->jenis_pengadaan ?? '') . ' ' . ($pkg->metode_pengadaan ?? ''));
                                                    if(str_contains($jenis, 'swakelola')) {
                                                        $paketSwakelola++;
                                                    } else {
                                                        $paketPenyedia++;
                                                    }
                                                }

                                                $sisaPagu = $totalPagu - $totalRealisasi;
                                                $progress = $totalPagu > 0 ? min(100, ($totalRealisasi / $totalPagu) * 100) : 0;
                                                $progressTone = $progress >= 75 ? 'emerald' : ($progress >= 40 ? 'amber' : 'rose');
                                                $toneClass = [
                                                    'emerald' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                                    'amber' => 'text-amber-600 bg-amber-50 border-amber-100',
                                                    'rose' => 'text-rose-600 bg-rose-50 border-rose-100',
                                                ][$progressTone];
                                                $barClass = [
                                                    'emerald' => 'bg-emerald-500',
                                                    'amber' => 'bg-amber-500',
                                                    'rose' => 'bg-rose-500',
                                                ][$progressTone];
                                            @endphp

                                            <a href="{{ route('kabid.monev.show', $subActivity) }}"
                                                class="group block rounded-2xl border border-slate-200 bg-white hover:border-emerald-200 hover:shadow-md transition-all overflow-hidden">
                                                <div class="p-4">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-extrabold text-slate-500">{{ $subActivity->kode }}</p>
                                                            <h4 class="text-sm font-bold text-slate-800 mt-1 leading-snug group-hover:text-emerald-700">
                                                                {{ $subActivity->nama }}
                                                            </h4>
                                                        </div>
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-[11px] font-bold {{ $toneClass }} shrink-0">
                                                            {{ number_format($progress, 1, ',', '.') }}%
                                                        </span>
                                                    </div>

                                                    <div class="space-y-2.5 mt-4 text-xs">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-slate-400 font-semibold">Total Pagu</span>
                                                            <span class="text-slate-800 font-bold text-right">{{ $rupiah($totalPagu) }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-slate-400 font-semibold">Realisasi</span>
                                                            <span class="text-emerald-600 font-bold text-right">{{ $rupiah($totalRealisasi) }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between gap-3">
                                                            <span class="text-slate-400 font-semibold">Sisa Dana</span>
                                                            <span class="{{ $sisaPagu < 0 ? 'text-rose-600' : 'text-slate-800' }} font-bold text-right">{{ $rupiah($sisaPagu) }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4">
                                                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                                            <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $progress }}%;"></div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-3 text-[11px] font-bold text-slate-500">
                                                            <span class="inline-flex items-center gap-1.5">
                                                                <i data-lucide="briefcase-business" class="w-3 h-3 text-blue-500"></i>
                                                                Penyedia {{ $paketPenyedia }}
                                                            </span>
                                                            <span class="inline-flex items-center gap-1.5">
                                                                <i data-lucide="users-round" class="w-3 h-3 text-amber-500"></i>
                                                                Swakelola {{ $paketSwakelola }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="px-4 py-3 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-700">
                                                    <span>Lihat detail</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <x-ui.empty-state icon="info" title="Belum Ada Data" description="Belum ada data program, kegiatan, dan sub kegiatan yang dapat dimonitor." />
                @endforelse
            </div>

            <aside class="space-y-4">
                <x-ui.card padding="md">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Komposisi Paket</p>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 font-semibold text-slate-600">
                                <i data-lucide="briefcase-business" class="w-4 h-4 text-blue-500"></i>
                                Penyedia
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $summary['penyedia'] }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 font-semibold text-slate-600">
                                <i data-lucide="users-round" class="w-4 h-4 text-amber-500"></i>
                                Swakelola
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $summary['swakelola'] }}</span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card padding="md">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Catatan</p>
                    <p class="text-sm text-slate-500 leading-relaxed mt-3">
                        Realisasi dihitung dari kontrak pengadaan, catatan dikecualikan, perjalanan dinas, dan lembur yang sudah dikunci.
                    </p>
                </x-ui.card>
            </aside>
        </div>
    </x-ui.workspace>
</div>

<script>
    function printHidden(url) {
        const oldIframe = document.getElementById('hidden-print-iframe');
        if (oldIframe) oldIframe.remove();

        const iframe = document.createElement('iframe');
        iframe.id = 'hidden-print-iframe';
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        iframe.style.visibility = 'hidden';

        document.body.appendChild(iframe);
        iframe.src = url;
    }
</script>
@endcomponent
