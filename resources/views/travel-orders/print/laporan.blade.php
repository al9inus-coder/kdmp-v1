<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            padding: 15mm;
            border: 1px #D3D3D3 solid;
            background: white;
            box-sizing: border-box;
        }
        .isi p { text-align: justify; margin: 4px 0 10px; }
        .bagian { margin-top: 14px; }
        .bagian-judul { display: flex; }
        .bagian-judul .huruf { width: 24px; flex-shrink: 0; font-weight: bold; }
        .bagian-judul .teks { font-weight: bold; }
        .bagian-isi { margin-left: 24px; }
        .sub { display: flex; margin-top: 8px; }
        .sub .nomor { width: 24px; flex-shrink: 0; }

        @media screen {
            html, body { overflow: hidden; }
        }

        @media print {
            .page {
                margin: 0;
                border: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
            }
            .page:not(:last-child) {
                page-break-after: always;
            }
            /* @page {
                size: A4;
                margin: 10mm;
            } */
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">
    @php
        $skpd = \App\Models\Skpd::first();
        $ketua = $travelOrder->personnels->sortBy('urutan')->first();
        $employee = $ketua?->employee;

        $tglBerangkat = $travelOrder->tanggal_berangkat->locale('id')->translatedFormat('d F Y');
        $tglKembali = $travelOrder->tanggal_kembali->locale('id')->translatedFormat('d F Y');
        $periode = $travelOrder->tanggal_berangkat->equalTo($travelOrder->tanggal_kembali)
            ? "tanggal {$tglBerangkat}"
            : "tanggal {$tglBerangkat} s.d. {$tglKembali}";

        $fmtTgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '................';
    @endphp

    <div class="page">
        {{-- KOP INSTANSI --}}
        <table style="width: 100%; border-bottom: 3px double #000; margin-bottom: 15px;">
            <tr>
                <td style="width: 100px; text-align: center; padding-bottom: 10px;">
                    <img src="{{ asset('images/logo-bengkayang.png') }}" alt="Logo" style="width: 80px;">
                </td>
                <td style="text-align: center; padding-bottom: 10px;">
                    <div style="font-size: 14pt;">PEMERINTAH KABUPATEN BENGKAYANG</div>
                    <div style="font-size: 15pt; font-weight: bold;">{{ strtoupper($skpd->nama ?? '') }}</div>
                    <div style="font-size: 10pt;">{{ $skpd->alamat ?? '' }}</div>
                </td>
            </tr>
        </table>

        {{-- Judul --}}
        <div style="text-align: center; margin: 25px 0;">
            <div style="font-weight: bold; letter-spacing: 1px;">LAPORAN</div>
            <div style="font-weight: bold; letter-spacing: 1px;">TENTANG</div>
            <div style="font-weight: bold; text-transform: uppercase; margin-top: 6px; text-decoration: underline;">
                {{ $travelOrder->maksud_perjalanan }}
            </div>
        </div>

        <div class="isi">
            {{-- A. Pendahuluan --}}
            <div class="bagian">
                <div class="bagian-judul"><span class="huruf">A.</span><span class="teks">Pendahuluan</span></div>
                <div class="bagian-isi">
                    <div class="sub">
                        <span class="nomor">1.</span>
                        <div>
                            <strong>Umum/Latar Belakang</strong>
                            <p>{!! nl2br(e($report->hasil_latar_belakang ?: '-')) !!}</p>
                        </div>
                    </div>
                    <div class="sub">
                        <span class="nomor">2.</span>
                        <div>
                            <strong>Landasan Hukum</strong>
                            <p>
                                @if ($travelOrder->dasar_pelaksanaan)
                                    {{ $travelOrder->dasar_pelaksanaan }};<br>
                                @endif
                                Surat Tugas Nomor {{ $report->nomor_surat_tugas ?: '................' }} tanggal {{ $fmtTgl($report->tanggal_surat_tugas) }};<br>
                                Surat Perjalanan Dinas (SPD) Nomor {{ $report->nomor_spd ?: '................' }} tanggal {{ $fmtTgl($report->tanggal_spd) }}.
                            </p>
                        </div>
                    </div>
                    <div class="sub">
                        <span class="nomor">3.</span>
                        <div>
                            <strong>Maksud dan Tujuan</strong>
                            <p>
                                Melaksanakan perjalanan dinas dalam rangka {{ $travelOrder->maksud_perjalanan }}
                                di {{ $travelOrder->tempat_tujuan }}, {{ $periode }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- B. Kegiatan yang dilaksanakan --}}
            <div class="bagian">
                <div class="bagian-judul"><span class="huruf">B.</span><span class="teks">Kegiatan yang Dilaksanakan</span></div>
                <div class="bagian-isi">
                    <p>{!! nl2br(e($report->hasil_kegiatan ?: '-')) !!}</p>
                </div>
            </div>

            {{-- C. Hasil yang dicapai --}}
            <div class="bagian">
                <div class="bagian-judul"><span class="huruf">C.</span><span class="teks">Hasil yang Dicapai</span></div>
                <div class="bagian-isi">
                    <p>{!! nl2br(e($report->hasil_dicapai ?: '-')) !!}</p>
                </div>
            </div>

            {{-- D. Kesimpulan dan Saran --}}
            <div class="bagian">
                <div class="bagian-judul"><span class="huruf">D.</span><span class="teks">Kesimpulan dan Saran</span></div>
                <div class="bagian-isi">
                    <p>{!! nl2br(e($report->hasil_kesimpulan ?: '-')) !!}</p>
                </div>
            </div>

            {{-- E. Penutup --}}
            <div class="bagian">
                <div class="bagian-judul"><span class="huruf">E.</span><span class="teks">Penutup</span></div>
                <div class="bagian-isi">
                    <p>{!! nl2br(e($report->hasil_penutup ?: '-')) !!}</p>
                </div>
            </div>
        </div>

        {{-- Blok tanda tangan --}}
        <table style="width: 100%; margin-top: 40px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <table style="border-collapse: collapse;">
                        <tr><td>Dibuat di</td><td style="padding: 0 4px;">:</td><td>Bengkayang</td></tr>
                        <tr><td>Pada tanggal</td><td style="padding: 0 4px;">:</td><td>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</td></tr>
                    </table>
                    <div style="margin-top: 10px;">{{ $employee?->jabatan ?? 'Pelaksana' }},</div>
                    <br><br><br>
                    <div style="font-weight: bold; text-decoration: underline;">{{ $employee?->nama ?? '................' }}</div>
                    <div>{{ $employee?->golongan ? 'Gol. ' . $employee->golongan : '' }}</div>
                    <div>NIP. {{ $employee?->nip ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
