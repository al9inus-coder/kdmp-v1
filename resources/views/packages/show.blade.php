@component('layouts.kdmp')
@section('title', 'Detail Paket Pekerjaan')

<x-ui.toast />

@php
    $statusBadge = match($package->status) {
        'needs_review' => ['danger', 'Needs Review'],
        'draft' => ['warning', 'Draft'],
        'submitted' => ['info', 'Submitted'],
        'approved' => ['success', 'Approved'],
        default => ['draft', $package->status],
    };
@endphp

<x-ui.workspace title="Detail Paket Pekerjaan" description="{{ $package->nama_paket }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <div class="max-w-4xl">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="package" class="w-4 h-4"></i></div>
                    <h3 class="text-sm font-bold text-slate-900">Informasi Paket</h3>
                </div>
                <x-ui.badge :variant="$statusBadge[0]">{{ $statusBadge[1] }}</x-ui.badge>
            </div>
            <dl class="divide-y divide-slate-100">
                @php
                    $rows = [
                        ['ID RUP', $package->id_rup ?? '-'],
                        ['Nama Paket', $package->nama_paket],
                        ['Tahun Anggaran', $package->fiscalYear->tahun ?? '-'],
                        ['Program', trim(($package->program?->kode ?? '').($package->program ? ' - '.$package->program->nama : '')) ?: '-'],
                        ['Kegiatan', trim(($package->activity?->kode ?? '').($package->activity ? ' - '.$package->activity->nama : '')) ?: '-'],
                        ['Sub Kegiatan', trim(($package->subActivity?->kode ?? '').($package->subActivity ? ' - '.$package->subActivity->nama : '')) ?: '-'],
                        ['Rekening', trim(($package->account?->kode ?? '').($package->account ? ' - '.$package->account->nama : '')) ?: '-'],
                    ];
                @endphp
                @foreach($rows as [$label, $value])
                    <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                        <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">{{ $label }}</dt>
                        <dd class="text-sm text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Pagu</dt>
                    <dd class="text-sm font-bold text-emerald-700">Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Jenis Pengadaan</dt>
                    <dd class="text-sm text-slate-800">{{ $package->jenis_pengadaan ?? '-' }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Metode Pengadaan</dt>
                    <dd class="text-sm text-slate-800">{{ $package->metode_pengadaan ?? '-' }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Pemilihan Mulai / Selesai</dt>
                    <dd class="text-sm text-slate-800">{{ bulanIndonesia($package->pemilihan_mulai_bulan) }} / {{ bulanIndonesia($package->pemilihan_selesai_bulan) }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Kontrak Mulai / Selesai</dt>
                    <dd class="text-sm text-slate-800">{{ bulanIndonesia($package->kontrak_mulai_bulan) }} / {{ bulanIndonesia($package->kontrak_selesai_bulan) }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Diajukan</dt>
                    <dd class="text-sm text-slate-800">{{ $package->submitted_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                </div>
                <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                    <dt class="w-full sm:w-56 text-sm font-semibold text-slate-500 shrink-0">Disetujui</dt>
                    <dd class="text-sm text-slate-800">{{ $package->approved_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                </div>
            </dl>

            {{-- Action bar --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    @if($package->status === 'draft')
                        @can('submit', $package)
                            <form action="{{ route('packages.submit', $package) }}" method="POST">
                                @csrf
                                <x-ui.button variant="primary" size="md" type="submit"><i data-lucide="send" class="w-4 h-4 mr-2"></i> Ajukan</x-ui.button>
                            </form>
                        @endcan
                    @endif

                    @if($package->status === 'submitted')
                        @can('approve', $package)
                            <form action="{{ route('packages.approve', $package) }}" method="POST">
                                @csrf
                                <x-ui.button variant="success" size="md" type="submit"><i data-lucide="check" class="w-4 h-4 mr-2"></i> Setujui</x-ui.button>
                            </form>
                        @endcan
                        @can('returnToDraft', $package)
                            <form action="{{ route('packages.return', $package) }}" method="POST">
                                @csrf
                                <x-ui.button variant="danger" size="md" type="submit"><i data-lucide="undo-2" class="w-4 h-4 mr-2"></i> Kembalikan ke Draft</x-ui.button>
                            </form>
                        @endcan
                    @endif

                    @if($package->isComplete() && !$package->procurementPackage)
                        <form action="{{ route('packages.procurement-packages.store', $package) }}" method="POST">
                            @csrf
                            @if($package->jenis_pengadaan === 'Swakelola')
                                @php
                                    $accountName = strtolower($package->account?->nama ?? '');
                                    $btnLabel = 'Buat Ruang Swakelola';
                                    if (str_contains($accountName, 'perjalanan dinas')) {
                                        $btnLabel = 'Buat Ruang Eksekusi Perjalanan Dinas';
                                    } elseif (str_contains($accountName, 'lembur')) {
                                        $btnLabel = 'Buat Ruang Eksekusi Lembur';
                                    }
                                @endphp
                                <x-ui.button variant="primary" size="md" type="submit"><i data-lucide="folder-plus" class="w-4 h-4 mr-2"></i> {{ $btnLabel }}</x-ui.button>
                            @else
                                <x-ui.button variant="success" size="md" type="submit"><i data-lucide="folder-plus" class="w-4 h-4 mr-2"></i> Buat Paket Pengadaan</x-ui.button>
                            @endif
                        </form>
                    @endif

                    @if($package->procurementPackage)
                        <x-ui.button :variant="$package->jenis_pengadaan === 'Swakelola' ? 'primary' : 'success'" size="md" href="{{ route('procurement-packages.show', $package) }}">
                            <i data-lucide="log-in" class="w-4 h-4 mr-2"></i> {{ $package->jenis_pengadaan === 'Swakelola' ? 'Masuk Ruang Swakelola' : 'Masuk Paket Pengadaan' }}
                        </x-ui.button>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if(in_array($package->status, ['needs_review', 'draft']))
                        @can('update', $package)
                            <x-ui.button variant="secondary" size="md" href="{{ route('packages.edit', $package) }}"><i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit / Lengkapi</x-ui.button>
                        @endcan
                    @endif
                    @can('delete', $package)
                        <form action="{{ route('packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                            @csrf @method('DELETE')
                            <x-ui.button variant="danger" size="md" type="submit"><i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Hapus</x-ui.button>
                        </form>
                    @endcan
                </div>
            </div>
        </section>
    </div>
</x-ui.workspace>
@endcomponent
