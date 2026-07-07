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

        {{-- Header: badge ringkas + kembali ke daftar --}}
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
            </div>
            <a href="{{ route('staf.sppd.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Daftar SPPD
            </a>
        </div>

        {{-- Stepper lifecycle --}}
        <x-travel.stepper :travel-order="$travelOrder" :package="$package" active="sppd" />

        {{-- Konten detail SPPD --}}
        @include('staf.travel-orders.partials.detail')
    </div>
@endcomponent
