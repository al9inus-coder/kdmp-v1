@component('layouts.kdmp')
@section('title', 'Pengajuan SPPD')

<div class="space-y-6">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="plane" class="w-6 h-6 text-emerald-600"></i>
                Pengajuan <span class="text-emerald-600">SPPD</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Tinjau pengajuan surat perjalanan dinas dari staf: setujui, revisi, atau tolak di halaman detail.</p>
        </div>
        <a href="{{ route('dashboard.kabid') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Filter panel --}}
    @php
        $statusTabs = [
            ''          => ['label' => 'Semua',        'dot' => 'bg-slate-400',   'active' => 'text-emerald-700 border-emerald-600 bg-emerald-50/60'],
            'submitted' => ['label' => 'Diajukan',     'dot' => 'bg-blue-500',    'active' => 'text-blue-700 border-blue-500 bg-blue-50/60'],
            'revision'  => ['label' => 'Perlu Revisi', 'dot' => 'bg-amber-500',   'active' => 'text-amber-700 border-amber-500 bg-amber-50/60'],
            'approved'  => ['label' => 'Disetujui',    'dot' => 'bg-emerald-500', 'active' => 'text-emerald-700 border-emerald-500 bg-emerald-50/60'],
            'rejected'  => ['label' => 'Ditolak',      'dot' => 'bg-rose-500',    'active' => 'text-rose-700 border-rose-500 bg-rose-50/60'],
        ];
    @endphp

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="flex items-end gap-1 px-3 pt-2 border-b border-slate-200 overflow-x-auto">
            @foreach($statusTabs as $value => $tab)
                @php
                    $count = $value === '' ? $statusCounts->sum() : ($statusCounts[$value] ?? 0);
                    $isActive = ($status ?? '') === $value;
                    $tabUrl = route('kabid.sppd.index', array_filter(array_merge(request()->except(['status', 'page']), $value !== '' ? ['status' => $value] : [])));
                @endphp
                <a href="{{ $tabUrl }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap rounded-t-xl border-b-2 transition-colors
                        {{ $isActive ? $tab['active'] : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $tab['dot'] }}"></span>
                    {{ $tab['label'] }}
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-white/80 text-slate-700' : 'bg-slate-100 text-slate-500' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('kabid.sppd.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                    placeholder="Cari tujuan, maksud, atau paket...">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                <i data-lucide="search" class="w-4 h-4"></i> Cari
            </button>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px] table-fixed text-sm text-left text-slate-600">
                <colgroup>
                    <col class="w-[18%]">
                    <col class="w-[28%]">
                    <col class="w-[18%]">
                    <col class="w-[20%]">
                    <col class="w-[9%]">
                    <col class="w-[7%]">
                </colgroup>
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nama Pelaksana</th>
                        <th class="px-6 py-4 font-semibold">Maksud dan Tujuan</th>
                        <th class="px-6 py-4 font-semibold">Tanggal Perjalanan</th>
                        <th class="px-6 py-4 font-semibold">Sub Kegiatan</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($travelOrders as $to)
                        @php
                            $meta = $to->statusMeta();
                            $isDiajukan = $to->status === \App\Models\TravelOrder::STATUS_SUBMITTED;
                            $duration = $to->tanggal_berangkat && $to->tanggal_kembali
                                ? $to->tanggal_berangkat->diffInDays($to->tanggal_kembali) + 1
                                : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer {{ $isDiajukan ? 'bg-blue-50/20' : '' }}"
                            onclick="window.location.href='{{ route('kabid.packages.travel-orders.show', [$to->package, $to]) }}'">
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($to->personnels->take(4) as $personnel)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                            <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                            {{ $personnel->employee?->nama ?? 'Pegawai' }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 font-semibold">Belum ada pelaksana</span>
                                    @endforelse
                                    @if($to->personnels->count() > 4)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-500 text-xs font-bold">+{{ $to->personnels->count() - 4 }}</span>
                                    @endif
                                </div>
                                @if($to->creator)
                                    <p class="text-[11px] text-slate-400 mt-1.5">Diajukan {{ $to->creator->name }}{{ $to->submitted_at ? ' · ' . $to->submitted_at->locale('id')->diffForHumans() : '' }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2">{{ $to->maksud_perjalanan }}</p>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                    {{ $to->tempat_tujuan }}
                                </p>
                                @if(in_array($to->status, [\App\Models\TravelOrder::STATUS_REVISION, \App\Models\TravelOrder::STATUS_REJECTED]) && $to->catatan_review)
                                    <p class="text-[11px] {{ $to->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'text-rose-600' : 'text-amber-600' }} font-semibold mt-1 flex items-center gap-1">
                                        <i data-lucide="message-square-warning" class="w-3 h-3"></i>
                                        {{ Str::limit($to->catatan_review, 50) }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <p class="font-medium text-slate-700">{{ $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y') }}</p>
                                <p class="text-slate-400">s.d. {{ $to->tanggal_kembali?->locale('id')->translatedFormat('d M Y') }}</p>
                                <span class="mt-1 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 font-semibold">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    {{ $duration }} hari
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-semibold text-slate-700 line-clamp-2">{{ $to->package?->subActivity?->nama ?? '-' }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">{{ $to->package?->subActivity?->kode ?? ($to->package?->nama_paket ?? '-') }}</p>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border {{ $meta['badge'] }}">
                                    <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i> {{ $meta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('kabid.packages.travel-orders.show', [$to->package, $to]) }}"
                                    onclick="event.stopPropagation()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                                    </div>
                                    <p class="font-medium text-slate-600">Belum ada pengajuan SPPD.</p>
                                    <p class="text-sm mt-1">Pengajuan dari staf akan muncul di sini untuk ditinjau.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($travelOrders->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">{{ $travelOrders->links() }}</div>
        @endif
    </div>
</div>
@endcomponent
