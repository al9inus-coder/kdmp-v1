@component('layouts.kdmp')
@section('title', 'Detail Monev')

@php
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

    $totalPagu = 0;
    $totalRealisasi = 0;
    $paketSwakelola = 0;
    $paketPenyedia = 0;

    foreach ($subActivity->packages as $pkg) {
        $totalPagu += (float) $pkg->pagu;
        $totalRealisasi += $packageRealisasi($pkg);

        $jenis = strtolower(($pkg->jenis_pengadaan ?? '') . ' ' . ($pkg->metode_pengadaan ?? ''));
        if (str_contains($jenis, 'swakelola')) {
            $paketSwakelola++;
        } else {
            $paketPenyedia++;
        }
    }

    $sisaPagu = $totalPagu - $totalRealisasi;
    $progress = $totalPagu > 0 ? min(100, $totalRealisasi / $totalPagu * 100) : 0;
    $progressTone = $progress >= 75 ? 'emerald' : ($progress >= 40 ? 'amber' : 'rose');
    $toneText = ['emerald' => 'text-emerald-600', 'amber' => 'text-amber-600', 'rose' => 'text-rose-600'][$progressTone];
    $toneBg = ['emerald' => '#10b981', 'amber' => '#f59e0b', 'rose' => '#f43f5e'][$progressTone];
    $rupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $groupedPackages = $subActivity->packages->groupBy(fn($pkg) => $pkg->account?->id ?? 'none');
@endphp

<div class="space-y-6">
    <x-ui.toast />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg font-mono">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $subActivity->kode }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                <i data-lucide="folder-kanban" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $subActivity->activity?->program?->kode ?? '-' }}
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('kabid.monev.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
            <button type="button" onclick="printHidden('{{ route('monev.print', $subActivity) }}')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-black transition-colors shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Cetak Kartu Kendali
            </button>
        </div>
    </div>

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 lg:p-8 grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-8 items-center">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Detail Monitoring Sub Kegiatan</p>
                <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 mt-2 leading-tight">{{ $subActivity->nama }}</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Program</p>
                        <p class="text-sm font-bold text-slate-800 mt-1">{{ $subActivity->activity?->program?->kode ?? '-' }} - {{ $subActivity->activity?->program?->nama ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kegiatan</p>
                        <p class="text-sm font-bold text-slate-800 mt-1">{{ $subActivity->activity?->kode ?? '-' }} - {{ $subActivity->activity?->nama ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative w-56 h-56 rounded-full flex items-center justify-center"
                    style="background: conic-gradient({{ $toneBg }} {{ $progress }}%, #e2e8f0 0);">
                    <div class="absolute inset-4 rounded-full bg-white shadow-inner"></div>
                    <div class="relative text-center px-6">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Serapan</p>
                        <p class="text-5xl font-extrabold {{ $toneText }} mt-1">{{ number_format($progress, 1, ',', '.') }}%</p>
                        <p class="text-xs font-semibold text-slate-500 mt-2">dari total pagu</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pagu</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $rupiah($totalPagu) }}</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Realisasi</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $rupiah($totalRealisasi) }}</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisa Dana</p>
            <p class="text-2xl font-extrabold {{ $sisaPagu < 0 ? 'text-rose-600' : 'text-slate-900' }} mt-2">{{ $rupiah($sisaPagu) }}</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Jumlah Paket</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $subActivity->packages->count() }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-2">{{ $paketPenyedia }} penyedia &bull; {{ $paketSwakelola }} swakelola</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6 items-start">
        <div class="space-y-6 min-w-0">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                    <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <i data-lucide="list-tree" class="w-5 h-5 text-blue-500"></i>
                        Kartu Kendali Kegiatan
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="px-5 py-3 text-center w-12">No</th>
                                <th class="px-5 py-3 text-center w-36">Kode Rekening</th>
                                <th class="px-5 py-3 min-w-64">Uraian Belanja</th>
                                <th class="px-5 py-3 text-right w-36">Pagu (Rp)</th>
                                <th class="px-5 py-3 text-right w-36">Realisasi (Rp)</th>
                                <th class="px-5 py-3 text-right w-36">Sisa (Rp)</th>
                                <th class="px-5 py-3 text-center w-24">Serapan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php $no = 1; @endphp
                            @forelse($groupedPackages as $accountId => $packages)
                                @php
                                    $account = $packages->first()->account;
                                    $groupPagu = (float) $packages->sum('pagu');
                                    $groupRealisasi = $packages->sum(fn($pkg) => $packageRealisasi($pkg));
                                    $groupSisa = $groupPagu - $groupRealisasi;
                                    $groupPersen = $groupPagu > 0 ? min(100, $groupRealisasi / $groupPagu * 100) : 0;
                                @endphp

                                {{-- Baris rekening belanja --}}
                                <tr class="bg-slate-50/80 font-bold">
                                    <td class="px-5 py-3.5 text-center text-slate-600">{{ $no++ }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 font-mono">
                                            {{ $account->kode ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-900">{{ $account->nama ?? 'Tanpa Uraian Belanja' }}</td>
                                    <td class="px-5 py-3.5 text-right text-blue-600 whitespace-nowrap">{{ $rupiah($groupPagu) }}</td>
                                    <td class="px-5 py-3.5 text-right"></td>
                                    <td class="px-5 py-3.5 text-right text-rose-600 whitespace-nowrap">{{ $rupiah($groupSisa) }}</td>
                                    <td class="px-5 py-3.5 text-center text-slate-700">{{ number_format($groupPersen, 1, ',', '.') }}%</td>
                                </tr>

                                {{-- Baris paket pengadaan --}}
                                @foreach($packages as $pkg)
                                    @php
                                        $pkgRealisasi = $packageRealisasi($pkg);
                                        $packageUrl = $pkg->procurementPackage
                                            ? route('kabid.procurement-packages.show', $pkg)
                                            : route('kabid.packages.show', $pkg);
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-5 py-3"></td>
                                        <td class="px-5 py-3"></td>
                                        <td class="px-5 py-3 pl-10">
                                            <div class="flex items-start gap-2">
                                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5 text-slate-300 mt-0.5 shrink-0"></i>
                                                <div class="min-w-0">
                                                    <a href="{{ $packageUrl }}" class="font-semibold text-slate-800 hover:text-emerald-700 leading-snug">
                                                        {{ $pkg->nama_paket }}
                                                    </a>
                                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                        {{ $pkg->jenis_pengadaan ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-right"></td>
                                        <td class="px-5 py-3 text-right font-semibold text-emerald-600 whitespace-nowrap">{{ $rupiah($pkgRealisasi) }}</td>
                                        <td class="px-5 py-3 text-right"></td>
                                        <td class="px-5 py-3 text-center"></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10">
                                        <x-ui.empty-state icon="package-x" title="Belum Ada Paket" description="Belum ada paket pekerjaan pada sub kegiatan ini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-20">
            <x-ui.card padding="md">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Komposisi</p>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 font-semibold text-slate-600">
                            <i data-lucide="briefcase-business" class="w-4 h-4 text-blue-500"></i>
                            Penyedia
                        </span>
                        <span class="font-extrabold text-slate-900">{{ $paketPenyedia }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 font-semibold text-slate-600">
                            <i data-lucide="users-round" class="w-4 h-4 text-amber-500"></i>
                            Swakelola
                        </span>
                        <span class="font-extrabold text-slate-900">{{ $paketSwakelola }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card padding="md">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sumber Realisasi</p>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p class="flex items-start gap-2">
                        <i data-lucide="file-signature" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
                        Kontrak pengadaan dan paket dikecualikan.
                    </p>
                    <p class="flex items-start gap-2">
                        <i data-lucide="plane" class="w-4 h-4 text-blue-500 mt-0.5 shrink-0"></i>
                        Perjalanan dinas yang tercatat.
                    </p>
                    <p class="flex items-start gap-2">
                        <i data-lucide="clock-3" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                        Lembur yang sudah dikunci.
                    </p>
                </div>
            </x-ui.card>
        </aside>
    </div>
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
