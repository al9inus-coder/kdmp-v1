@component('layouts.kdmp')

@section('title', 'Surat Permohonan Pengadaan')

@slot('header')
@endslot

<style>
/* TOMBOL MELENGKUNG (PILL) & EFEK HOVER */
    .btn-floating {
        border-radius: 50px !important;
        padding: 12px 25px !important;
        font-weight: bold;
        font-size: 1.05rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }

    .btn-floating:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.4) !important;
    }

    .btn-warning.btn-floating {
        color: #212529 !important; 
    }

    /* WADAH TOMBOL STICKY (Otomatis menyesuaikan lebar document-viewer) */
    .viewer-sticky-actions {
        position: sticky;
        /* Menahan posisi tombol agar selalu ~160px dari dasar layar monitor */
        top: calc(100vh - 160px); 
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        
        /* Jarak tombol dari tepi kiri dan tepi kanan document-viewer (kotak abu-abu) */
        padding: 0 40px; 
        
        /* Tinggi dibuat 0 agar tidak memakan ruang dan mendorong kertas ke bawah */
        height: 0; 
        pointer-events: none; /* Area kosong transparan tidak menghalangi klik ke dokumen */
    }

    /* Kembalikan fungsi klik khusus untuk area yang ada tombolnya */
    .viewer-sticky-actions .action-left,
    .viewer-sticky-actions .action-right {
        pointer-events: auto;
    }

    /* Susun tombol ke bawah rata kanan */
    .viewer-sticky-actions .action-right {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* Latar Belakang bergaya PDF Viewer */
    .document-viewer {
        background-color: #a7a7a7;
        padding: 30px 0;
        border-radius: 5px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
    }

    /* Pengaturan Kertas A4 Dinamis */
    .document-paper {
        width: 210mm;
        min-height: 297mm;
        margin: auto;
        
        /* Padding standar dokumen resmi (Atas: 3cm, Kanan: 2cm, Bawah: 2cm, Kiri: 3cm) */
        padding: 11mm 15mm 11mm 15mm;
        background: #fff;
        
        font-family: Arial, sans-serif;
        font-size: 11pt; /* Lebih proporsional untuk cetak */
        color: #000;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        position: relative;
    }

    .kop-pemerintah{
        font-size:14pt;
        text-transform:uppercase;
        line-height:1.1;
        margin-bottom:2px;
    }

    .kop-dinas{
        font-size:15pt;
        font-weight:bold;
        text-transform:uppercase;
        line-height:1.15;
        margin-bottom:4px;
    }

    .kop-alamat{
        font-size:10pt;
        line-height:1.1;
        margin-bottom:0;
    }

    .judul-dokumen{
        font-size:12pt;
        font-weight:bold;
        text-transform:uppercase;
    }

    /* Input Editor dalam Dokumen */
    .doc-editor {
        width: 100%;
        border: 1px dashed #adb5bd; /* Tanda bahwa ini form yang bisa diedit */
        border-radius: 4px;
        padding: 10px;
        font-family: Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.5;
        resize: vertical;
        min-height: 100px;
        background: #fdfdfd;
        transition: all 0.3s ease;
    }

    .doc-editor:focus {
        border: 1px solid #007bff;
        background: #fff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        outline: none;
    }

    .section-row{
        margin-bottom:20px;
    }

    .ttd-area{
        margin-top:20px;
    }

    .doc-mini-table{
        width:100%;
        font-size:12pt;
    }

    .doc-mini-table td{
        padding:1px 4px;
        border:none !important;
        vertical-align:top;
    }

    .doc-mini-table tr{
        border:none !important;
    }

    .spesifikasi-table{
        width:100%;
        border-collapse:collapse;
        font-size:11pt;
    }

    .spesifikasi-table th,
    .spesifikasi-table td{
        border:1px solid #000;
        padding:6px;
        vertical-align:top;
    }

    .spesifikasi-table th{
        text-align:center;
        font-weight:bold;
    }

    .admin-table {
        border-collapse: collapse;
        width: 100%;
    }

    .admin-table td {
        border: none;
        padding: 1px 0;
        vertical-align: top;
    }

    .label-col {
        width: 180px;
        white-space: nowrap;
    }

    .colon-col {
        width: 25px;
        text-align: center;
    }
</style>



    @include('components.procurement-progress', [
        'procurementPackage' => $procurementPackage
    ])

    @if(session('success'))
        <div class="p-6 mb-6 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-start">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- DOKUMEN VIEWER (A4) --}}
    <div class="document-viewer">
        {{-- TOMBOL FLOATING (OTOMATIS TERKURUNG DI DALAM VIEWER) --}}
        <div class="viewer-sticky-actions">
            {{-- Bagian Kiri: Tombol Kembali --}}
            <div class="action-left">
                <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.show', $procurementPackage->package) }}" 
                   class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 -floating">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
            {{-- Bagian Kanan: Tombol Edit & Cetak --}}
            <div class="action-right">
                <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-request.edit', $procurementPackage->package) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 -floating">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>

                <button type="button"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 -floating"
                   onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-request.print', $procurementPackage->package) }}')">
                    <i class="fas fa-print mr-1"></i> Cetak PDF
                </button>
                
                @if($procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_DRAFT)
                    <button type="button" 
                            id="btn-complete-preparation" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 -floating"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
                        <i class="fas fa-check-double mr-1"></i> Selesaikan Persiapan
                    </button>
                @endif
            </div>
        </div>

        <form id="complete-preparation-form"
            action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.complete-preparation', $procurementPackage->package) }}"
            method="POST"
            style="display:none;">
            @csrf
        </form>

        <div class="document-paper">
            @include('procurement-requests._document')

        </div>
    </div>


@push('js')
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
    iframe.onload = function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}

const completeBtn = document.getElementById('btn-complete-preparation');
if (completeBtn) {
    completeBtn.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin dokumen Persiapan Pengadaan sudah lengkap? Status ini akan dikunci dan dilanjutkan ke tahap Proses Pengadaan.')) {
            document.getElementById('complete-preparation-form').submit();
        }
    });
}
</script>
@endpush
@endcomponent
