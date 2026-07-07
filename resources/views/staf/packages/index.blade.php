@component('layouts.kdmp')
@section('title', 'Daftar Paket Pengadaan')

<div class="space-y-6">
    <x-ui.toast />

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Daftar <span class="text-indigo-600">Paket Pengadaan</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau seluruh paket pengadaan yang ditugaskan kepada Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('packages.import.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors shadow-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Impor Paket
            </a>
            <a href="{{ route('staf.packages.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Paket
            </a>
        </div>
    </div>

    <!-- Filter Panel -->
    @php
        $statusTabs = [
            ''             => ['label' => 'Semua',        'dot' => 'bg-slate-400',   'active' => 'text-indigo-700 border-indigo-600 bg-indigo-50/60'],
            'needs_review' => ['label' => 'Needs Review', 'dot' => 'bg-rose-500',    'active' => 'text-rose-700 border-rose-500 bg-rose-50/60'],
            'draft'        => ['label' => 'Draft',        'dot' => 'bg-amber-500',   'active' => 'text-amber-700 border-amber-500 bg-amber-50/60'],
            'submitted'    => ['label' => 'Submitted',    'dot' => 'bg-blue-500',    'active' => 'text-blue-700 border-blue-500 bg-blue-50/60'],
            'approved'     => ['label' => 'Approved',     'dot' => 'bg-emerald-500', 'active' => 'text-emerald-700 border-emerald-500 bg-emerald-50/60'],
        ];

        $advancedCount = collect([$programId, $activityId, $subActivityId])->filter()->count();

        $activeChips = [];
        if ($search)        $activeChips['search']          = 'Cari: "'.Str::limit($search, 25).'"';
        if ($programId)     $activeChips['program_id']      = 'Program '.($programs->firstWhere('id', $programId)?->kode ?? $programId);
        if ($activityId)    $activeChips['activity_id']     = 'Kegiatan '.($activities->firstWhere('id', $activityId)?->kode ?? $activityId);
        if ($subActivityId) $activeChips['sub_activity_id'] = 'Sub '.($subActivities->firstWhere('id', $subActivityId)?->kode ?? $subActivityId);
    @endphp

    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden"
        x-data="{ filterOpen: {{ $advancedCount ? 'true' : 'false' }} }">

        <!-- Status Tabs -->
        <div class="flex items-end gap-1 px-3 pt-2 border-b border-slate-200 overflow-x-auto">
            @foreach($statusTabs as $value => $tab)
                @php
                    $count    = $value === '' ? $statusCounts->sum() : ($statusCounts[$value] ?? 0);
                    $isActive = ($statusFilter ?? '') === $value;
                    $tabUrl   = route('staf.packages.index', array_filter(array_merge(
                        request()->except(['status', 'page']),
                        $value !== '' ? ['status' => $value] : []
                    )));
                @endphp
                <a href="{{ $tabUrl }}"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap rounded-t-xl border-b-2 transition-colors
                        {{ $isActive ? $tab['active'] : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $tab['dot'] }}"></span>
                    {{ $tab['label'] }}
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-white/80 text-slate-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $count }}
                    </span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('staf.packages.index') }}">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif

            <!-- Toolbar: Search + Toggle Filter -->
            <div class="p-4 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Cari nama paket atau ID RUP, tekan Enter...">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="filterOpen = !filterOpen"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl border transition-colors"
                        :class="filterOpen ? 'text-indigo-700 bg-indigo-50 border-indigo-200' : 'text-slate-600 bg-white border-slate-200 hover:bg-slate-50'">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                        Filter Lanjutan
                        @if($advancedCount)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-600 text-white">{{ $advancedCount }}</span>
                        @endif
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Cari
                    </button>
                </div>
            </div>

            <!-- Advanced Filters (collapsible) -->
            <div x-show="filterOpen" x-transition.opacity.duration.150ms style="display: none;"
                class="px-4 pb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Program</label>
                    <select name="program_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all appearance-none">
                        <option value="">Semua Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected($programId == $program->id)>
                                {{ $program->kode }} - {{ Str::limit($program->nama, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Kegiatan</label>
                    <select name="activity_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all appearance-none">
                        <option value="">Semua Kegiatan</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}" @selected($activityId == $activity->id)>
                                {{ $activity->kode }} - {{ Str::limit($activity->nama, 35) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Sub Kegiatan</label>
                    <select name="sub_activity_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all appearance-none">
                        <option value="">Semua Sub Kegiatan</option>
                        @foreach($subActivities as $sub)
                            <option value="{{ $sub->id }}" @selected($subActivityId == $sub->id)>
                                {{ $sub->kode }} - {{ Str::limit($sub->nama, 30) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Footer: hasil + chips filter aktif -->
        <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 flex flex-wrap items-center gap-2">
            <p class="text-xs text-slate-400 mr-auto">
                Ditemukan <span class="font-semibold text-slate-600">{{ $packages->total() }}</span> paket
            </p>

            @foreach($activeChips as $param => $label)
                <a href="{{ route('staf.packages.index', array_filter(request()->except([$param, 'page']))) }}"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full hover:bg-indigo-100 transition-colors"
                    title="Hapus filter ini">
                    {{ $label }}
                    <i data-lucide="x" class="w-3 h-3"></i>
                </a>
            @endforeach

            @if($activeChips || $statusFilter)
                <a href="{{ route('staf.packages.index') }}"
                    class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition-colors ml-1">
                    Reset semua
                </a>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">ID RUP</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Nama Paket</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Pagu Anggaran</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Jenis Pengadaan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Metode Pengadaan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $package)
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location.href='{{ route('staf.packages.show', $package) }}'">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                {{ $package->id_rup ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-2" title="{{ $package->nama_paket }}">
                                    {{ $package->nama_paket ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-slate-700 whitespace-nowrap">
                                Rp {{ number_format($package->pagu ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $package->jenis_pengadaan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $package->metode_pengadaan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($package->status === 'needs_review')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Needs Review
                                    </span>
                                @elseif($package->status === 'draft')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> Draft
                                    </span>
                                @elseif($package->status === 'submitted')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i> Submitted
                                    </span>
                                @elseif($package->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $package->status ?? '-' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="package-x" class="w-8 h-8 text-slate-400"></i>
                                    </div>
                                    <p class="font-medium text-slate-600">Tidak ada paket ditemukan.</p>
                                    <p class="text-sm mt-1">Coba ubah filter pencarian atau tambah paket baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $packages->firstItem() }}–{{ $packages->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $packages->total() }}</span> paket
                </p>
                <div class="flex items-center gap-1">
                    {{-- Prev --}}
                    @if($packages->onFirstPage())
                        <span class="px-3 py-1.5 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded-lg cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        </span>
                    @else
                        <a href="{{ $packages->previousPageUrl() }}" class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach($packages->getUrlRange(max(1, $packages->currentPage()-2), min($packages->lastPage(), $packages->currentPage()+2)) as $page => $url)
                        @if($page == $packages->currentPage())
                            <span class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 border border-indigo-600 rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($packages->hasMorePages())
                        <a href="{{ $packages->nextPageUrl() }}" class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <span class="px-3 py-1.5 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded-lg cursor-not-allowed">
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $packages->count() }}</span> paket</p>
            </div>
        @endif
    </div>
</div>
@endcomponent
