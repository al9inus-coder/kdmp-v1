@component('layouts.kdmp')
@section('title', 'Pengajuan SPPD')

<div class="space-y-6"
    x-data="{
        review: { open: false, mode: 'revisi', action: '', tujuan: '' },
        openReview(mode, action, tujuan) {
            this.review = { open: true, mode, action, tujuan };
        },
    }">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="plane" class="w-6 h-6 text-emerald-600"></i>
                Pengajuan <span class="text-emerald-600">SPPD</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Tinjau pengajuan surat perjalanan dinas dari staf: setujui, revisi, atau tolak.</p>
        </div>
        <a href="{{ route('dashboard.kabid') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Filter --}}
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
            <table class="w-full min-w-[1080px] text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Pelaksana</th>
                        <th class="px-6 py-4 font-semibold">Maksud &amp; Tujuan</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($travelOrders as $to)
                        @php
                            $meta = $to->statusMeta();
                            $isDiajukan = $to->status === \App\Models\TravelOrder::STATUS_SUBMITTED;
                            $duration = $to->tanggal_berangkat && $to->tanggal_kembali ? $to->tanggal_berangkat->diffInDays($to->tanggal_kembali) + 1 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors {{ $isDiajukan ? 'bg-blue-50/20' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-xs">
                                    @forelse($to->personnels->take(4) as $personnel)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                            <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>{{ $personnel->employee?->nama ?? 'Pegawai' }}
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
                                <p class="font-semibold text-slate-800 line-clamp-2">{{ $to->maksud_perjalanan }}</p>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>{{ $to->tempat_tujuan }}
                                </p>
                                @if(in_array($to->status, [\App\Models\TravelOrder::STATUS_REVISION, \App\Models\TravelOrder::STATUS_REJECTED]) && $to->catatan_review)
                                    <p class="text-[11px] {{ $to->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'text-rose-600' : 'text-amber-600' }} font-semibold mt-1 flex items-center gap-1">
                                        <i data-lucide="message-square-warning" class="w-3 h-3"></i>{{ Str::limit($to->catatan_review, 45) }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <p class="font-medium text-slate-700">{{ $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y') }}</p>
                                <p class="text-slate-400">s.d. {{ $to->tanggal_kembali?->locale('id')->translatedFormat('d M Y') }}</p>
                                <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-semibold">
                                    <i data-lucide="clock" class="w-3 h-3"></i>{{ $duration }} hari
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border {{ $meta['badge'] }}">
                                    <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i> {{ $meta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isDiajukan)
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                            @click="openReview('tolak', '{{ route('kabid.packages.travel-orders.reject', [$to->package, $to]) }}', '{{ addslashes($to->tempat_tujuan) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-700 bg-white border border-rose-200 rounded-lg hover:bg-rose-50 transition-colors" title="Tolak">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button"
                                            @click="openReview('revisi', '{{ route('kabid.packages.travel-orders.revise', [$to->package, $to]) }}', '{{ addslashes($to->tempat_tujuan) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-amber-700 bg-white border border-amber-200 rounded-lg hover:bg-amber-50 transition-colors" title="Minta Revisi">
                                            <i data-lucide="file-warning" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <form method="POST" action="{{ route('kabid.packages.travel-orders.approve', [$to->package, $to]) }}" onsubmit="return confirm('Setujui pengajuan SPPD ini?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i> Setujui
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="flex justify-center">
                                        <a href="{{ route('kabid.packages.travel-orders.show', [$to->package, $to]) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
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

    {{-- Modal Revisi / Tolak (bersama) --}}
    <div x-show="review.open" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="review.open = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="review.open = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <form method="POST" :action="review.action">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2"
                    :class="review.mode === 'tolak' ? 'bg-rose-50/60' : 'bg-amber-50/60'">
                    <span class="w-7 h-7 rounded-lg border flex items-center justify-center"
                        :class="review.mode === 'tolak' ? 'bg-rose-100 border-rose-200 text-rose-600' : 'bg-amber-100 border-amber-200 text-amber-600'">
                        <i x-show="review.mode === 'tolak'" data-lucide="x-circle" class="w-4 h-4"></i>
                        <i x-show="review.mode !== 'tolak'" data-lucide="file-warning" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-bold text-slate-800" x-text="review.mode === 'tolak' ? 'Tolak Pengajuan SPPD' : 'Minta Revisi SPPD'"></h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-400 mb-2">Tujuan: <span class="font-semibold text-slate-600" x-text="review.tujuan"></span></p>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        <span x-text="review.mode === 'tolak' ? 'Alasan Penolakan' : 'Catatan Revisi'"></span> <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan_review" rows="4" required
                        :placeholder="review.mode === 'tolak' ? 'Jelaskan alasan penolakan...' : 'Jelaskan apa yang perlu diperbaiki staf...'"
                        class="w-full rounded-lg border-slate-300 sm:text-sm"
                        :class="review.mode === 'tolak' ? 'focus:border-rose-500 focus:ring-rose-500' : 'focus:border-amber-500 focus:ring-amber-500'"></textarea>
                    <p x-show="review.mode === 'tolak'" class="text-[11px] text-slate-400 mt-1.5">Penolakan bersifat final — staf tidak dapat mengajukan ulang.</p>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                    <button type="button" @click="review.open = false" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white rounded-xl shadow-sm transition-colors whitespace-nowrap"
                        :class="review.mode === 'tolak' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200' : 'bg-amber-500 hover:bg-amber-600 shadow-amber-200'">
                        <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
                        <span x-text="review.mode === 'tolak' ? 'Tolak' : 'Kirim Revisi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:initialized', () => {
        Alpine.effect(() => { setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50); });
    });
</script>
@endcomponent
