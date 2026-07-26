@props([
    // Diisi → dirender sebagai tautan (ponsel, menuju halaman /asisten).
    // Kosong → dirender sebagai tombol (layar besar, membuka panel).
    'href' => null,
    // Kelas tambahan untuk mengatur di layar mana tombol ini muncul.
    'tampil' => '',
])

@php
    // Satu tombol, satu arti, satu bentuk — di ponsel maupun layar besar.
    // Warnanya berubah saat ada pekerjaan menunggu; ikon dan angkanya ikut
    // berubah supaya tidak bergantung pada rona semata.
    $antrean = app(\App\Services\AntreanKerja::class)->jumlah(auth()->user());

    $kelas = trim($tampil . ' fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full text-white shadow-xl'
        . ' hover:scale-105 active:scale-95 transition-transform flex items-center justify-center '
        . ($antrean > 0 ? 'bg-amber-500' : 'bg-gradient-to-br from-blue-500 via-violet-500 to-rose-500'));

    $judul = $antrean > 0 ? $antrean . ' hal menunggu tindakan Anda' : 'Asisten KDMP';
@endphp

{{-- Tag pembuka dipisah karena elemennya berbeda (a vs button) sementara
     isinya sama persis. --}}
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas, 'title' => $judul]) }}>
@else
    <button type="button" {{ $attributes->merge(['class' => $kelas, 'title' => $judul]) }}>
@endif

    <i data-lucide="{{ $antrean > 0 ? 'bell-dot' : 'sparkles' }}" class="w-6 h-6"></i>

    @if($antrean > 0)
        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-white text-amber-600 text-[11px] font-bold shadow">
            {{ $antrean > 99 ? '99+' : $antrean }}
        </span>
    @endif

@if($href)
    </a>
@else
    </button>
@endif
