@component('layouts.kdmp')
@section('title', 'Referensi Harga')

<x-ui.toast />

<x-ui.workspace title="Referensi Harga" description="{{ $procurementPackage->package->nama_paket }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('procurement-packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    {{-- Progress bar --}}
    <div class="mb-6">
        @include('components.procurement-progress', ['procurementPackage' => $procurementPackage])
    </div>

    {{-- Ribbon info paket --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-slate-200 border border-slate-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
        <div class="bg-white px-5 py-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Paket</p>
            <p class="text-sm font-bold text-slate-900 leading-snug">{{ $procurementPackage->package->nama_paket }}</p>
        </div>
        <div class="bg-white px-5 py-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">ID RUP</p>
            <p class="text-sm font-bold text-slate-900 font-mono">{{ $procurementPackage->package->id_rup ?? '-' }}</p>
        </div>
        <div class="bg-white px-5 py-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pagu Paket</p>
            <p class="text-base font-bold text-emerald-700">Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}</p>
        </div>
    </div>

    <h4 class="text-base font-bold text-slate-900 mb-4">Daftar Referensi Harga</h4>

    @forelse($technicalItems as $item)
        @php
            $namaBarang = $item->nama_barang_jasa;
            $references = $groupedReferences[$namaBarang] ?? collect();
            // Statistik hanya dari referensi berharga > 0 — harga 0 berarti
            // penyedia tidak memiliki barang, bukan penawaran termurah.
            $validReferences = $references->filter(fn ($r) => (float) $r->harga_satuan > 0);
            $rataRataHargaSatuan = $validReferences->avg('harga_satuan') ?? 0;
            $rataRataJumlahHarga = $validReferences->avg('jumlah_harga') ?? 0;
            $hargaTerendah = $validReferences->min('harga_satuan') ?? 0;
        @endphp
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-5">
            {{-- Header barang --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <h5 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4 text-sky-500"></i> {{ $item->nama_barang_jasa }}
                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold bg-sky-50 text-sky-700 border border-sky-100">{{ $references->count() }}/3 Referensi</span>
                    </h5>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs bg-white border border-slate-200 text-slate-600">Volume: <strong class="text-slate-800">{{ number_format((float) $item->volume, 0, ',', '.') }} {{ $item->satuan }}</strong></span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs bg-white border border-slate-200 text-slate-600">Harga Satuan DPA: <strong class="text-slate-800">Rp {{ number_format($item->harga_satuan_dpa ?? 0, 0, ',', '.') }}</strong></span>
                </div>
                <a href="{{ route('procurement-packages.price-references.create', ['package' => $procurementPackage->package, 'technical_specification_item_id' => $item->id]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Referensi
                </a>
            </div>

            {{-- Tabel referensi --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-12">No</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Produk Etalase</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Pelaku Usaha</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah Harga</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Link</th>
                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($references->isEmpty())
                            <tr><td colspan="7" class="px-4 py-6 text-center">
                                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm bg-amber-50 text-amber-700 border border-amber-200"><i data-lucide="alert-circle" class="w-4 h-4"></i> Belum ada referensi harga untuk barang ini.</span>
                            </td></tr>
                        @else
                            @foreach($references as $index => $priceReference)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 text-center text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $priceReference->nama_produk_etalase ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $priceReference->nama_pelaku_usaha ?? '-' }}</td>
                                    @if((float) $priceReference->harga_satuan > 0)
                                        <td class="px-4 py-3 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format((float) $priceReference->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-700 tabular-nums">Rp {{ number_format((float) $priceReference->jumlah_harga, 0, ',', '.') }}</td>
                                    @else
                                        <td colspan="2" class="px-4 py-3 text-right">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="package-x" class="w-3 h-3"></i> Tidak tersedia
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-center">
                                        @if($priceReference->link_produk)
                                            <a href="{{ $priceReference->link_produk }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-100 hover:bg-sky-100 transition-colors"><i data-lucide="external-link" class="w-3 h-3"></i> Buka</a>
                                        @else - @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('procurement-packages.price-references.edit', [$procurementPackage->package, $priceReference]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                            <form action="{{ route('procurement-packages.price-references.destroy', [$procurementPackage->package, $priceReference]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus referensi harga ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Footer stats --}}
            <div class="px-6 py-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl bg-sky-50 border border-sky-100 px-4 py-2.5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata Satuan</p>
                    <p class="text-base font-bold text-sky-700">Rp {{ number_format((float) $rataRataHargaSatuan, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-2.5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata Jumlah</p>
                    <p class="text-base font-bold text-emerald-700">Rp {{ number_format((float) $rataRataJumlahHarga, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-2.5">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Harga Terendah</p>
                    <p class="text-base font-bold text-amber-700">Rp {{ number_format((float) $hargaTerendah, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>
    @empty
        <x-ui.empty-state icon="tag" title="Belum Ada Referensi Harga" description="Belum ada referensi harga yang ditambahkan untuk paket ini." />
    @endforelse

    {{-- Navigasi bawah --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('procurement-packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button variant="secondary" size="md" type="button" onclick="printPdf('{{ route('procurement-packages.price-references.print', $procurementPackage->package) }}')">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Cetak Referensi Harga
            </x-ui.button>
            @if($procurementPackage->procurementRequest)
                <x-ui.button variant="primary" size="md" href="{{ route('procurement-packages.procurement-request.show', $procurementPackage->package) }}">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Lihat Surat Permohonan
                </x-ui.button>
            @else
                <x-ui.button variant="success" size="md" href="{{ route('procurement-packages.procurement-request.create', $procurementPackage->package) }}">
                    Lanjut Surat Permohonan <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </x-ui.button>
            @endif
        </div>
    </div>
</x-ui.workspace>

<script>
function printPdf(url) {
    let iframe = document.getElementById('print-iframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
    }
    iframe.src = url;
    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}
</script>
@endcomponent
