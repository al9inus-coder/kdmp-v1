@component('layouts.kdmp')
    @section('title', 'Daftar SPPD')

    <div class="space-y-6" x-data="{ showPicker: false, pickedPackage: '' }">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="plane" class="w-6 h-6 text-indigo-600"></i>
                    Surat Perintah <span class="text-indigo-600">Perjalanan Dinas</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Ajukan dan pantau status persetujuan SPPD Anda.</p>
            </div>
            <button type="button" @click="showPicker = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200 shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Ajukan SPPD
            </button>
        </div>

        {{-- Filter panel --}}
        @php
            $statusTabs = [
                '' => [
                    'label' => 'Semua',
                    'dot' => 'bg-slate-400',
                    'active' => 'text-indigo-700 border-indigo-600 bg-indigo-50/60',
                ],
                'draft' => [
                    'label' => 'Draf',
                    'dot' => 'bg-slate-400',
                    'active' => 'text-slate-700 border-slate-500 bg-slate-50',
                ],
                'submitted' => [
                    'label' => 'Diajukan',
                    'dot' => 'bg-blue-500',
                    'active' => 'text-blue-700 border-blue-500 bg-blue-50/60',
                ],
                'revision' => [
                    'label' => 'Perlu Revisi',
                    'dot' => 'bg-amber-500',
                    'active' => 'text-amber-700 border-amber-500 bg-amber-50/60',
                ],
                'approved' => [
                    'label' => 'Disetujui',
                    'dot' => 'bg-emerald-500',
                    'active' => 'text-emerald-700 border-emerald-500 bg-emerald-50/60',
                ],
                'rejected' => [
                    'label' => 'Ditolak',
                    'dot' => 'bg-rose-500',
                    'active' => 'text-rose-700 border-rose-500 bg-rose-50/60',
                ],
            ];
        @endphp

        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="flex items-end gap-1 px-3 pt-2 border-b border-slate-200 overflow-x-auto">
                @foreach ($statusTabs as $value => $tab)
                    @php
                        $count = $value === '' ? $statusCounts->sum() : $statusCounts[$value] ?? 0;
                        $isActive = ($status ?? '') === $value;
                        $tabUrl = route(
                            'staf.sppd.index',
                            array_filter(
                                array_merge(
                                    request()->except(['status', 'page']),
                                    $value !== '' ? ['status' => $value] : [],
                                ),
                            ),
                        );
                    @endphp
                    <a href="{{ $tabUrl }}"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap rounded-t-xl border-b-2 transition-colors
                        {{ $isActive ? $tab['active'] : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $tab['dot'] }}"></span>
                        {{ $tab['label'] }}
                        <span
                            class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-white/80 text-slate-700' : 'bg-slate-100 text-slate-500' }}">{{ $count }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('staf.sppd.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
                @if ($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Cari tujuan, maksud, atau paket...">
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
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
                                $duration =
                                    $to->tanggal_berangkat && $to->tanggal_kembali
                                        ? $to->tanggal_berangkat->diffInDays($to->tanggal_kembali) + 1
                                        : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors group cursor-pointer"
                                onclick="window.location.href='{{ route('staf.packages.travel-orders.show', [$to->package, $to]) }}'">
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($to->personnels as $personnel)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                                <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                                {{ $personnel->employee?->nama ?? 'Pegawai' }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 font-semibold">Belum ada pelaksana</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                        {{ $to->maksud_perjalanan }}</p>
                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        {{ $to->tempat_tujuan }}
                                    </p>
                                    @if ($to->status === \App\Models\TravelOrder::STATUS_REVISION && $to->catatan_review)
                                        <p class="text-[11px] text-amber-600 font-semibold mt-1 flex items-center gap-1">
                                            <i data-lucide="message-square-warning" class="w-3 h-3"></i>
                                            {{ Str::limit($to->catatan_review, 50) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <p class="font-medium text-slate-700">
                                        {{ $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y') }}</p>
                                    <p class="text-slate-400">s.d.
                                        {{ $to->tanggal_kembali?->locale('id')->translatedFormat('d M Y') }}</p>
                                    <span
                                        class="mt-1 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 font-semibold">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        {{ $duration }} hari
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-semibold text-slate-700 line-clamp-2">
                                        {{ $to->package?->subActivity?->nama ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        {{ $to->package?->subActivity?->kode ?? ($to->package?->nama_paket ?? '-') }}</p>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border {{ $meta['badge'] }}">
                                        <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i> {{ $meta['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('staf.packages.travel-orders.show', [$to->package, $to]) }}"
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
                                        <div
                                            class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                            <i data-lucide="plane" class="w-8 h-8 text-slate-400"></i>
                                        </div>
                                        <p class="font-medium text-slate-600">Belum ada pengajuan SPPD.</p>
                                        <p class="text-sm mt-1">Klik "Ajukan SPPD" untuk membuat pengajuan pertama.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($travelOrders->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $travelOrders->links() }}
                </div>
            @endif
        </div>

        {{-- Modal pilih sub kegiatan untuk Ajukan SPPD --}}
        <div x-show="showPicker" style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" @keydown.escape.window="showPicker = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showPicker = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="px-5 py-4 border-b border-slate-100 bg-indigo-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span
                            class="w-7 h-7 rounded-lg bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-500">
                            <i data-lucide="plane" class="w-4 h-4"></i>
                        </span>
                        Ajukan SPPD Baru
                    </h3>
                    <button type="button" @click="showPicker = false"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="p-5 space-y-3">
                    @if ($eligibleSubActivities->isEmpty())
                        <div
                            class="rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-700 font-semibold flex items-start gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            Belum ada sub kegiatan yang memiliki belanja perjalanan dinas. Buat/impor paketnya terlebih dahulu.
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Sub Kegiatan</label>
                            <select x-model="pickedPackage"
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">-- Pilih sub kegiatan --</option>
                                @foreach ($eligibleSubActivities as $subActivity)
                                    @php
                                        $travelPackage = $subActivity->packages->first();
                                    @endphp
                                    @if($travelPackage)
                                        <option value="{{ route('staf.packages.travel-orders.create', $travelPackage) }}">
                                            {{ $subActivity->kode ? $subActivity->kode . ' — ' : '' }}{{ Str::limit($subActivity->nama, 72) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1.5">Hanya sub kegiatan yang memiliki paket belanja perjalanan dinas yang ditampilkan.</p>
                        </div>
                    @endif
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                    <button type="button" @click="showPicker = false"
                        class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">
                        Batal
                    </button>
                    <button type="button" @click="if (pickedPackage) window.location.href = pickedPackage"
                        :disabled="!pickedPackage"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i> Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endcomponent
