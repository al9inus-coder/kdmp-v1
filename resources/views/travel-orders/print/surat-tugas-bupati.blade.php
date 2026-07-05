<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas Bupati</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        table.layout { width: 100%; border-collapse: collapse; }
        table.layout td { padding: 2px 5px; vertical-align: top; }
        
        @media print {
            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">
    <div class="page">
        <!-- Logo placeholder -->
        <div class="text-center mb-2">
            <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="Logo Garuda" style="height: 80px; filter: grayscale(100%);">
        </div>
        
        <div class="text-center font-weight-bold mb-4">
            BUPATI BENGKAYANG
        </div>

        <div class="text-center mb-4">
            <span style="text-decoration: underline;">SURAT TUGAS</span><br>
            Nomor : ......../........./......./{{ date('Y') }}
        </div>

        <table class="layout mb-4">
            <tr>
                <td style="width: 100px;">Dasar</td>
                <td style="width: 10px;">:</td>
                <td>{{ $travelOrder->dasar_pelaksanaan ?? '-' }}</td>
            </tr>
        </table>

        <div class="text-center mb-4">
            MEMERINTAHKAN :
        </div>

        <table class="layout mb-4">
            <tr>
                <td style="width: 100px;">Kepada</td>
                <td style="width: 10px;">:</td>
                <td>
                    <table class="layout">
                        @foreach($travelOrder->personnels as $index => $personnel)
                            <tr>
                                <td style="width: 120px;">{{ $index === 0 ? 'Nama' : '' }}</td>
                                <td style="width: 10px;">{{ $index === 0 ? ':' : '' }}</td>
                                <td>{{ $personnel->employee->nama }}</td>
                            </tr>
                            <tr>
                                <td>{{ $index === 0 ? 'Pangkat/Gol.' : '' }}</td>
                                <td>{{ $index === 0 ? ':' : '' }}</td>
                                <td>{{ $personnel->employee->golongan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>{{ $index === 0 ? 'NIP.' : '' }}</td>
                                <td>{{ $index === 0 ? ':' : '' }}</td>
                                <td>{{ $personnel->employee->nip ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>{{ $index === 0 ? 'Jabatan' : '' }}</td>
                                <td>{{ $index === 0 ? ':' : '' }}</td>
                                <td>{{ $personnel->employee->jabatan ?? '-' }}</td>
                            </tr>
                            @if(!$loop->last)
                            <tr><td colspan="3"><hr style="border-top: 1px dashed #ccc; margin: 5px 0;"></td></tr>
                            @endif
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr>
                <td>Untuk</td>
                <td>:</td>
                <td>{{ $travelOrder->maksud_perjalanan }} di {{ $travelOrder->tempat_tujuan }}, tanggal {{ $travelOrder->tanggal_berangkat->translatedFormat('d F Y') }} s.d {{ $travelOrder->tanggal_kembali->translatedFormat('d F Y') }}.</td>
            </tr>
        </table>

        <table class="layout" style="margin-top: 50px;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    Bengkayang, {{ $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <br>
                    BUPATI BENGKAYANG
                    <br><br><br><br><br>
                    <span style="text-decoration: underline; font-weight: bold;">SEBASTIANUS DARWIS</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
