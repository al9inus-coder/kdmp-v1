@component('layouts.kdmp')
@section('title', 'Belanja Lembur - ' . $package->nama_paket)

<x-ui.toast />

<x-ui.workspace title="Modul Belanja Lembur" description="Paket: {{ $package->nama_paket }} (Tahun {{ $package->created_at ? $package->created_at->format('Y') : date('Y') }})">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.show', $package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali ke Paket
        </x-ui.button>
    </x-slot:actions>

    <div class="mb-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i data-lucide="calendar-clock" class="w-4 h-4 text-amber-500"></i> Pilih Bulan Rekapitulasi</h3>
    </div>

    @php
        $months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($months as $num => $name)
            @php $exists = \App\Models\Overtime::where('package_id', $package->id)->where('bulan', $num)->exists(); @endphp
            <a href="{{ route('packages.overtimes.show', [$package, $num]) }}"
                class="group block rounded-2xl border bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg {{ $exists ? 'border-amber-200 hover:border-amber-300' : 'border-slate-200 hover:border-slate-300' }}">
                <div class="flex flex-col items-center justify-center text-center py-7 px-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 {{ $exists ? 'bg-amber-50 text-amber-500' : 'bg-slate-100 text-slate-400' }}">
                        <i data-lucide="{{ $exists ? 'calendar-check' : 'calendar' }}" class="w-7 h-7"></i>
                    </div>
                    <h5 class="font-bold text-slate-900">{{ $name }}</h5>
                    @if($exists)
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Data Tersedia</span>
                    @else
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">Belum Diisi</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</x-ui.workspace>
@endcomponent
