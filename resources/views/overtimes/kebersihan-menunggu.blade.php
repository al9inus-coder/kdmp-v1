@component('layouts.kdmp')
@section('title', 'Lembur')

{{-- Layar tunggu untuk Staf: mode lembur paket belum ditetapkan kabid/admin,
     atau paket ini bermode Pegawai Dinas (bukan wilayah input staf). --}}

<div class="max-w-xl mx-auto py-16 text-center">
    <div class="w-14 h-14 mx-auto rounded-2xl {{ $mode === 'dinas' ? 'bg-indigo-50 text-indigo-500' : 'bg-amber-50 text-amber-500' }} flex items-center justify-center mb-5">
        <i data-lucide="{{ $mode === 'dinas' ? 'users' : 'hourglass' }}" class="w-7 h-7"></i>
    </div>
    @if($mode === 'dinas')
        <h1 class="text-lg font-black text-slate-900">Lembur Paket Ini Dikelola Kabid/Admin</h1>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Paket <b>{{ $package->nama_paket }}</b> memakai mode <b>Lembur Pegawai Dinas</b>,
            sehingga pengisiannya dilakukan oleh Kabid/Admin — bukan lewat halaman staf.
        </p>
    @else
        <h1 class="text-lg font-black text-slate-900">Mode Lembur Belum Ditetapkan</h1>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Kabid/Admin belum memilih jenis lembur untuk paket <b>{{ $package->nama_paket }}</b>.
            Setelah mode <b>Petugas Kebersihan</b> dipilih, Anda dapat mengunggah data kehadiran di sini.
        </p>
    @endif
    <a href="{{ $backUrl }}"
        class="inline-flex items-center gap-2 mt-6 px-4 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali ke Daftar Lembur
    </a>
</div>
@endcomponent
