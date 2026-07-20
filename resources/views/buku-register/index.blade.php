@component('layouts.kdmp')
@section('title', 'Buku Register')

<x-ui.toast />

<x-ui.workspace title="Buku Register" description="Rekapitulasi nomor dan tanggal dokumen pengadaan, dikelompokkan per program.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="book-marked" class="w-4 h-4 text-emerald-500"></i>
            {{ $groupedPackages->count() }} Program
        </div>
    </x-slot:actions>

    @forelse($groupedPackages as $programName => $packages)
        <section x-data="{ open: true }" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="folder" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 truncate">{{ $programName }}</h3>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="printTable('table-program-{{ $loop->index }}', '{{ addslashes($programName) }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                    </button>
                    <button type="button" x-on:click="open = !open"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 transition-colors" title="Sembunyikan / Tampilkan">
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': !open }"></i>
                    </button>
                </div>
            </div>

            <div x-show="open">
                <div class="overflow-x-auto" id="table-program-{{ $loop->index }}">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-12">No</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[200px]">Nama Paket</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Surat Permohonan</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Surat Pesanan</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">BAST</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">BAP</th>
                                <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Kwitansi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($packages as $index => $item)
                                @php $kodeProgram = $item->package?->program?->kode ?? '2.11.04'; @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors align-top">
                                    <td class="px-4 py-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900 leading-snug">{{ $item->package?->nama_paket ?? '-' }}</td>

                                    {{-- Surat Permohonan --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->procurementRequest?->nomor_surat ? '000.3.2/'.$item->procurementRequest->nomor_surat.'/SP-PBJ/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->procurementRequest?->tanggal_surat ? \Carbon\Carbon::parse($item->procurementRequest->tanggal_surat)->format('d/m/Y') : '-' }}</div>
                                    </td>

                                    {{-- Surat Pesanan --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->procurementProcess?->nomor_surat_pesanan ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->procurementProcess?->tanggal_surat_pesanan ? \Carbon\Carbon::parse($item->procurementProcess->tanggal_surat_pesanan)->format('d/m/Y') : '-' }}</div>
                                    </td>

                                    {{-- BAST --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->payment?->nomor_bast ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->payment?->tanggal_bast ? \Carbon\Carbon::parse($item->payment->tanggal_bast)->format('d/m/Y') : '-' }}</div>
                                    </td>

                                    {{-- Invoice --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->payment?->nomor_invoice ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->payment?->tanggal_invoice ? \Carbon\Carbon::parse($item->payment->tanggal_invoice)->format('d/m/Y') : '-' }}</div>
                                    </td>

                                    {{-- BAP --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->payment?->nomor_bap ? $item->payment->nomor_bap.'/BAP/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->payment?->tanggal_bap ? \Carbon\Carbon::parse($item->payment->tanggal_bap)->format('d/m/Y') : '-' }}</div>
                                    </td>

                                    {{-- Kwitansi --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $item->payment?->nomor_kwitansi ? $item->payment->nomor_kwitansi.'/KWT/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                        <div class="text-xs italic text-slate-400 mt-0.5">{{ $item->payment?->tanggal_kwitansi ? \Carbon\Carbon::parse($item->payment->tanggal_kwitansi)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada data paket pengadaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @empty
        <x-ui.empty-state icon="book-marked" title="Belum Ada Data" description="Belum ada data dokumen pengadaan untuk ditampilkan pada buku register." />
    @endforelse
</x-ui.workspace>

<script>
    function printTable(containerId, programName) {
        const tableHtml = document.getElementById(containerId).innerHTML;

        let oldIframe = document.getElementById('print-iframe');
        if (oldIframe) {
            oldIframe.remove();
        }

        const iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Cetak Buku Register - ${programName}</title>
                <style>
                    body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 0.3rem; vertical-align: middle; font-size: 10pt; }
                    thead th { text-align: center; font-weight: bold; background: #f4f6f9; }
                    .title-header { text-align: center; margin-bottom: 20px; font-weight: bold; }
                    .text-xs { font-size: 9pt; color: #555; font-style: italic; }
                    @media print {
                        @page { size: landscape; margin: 1cm; }
                    }
                </style>
            </head>
            <body>
                <div class="title-header">
                    <h4>BUKU REGISTER DOKUMEN PENGADAAN</h4>
                    <h5>${programName.toUpperCase()}</h5>
                </div>
                ${tableHtml}
            </body>
            </html>
        `);
        doc.close();

        setTimeout(function () {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 500);
    }
</script>
@endcomponent
