<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Surat Permohonan Pengadaan</title>
<style>
/* @page {
    size: A4 portrait;
    margin: 10mm 10mm;
} */

body {
    background: #f4f6f9;
    font-family: Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.4;
    margin: 0;
    padding: 20px;
}

.document-viewer {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.document-paper {
    background: white;
    width: 210mm;
    min-height: 297mm;
    padding: 15mm;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    box-sizing: border-box;
}

.btn-print {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 1000;
}

.btn-print:hover {
    background: #0056b3;
}

@media print {
    body {
        background: white;
        padding: 0;
    }
    
    .no-print {
        display: none !important;
    }
    
    .document-paper {
        box-shadow: none;
        margin: 0;
        width: 100%;
        min-height: auto;
        padding: 0;
    }

    table { page-break-inside: auto; }
    .spesifikasi-table tr { page-break-inside: avoid; page-break-after: auto; }
    .spesifikasi-table thead { display: table-header-group; }
}

/* KOP SURAT */
.kop-pemerintah{ font-size:14pt; text-transform:uppercase; line-height:1.1; margin-bottom:2px; }
.kop-dinas{ font-size:15pt; font-weight:bold; text-transform:uppercase; line-height:1.15; margin-bottom:4px; }
.kop-alamat{ font-size:10pt; line-height:1.1; margin-bottom:0; }

/* UTILITY CLASSES */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-justify { text-align: justify; }
.font-weight-bold { font-weight: bold; }
.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.ml-4 { margin-left: 1.5rem; }

/* GRID UTILITIES */
.row { display: flex; flex-wrap: wrap; width: 100%; }
.col-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
.col-5 { flex: 0 0 41.666667%; max-width: 41.666667%; }
.col-7 { flex: 0 0 58.333333%; max-width: 58.333333%; }
.col-10 { flex: 0 0 83.333333%; max-width: 83.333333%; }
.col-12 { flex: 0 0 100%; max-width: 100%; }

/* TABLES */
.admin-table { border-collapse: collapse; width: 100%; }
.admin-table td { border: none; padding: 1px 0; vertical-align: top; }
.label-col { width: 180px; white-space: nowrap; }
.colon-col { width: 25px; text-align: center; }

.spesifikasi-table { width: 100%; border-collapse: collapse; font-size: 11pt; }
.spesifikasi-table th, .spesifikasi-table td { border: 1px solid #000; padding: 6px; vertical-align: top; }
.spesifikasi-table th { text-align: center; font-weight: bold; }

.ttd-area{ margin-top:20px; }

</style>
</head>
<body>

@if(!request('embed'))
<button class="btn-print no-print" onclick="window.print()">Cetak</button>
@endif

<div class="document-viewer">
    <div class="document-paper">
        @include('procurement-requests._document')

    </div>
</div>

</body>
</html>
