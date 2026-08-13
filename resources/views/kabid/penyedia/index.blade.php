@component('layouts.kdmp')
@section('title', 'Pengadaan Penyedia')

@php
    $formatM = fn($num) => rupiahSingkat($num);

    $pipeline = [
        'draft' => [
            'label' => 'Draft', 'desc' => 'Persiapan dokumen',
            'icon' => 'file-text', 'iconBg' => 'bg-slate-100 text-slate-500',
            'dot' => 'bg-slate-400', 'bar' => 'bg-slate-400', 'ring' => 'ring-slate-300',
        ],
        'persiapan' => [
            'label' => 'Pemilihan', 'desc' => 'Pemilihan penyedia',
            'icon' => 'clipboard-list', 'iconBg' => 'bg-blue-50 text-blue-500',
            'dot' => 'bg-blue-500', 'bar' => 'bg-blue-500', 'ring' => 'ring-blue-300',
        ],
        'diproses' => [
            'label' => 'Diproses', 'desc' => 'Pelaksanaan & pembayaran',
            'icon' => 'sliders', 'iconBg' => 'bg-orange-50 text-orange-500',
            'dot' => 'bg-orange-500', 'bar' => 'bg-orange-500', 'ring' => 'ring-orange-300',
        ],
        'selesai' => [
            'label' => 'Selesai', 'desc' => 'Pengadaan tuntas',
            'icon' => 'check-circle', 'iconBg' => 'bg-emerald-50 text-emerald-500',
            'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'ring' => 'ring-emerald-300',
        ],
    ];

    $filterUrl = fn($s) => route('kabid.penyedia.index', array_filter([
        'status'     => $s,
        'search'     => request('search'),
        'program_id' => request('program_id'),
    ], fn($v) => $v !== null && $v !== ''));

    $totalCount = array_sum(array_column($stats, 'count'));
    $totalPagu  = array_sum(array_column($stats, 'total'));
@endphp

<x-ui.toast />

<x-ui.workspace title="Pengadaan Penyedia" description="Ikuti tahapan workflow: persiapan, pemilihan penyedia, pelaksanaan, hingga pembayaran. Klik kartu tahapan untuk memfilter.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="briefcase-business" class="w-4 h-4 text-emerald-500"></i>
            {{ $totalCount }} paket &bull; {{ $formatM($totalPagu) }}
        </div>
    </x-slot:actions>

    {{-- Pipeline: kartu tahapan sekaligus filter --}}
    {{-- Ponsel: tahapan jadi satu baris pil yang digeser mendatar. Kartu 2x2 di
         bawah memakan 444px sebelum baris data pertama muncul — lebih tinggi dari
         separuh layar. Pil "Semua" ditambahkan karena cara membatalkan filter
         sekarang adalah menekan ulang kartu yang aktif, dan itu tidak terlihat. --}}
    <div class="sm:hidden -mx-1 px-1 mb-4 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <a href="{{ $filterUrl(null) }}"
            class="shrink-0 inline-flex items-baseline gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                {{ $status ? 'bg-white border-slate-200 text-slate-600' : 'bg-slate-800 border-slate-800 text-white' }}">
            <span class="text-sm font-bold">{{ $totalCount }}</span> Semua
        </a>
        @foreach($pipeline as $key => $stage)
            <a href="{{ $status === $key ? $filterUrl(null) : $filterUrl($key) }}"
                class="shrink-0 inline-flex items-baseline gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                    {{ $status === $key ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-600' }}">
                <span class="w-1.5 h-1.5 rounded-full self-center {{ $stage['dot'] }}"></span>
                <span class="text-sm font-bold">{{ $stats[$key]['count'] }}</span> {{ $stage['label'] }}
            </a>
        @endforeach
    </div>

    <div class="hidden sm:grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($pipeline as $key => $stage)
            @php $isActive = $status === $key; @endphp
            <a href="{{ $isActive ? $filterUrl(null) : $filterUrl($key) }}"
                class="group relative bg-white rounded-2xl border shadow-sm p-5 transition-all hover:-translate-y-0.5 hover:shadow-md
                    {{ $isActive ? 'border-transparent ring-2 '.$stage['ring'] : 'border-slate-200' }}">
                <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full {{ $stage['bar'] }} {{ $isActive ? 'opacity-100' : 'opacity-30 group-hover:opacity-70' }} transition-opacity"></div>

                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl {{ $stage['iconBg'] }} flex items-center justify-center">
                        <i data-lucide="{{ $stage['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white">
                            <i data-lucide="filter" class="w-2.5 h-2.5"></i> Aktif
                        </span>
                    @else
                        <span class="text-[10px] font-semibold text-slate-300 group-hover:text-slate-400 transition-colors">Tahap {{ $loop->iteration }}</span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2 mt-3">
                    <span class="text-3xl font-black text-slate-900">{{ $stats[$key]['count'] }}</span>
                    <span class="flex items-center gap-1.5 text-xs font-bold text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full {{ $stage['dot'] }}"></span>{{ $stage['label'] }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $stage['desc'] }}</p>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                    <span class="font-bold text-slate-800">{{ $formatM($stats[$key]['total']) }}</span>
                    <span class="text-slate-400">anggaran</span>
                </div>
            </a>
        @endforeach
    </div>

    <x-ui.card padding="none" class="max-sm:-m-4 max-sm:rounded-none max-sm:border-0 max-sm:!shadow-none">
        {{-- Toolbar --}}
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-slate-100">
            <form action="{{ route('kabid.penyedia.index') }}" method="GET" class="w-full">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <x-ui.toolbar search="true" searchPlaceholder="Cari nama paket atau ID RUP...">
                    <x-slot:filters>
                        {{-- w-full di ponsel: tanpa itu select melar mengikuti teks
                             pilihan terpanjang (413px) dan tepi kanannya jatuh 94px
                             di luar layar 393px, sehingga pilihan yang sedang aktif
                             tidak terbaca. Dari sm ke atas kembali selebar isinya. --}}
                        <select name="program_id" onchange="this.form.submit()"
                            class="w-full sm:w-auto min-w-0 px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white text-slate-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                    {{ $program->kode }} - {{ Str::limit($program->nama, 42) }}
                                </option>
                            @endforeach
                        </select>
                    </x-slot:filters>

                    @if(request()->hasAny(['search', 'status', 'program_id']))
                        <x-ui.button variant="ghost" size="sm" href="{{ route('kabid.penyedia.index') }}">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Reset
                        </x-ui.button>
                    @endif
                </x-ui.toolbar>
            </form>
        </div>

        @php
            // Peta tahapan -> tampilan dihitung SEKALI di sini, lalu dipakai dua
            // kali: kartu di ponsel dan baris tabel di layar besar. Kalau masing-
            // masing menghitung sendiri, keduanya bisa menyimpang tanpa ketahuan.
            $W = \App\Models\ProcurementPackage::class;

            $baris = collect($procurementPackages->items())->map(function ($p) use ($W) {
                $pkg = $p->package;

                $b = [
                    'pkg'      => $pkg,
                    'label'    => 'Draft',
                    'dot'      => 'bg-slate-400',
                    'warna'    => 'bg-slate-100 text-slate-700',
                    'progres'  => 'Persiapan Pengadaan',
                    'aksi'     => 'Lanjutkan Persiapan',
                    'ikon'     => 'arrow-right',
                    'url'      => $pkg ? route('kabid.procurement-packages.show', $pkg) : '#',
                    'tahap'    => [1, 0, 0, 0],
                ];

                if ($p->workflow_status === $W::WORKFLOW_PROVIDER_SELECTION) {
                    $b = array_merge($b, [
                        'label' => 'Pemilihan', 'dot' => 'bg-blue-500', 'warna' => 'bg-blue-50 text-blue-700',
                        'progres' => 'Pemilihan Penyedia', 'aksi' => 'Buka Pemilihan',
                        'url' => $pkg ? route('kabid.procurement-packages.procurement-process.show', $pkg) : '#',
                        'tahap' => [2, 1, 0, 0],
                    ]);
                } elseif ($p->workflow_status === $W::WORKFLOW_EXECUTION) {
                    $b = array_merge($b, [
                        'label' => 'Pelaksanaan', 'dot' => 'bg-orange-500', 'warna' => 'bg-orange-50 text-orange-700',
                        'progres' => 'Pelaksanaan Kontrak', 'aksi' => 'Buka Pelaksanaan',
                        'url' => $pkg ? route('kabid.procurement-packages.execution.show', $pkg) : '#',
                        'tahap' => [2, 2, 1, 0],
                    ]);
                } elseif ($p->workflow_status === $W::WORKFLOW_PAYMENT_PROCESS) {
                    $b = array_merge($b, [
                        'label' => 'Pembayaran', 'dot' => 'bg-amber-500', 'warna' => 'bg-amber-50 text-amber-700',
                        'progres' => 'Pembayaran', 'aksi' => 'Buka Pembayaran',
                        'url' => $pkg ? route('kabid.procurement-packages.payment.show', $pkg) : '#',
                        'tahap' => [2, 2, 2, 1],
                    ]);
                } elseif ($p->workflow_status === $W::WORKFLOW_COMPLETED) {
                    $b = array_merge($b, [
                        'label' => 'Selesai', 'dot' => 'bg-emerald-500', 'warna' => 'bg-emerald-50 text-emerald-700',
                        'progres' => 'Selesai', 'aksi' => 'Lihat Dokumen', 'ikon' => 'eye',
                        'url' => $pkg ? route('kabid.procurement-packages.payment.show', $pkg) : '#',
                        'tahap' => [2, 2, 2, 2],
                    ]);
                }

                return $b;
            });

            $dotClass = fn ($state) => $state === 2
                ? 'bg-emerald-500'
                : ($state === 1 ? 'bg-blue-500 ring-2 ring-blue-400/50 animate-pulse' : 'bg-slate-200');
        @endphp

        {{-- Ponsel: tiap baris jadi kartu. Tabelnya lebar 954px dipaksa masuk kotak
             293px, jadi 661px harus digeser ke samping dan nama paket terpotong.
             Sebagai kartu, nama terbaca utuh dan tombol aksi tidak lagi tersembunyi
             di ujung kanan. --}}
        {{-- Ponsel: tiap baris jadi kartu berdiri sendiri di atas latar abu,
             bukan baris berpembatas. Pemisahan lewat jarak dan garis tepi jauh
             lebih terbaca di layar sempit daripada garis mendatar tunggal. --}}
        <div class="sm:hidden bg-slate-50 pt-3 pb-24 space-y-2.5">
            @forelse($baris as $b)
                <a href="{{ $b['url'] }}"
                    class="block bg-white border border-slate-200 rounded-xl p-3.5 active:bg-slate-50 transition-colors">
                    <div class="flex items-start justify-between gap-2.5">
                        <p class="font-bold text-slate-900 text-sm leading-snug">
                            {{ $b['pkg']?->nama_paket ?? '-' }}
                        </p>
                        <span class="shrink-0 text-[10px] font-semibold text-slate-400 bg-slate-100 rounded px-1.5 py-0.5 tracking-wide">
                            {{ $b['pkg']?->id_rup ?? '-' }}
                        </span>
                    </div>

                    <p class="text-[11px] text-slate-400 mt-1 truncate">
                        {{ $b['pkg']?->program?->kode ?? '-' }}
                        @if($b['pkg']?->program?->nama)
                            &bull; {{ $b['pkg']->program->nama }}
                        @endif
                    </p>

                    <div class="flex items-baseline justify-between gap-2.5 mt-2.5 pt-2.5 border-t border-slate-100">
                        <span>
                            <span class="text-[15px] font-extrabold text-slate-900">Rp {{ number_format($b['pkg']?->pagu ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">pagu</span>
                        </span>
                        <span class="shrink-0 text-[10px] font-bold text-slate-500 bg-slate-100 rounded-full px-2 py-0.5">
                            {{ $b['pkg']?->metode_pengadaan ?? '-' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 mt-2.5">
                        <span class="flex items-center gap-1">
                            @foreach($b['tahap'] as $s)
                                <span class="w-4 h-1 rounded-full {{ $s === 2 ? 'bg-emerald-500' : ($s === 1 ? 'bg-amber-500' : 'bg-slate-200') }}"></span>
                            @endforeach
                        </span>
                        <span class="text-[11px] font-semibold text-slate-600">{{ $b['progres'] }}</span>
                    </div>
                </a>
            @empty
                <div class="py-10">
                    <x-ui.empty-state icon="package-x" title="Tidak Ada Paket" description="Belum ada paket penyedia yang sesuai dengan filter saat ini." />
                </div>
            @endforelse
        </div>

        {{-- Tabel --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-24">ID RUP</th>
                        <th class="px-6 py-4 min-w-72">Nama Paket Pengadaan</th>
                        <th class="px-6 py-4">Pagu</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Progres Tahapan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($baris as $b)
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ $b['url'] }}'">
                            <td class="px-6 py-4 text-slate-400 font-semibold tracking-wide whitespace-nowrap">
                                {{ $b['pkg']?->id_rup ?? '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <div class="font-bold text-slate-900 text-sm group-hover:text-emerald-600 transition-colors leading-snug">
                                    {{ $b['pkg']?->nama_paket ?? '-' }}
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $b['pkg']?->program?->kode ?? '-' }}
                                    @if($b['pkg']?->program?->nama)
                                        &bull; {{ Str::limit($b['pkg']->program->nama, 58) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($b['pkg']?->pagu ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $b['pkg']?->metode_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center w-56 mb-2">
                                    @foreach($b['tahap'] as $i => $s)
                                        @if($i > 0)
                                            <div class="flex-1 h-[1.5px] {{ $b['tahap'][$i - 1] === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                        @endif
                                        <div class="w-1.5 h-1.5 rounded-full {{ $dotClass($s) }}"></div>
                                    @endforeach
                                </div>
                                <div class="text-xs font-semibold text-slate-500">{{ $b['progres'] }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10">
                                <x-ui.empty-state icon="package-x" title="Tidak Ada Paket" description="Belum ada paket penyedia yang sesuai dengan filter saat ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurementPackages->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $procurementPackages->links() }}
            </div>
        @else
            <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $procurementPackages->count() }}</span> paket
                </p>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
