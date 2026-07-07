@extends('adminlte::page')

@section('title', 'Detail Perjalanan Dinas')

@section('content_header')
    <h1>Detail Perjalanan Dinas</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">Informasi Perjalanan</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 250px;">Tipe Perjalanan</th>
                        <td>{{ ucwords(str_replace('_', ' ', $travelOrder->tipe_perjalanan)) }}</td>
                    </tr>
                    <tr>
                        <th>Dasar Pelaksanaan</th>
                        <td>{{ $travelOrder->dasar_pelaksanaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Maksud Perjalanan</th>
                        <td>{{ $travelOrder->maksud_perjalanan }}</td>
                    </tr>
                    <tr>
                        <th>Tempat Tujuan</th>
                        <td>{{ $travelOrder->tempat_tujuan }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Berangkat</th>
                        <td>{{ $travelOrder->tanggal_berangkat->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Kembali</th>
                        <td>{{ $travelOrder->tanggal_kembali->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th>Lama Perjalanan</th>
                        <td>
                            @php
                                $days = \Carbon\Carbon::parse($travelOrder->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($travelOrder->tanggal_kembali)) + 1;
                                $nights = max(0, $days - 1);
                                if (!empty($estimates)) {
                                    $firstEst = reset($estimates);
                                    if (isset($firstEst['nights'])) {
                                        $nights = $firstEst['nights'];
                                    }
                                }
                            @endphp
                            {{ $days }} Hari {{ $nights > 0 ? $nights . ' Malam' : '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Surat</th>
                        <td>{{ $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat->format('d-m-Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title text-white">Aksi</h3>
            </div>
            <div class="card-body">
                @if(strtolower($travelOrder->tipe_perjalanan) === 'luar_daerah' || strtolower($travelOrder->tipe_perjalanan) === 'luar daerah')
                    <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'permohonan-bupati']) }}" class="btn btn-warning btn-block mb-2"><i class="fas fa-file-word"></i> Nota Dinas</a>
                    <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'surat-tugas-bupati']) }}" class="btn btn-warning btn-block mb-2"><i class="fas fa-file-word"></i> Surat Tugas</a>
                @else
                    <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'surat-tugas-kadis']) }}" class="btn btn-warning btn-block mb-2"><i class="fas fa-file-word"></i> Surat Tugas</a>
                @endif
                <button onclick="printDocument('{{ route('packages.travel-orders.print-html', [$package, $travelOrder, 'sppd']) }}')" class="btn btn-success btn-block mb-2"><i class="fas fa-print"></i> Cetak SPPD</button>
                
                <hr class="my-3">
                <hr class="my-3">

                <a href="{{ route('packages.travel-orders.edit', [$package, $travelOrder]) }}" class="btn btn-primary btn-block mb-2"><i class="fas fa-edit"></i> Edit</a>
                <form action="{{ route('packages.travel-orders.destroy', [$package, $travelOrder]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perjalanan dinas ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block mb-2"><i class="fas fa-trash"></i> Hapus</button>
                </form>
                <a href="{{ route('procurement-packages.show', $package) }}" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mt-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pelaksana Perjalanan Dinas</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Detail Pegawai</th>
                            <th>Standar Biaya</th>
                            <th>Koefisien</th>
                            <th>Biaya Perkiraan</th>
                            <th>Biaya Rampung (SPJ)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalRampung = 0; @endphp
                        @foreach($travelOrder->personnels as $index => $personnel)
                        @php 
                            $est = $estimates[$personnel->id];
                            $perkiraan = $est['uang_harian'] + $est['biaya_penginapan'] + $est['biaya_representasi'] + $est['biaya_transport'];
                            $rampung = $personnel->uang_harian + $personnel->biaya_penginapan + $personnel->biaya_representasi + $personnel->biaya_transport; 
                            $totalRampung += $rampung;
                            
                            $isEselon2 = ($personnel->employee->kategori_biaya === 'Eselon II') || (stripos($personnel->employee->jabatan ?? '', 'kepala dinas') !== false);
                            $isLuarDaerah = (strtolower($travelOrder->tipe_perjalanan) === 'luar_daerah' || strtolower($travelOrder->tipe_perjalanan) === 'luar daerah');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $personnel->employee->nama }}</strong><br>
                                {{ $personnel->employee->jabatan ?? '-' }}<br>
                                <small class="text-muted">NIP. {{ $personnel->employee->nip ?? '-' }}</small><br>
                                <small class="text-muted">Pangkat/Gol. : {{ $personnel->employee->golongan ?? '-' }}</small>
                            </td>
                            <td>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr><td class="p-0">U. Harian</td><td class="p-0 text-right">Rp {{ number_format($est['base_uang_harian'] ?? 0, 0, ',', '.') }}</td></tr>
                                    <tr><td class="p-0">Transport</td><td class="p-0 text-right">Rp {{ number_format($est['biaya_transport'] ?? 0, 0, ',', '.') }}</td></tr>
                                    @if($isLuarDaerah)
                                    <tr><td class="p-0">Taksi</td><td class="p-0 text-right">Rp {{ number_format($est['biaya_taksi'] ?? 0, 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr><td class="p-0">Penginapan</td><td class="p-0 text-right">Rp {{ number_format($est['base_penginapan'] ?? 0, 0, ',', '.') }}</td></tr>
                                    @if($isEselon2)
                                    <tr><td class="p-0">Representasi</td><td class="p-0 text-right">Rp {{ number_format($est['base_representasi'] ?? 0, 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr><td class="p-0 pt-1">&nbsp;</td><td class="p-0 pt-1 text-right">&nbsp;</td></tr>
                                </table>
                            </td>
                            <td>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr><td class="p-0 text-center">{{ $est['days'] ?? 0 }} Hari</td></tr>
                                    <tr><td class="p-0 text-center">1 Kali</td></tr>
                                    @if($isLuarDaerah)
                                    <tr><td class="p-0 text-center">1 Kali</td></tr>
                                    @endif
                                    <tr><td class="p-0 text-center">{{ $est['nights'] ?? 0 }} Malam</td></tr>
                                    @if($isEselon2)
                                    <tr><td class="p-0 text-center">{{ $est['days'] ?? 0 }} Hari</td></tr>
                                    @endif
                                    <tr><td class="p-0 pt-1">&nbsp;</td></tr>
                                </table>
                            </td>
                            <td>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr><td class="p-0 text-right">Rp {{ number_format($est['uang_harian'], 0, ',', '.') }}</td></tr>
                                    <tr><td class="p-0 text-right">Rp {{ number_format($est['biaya_transport'], 0, ',', '.') }}</td></tr>
                                    @if($isLuarDaerah)
                                    <tr><td class="p-0 text-right">Rp {{ number_format($est['biaya_taksi'] ?? 0, 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr><td class="p-0 text-right">Rp {{ number_format($est['biaya_penginapan'], 0, ',', '.') }}</td></tr>
                                    @if($isEselon2)
                                    <tr><td class="p-0 text-right">Rp {{ number_format($est['biaya_representasi'], 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr class="border-top">
                                        @php 
                                            if ($isLuarDaerah) $perkiraan += ($est['biaya_taksi'] ?? 0); 
                                        @endphp
                                        <td class="p-0 pt-1 text-right"><strong>Rp {{ number_format($perkiraan, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                    <tr><td class="p-0 text-right text-success">Rp {{ number_format($personnel->uang_harian, 0, ',', '.') }}</td></tr>
                                    <tr><td class="p-0 text-right text-success">Rp {{ number_format($personnel->biaya_transport, 0, ',', '.') }}</td></tr>
                                    @if($isLuarDaerah)
                                    <tr><td class="p-0 text-right text-success">Rp {{ number_format($personnel->biaya_taksi ?? 0, 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr><td class="p-0 text-right text-success">Rp {{ number_format($personnel->biaya_penginapan, 0, ',', '.') }}</td></tr>
                                    @if($isEselon2)
                                    <tr><td class="p-0 text-right text-success">Rp {{ number_format($personnel->biaya_representasi, 0, ',', '.') }}</td></tr>
                                    @endif
                                    <tr class="border-top">
                                        @php
                                            if ($isLuarDaerah) $rampung += ($personnel->biaya_taksi ?? 0);
                                        @endphp
                                        <td class="p-0 pt-1 text-right text-success"><strong>Rp {{ number_format($rampung, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-sm btn-outline-primary mb-1 d-block w-100" data-toggle="modal" data-target="#editBiayaModal{{ $personnel->id }}">
                                    <i class="fas fa-edit"></i> Edit Biaya
                                </button>
                                <button onclick="printDocument('{{ route('packages.travel-orders.personnels.print-kuitansi', [$package, $travelOrder, $personnel]) }}')" class="btn btn-sm btn-outline-success d-block w-100">
                                    <i class="fas fa-print"></i> Kuitansi
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="5" class="text-right">TOTAL BIAYA RAMPUNG (DI-SPJ-KAN)</th>
                            @php 
                                if (strtolower($travelOrder->tipe_perjalanan) === 'luar_daerah' || strtolower($travelOrder->tipe_perjalanan) === 'luar daerah') {
                                    $totalRampung += $travelOrder->personnels->sum('biaya_taksi'); 
                                }
                            @endphp
                            <th class="text-success text-right" style="font-size: 1.25rem;">Rp {{ number_format($totalRampung, 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
    function printDocument(url) {
        let iframe = document.getElementById('print_iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print_iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
    }

    $(document).ready(function() {
        $('.checkbox-penginapan').on('change', function() {
            var targetId = $(this).data('target');
            var baseValue = parseFloat($(this).data('base'));
            var input = $('#' + targetId);
            
            if ($(this).is(':checked')) {
                // Set to 30%
                input.val(Math.round(baseValue * 0.3));
            } else {
                // Revert to 100%
                input.val(baseValue);
            }
        });
    });
</script>
@endpush

@foreach($travelOrder->personnels as $personnel)
<!-- Modal Edit Biaya Rampung untuk {{ $personnel->employee->nama }} -->
<div class="modal fade" id="editBiayaModal{{ $personnel->id }}" tabindex="-1" role="dialog" aria-labelledby="editBiayaModalLabel{{ $personnel->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('packages.travel-orders.personnels.update-biaya', [$package, $travelOrder, $personnel]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="editBiayaModalLabel{{ $personnel->id }}">Edit Biaya Rampung: {{ $personnel->employee->nama }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                        $isEselon2 = ($personnel->employee->kategori_biaya === 'Eselon II') || (stripos($personnel->employee->jabatan ?? '', 'kepala dinas') !== false);
                        $isLuarDaerah = (strtolower($travelOrder->tipe_perjalanan) === 'luar_daerah' || strtolower($travelOrder->tipe_perjalanan) === 'luar daerah');
                    @endphp
                    <div class="form-group">
                        <label>Uang Harian (Rp)</label>
                        <input type="number" name="uang_harian" class="form-control" value="{{ $personnel->uang_harian }}" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Biaya Transportasi (Rp)</label>
                        <input type="number" name="biaya_transport" class="form-control" value="{{ $personnel->biaya_transport }}" min="0" required>
                    </div>
                    @if($isLuarDaerah)
                    <div class="form-group">
                        <label>Biaya Taksi Bandara (Rp)</label>
                        <input type="number" name="biaya_taksi" class="form-control" value="{{ $personnel->biaya_taksi ?? 0 }}" min="0" required>
                    </div>
                    @else
                        <input type="hidden" name="biaya_taksi" value="0">
                    @endif
                    <div class="form-group">
                        <label>Biaya Penginapan (Rp)</label>
                        @php
                            $estModal = $personnel->getEstimatedCosts();
                            $stdPenginapan = $estModal['biaya_penginapan'] ?? 0;
                        @endphp
                        @if($stdPenginapan > 0)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input checkbox-penginapan" id="chkPenginapan{{ $personnel->id }}" data-target="penginapan{{ $personnel->id }}" data-base="{{ $stdPenginapan }}">
                            <label class="custom-control-label font-weight-normal text-muted" for="chkPenginapan{{ $personnel->id }}">Tidak Menginap (Dibayarkan 30% dari Standar: Rp {{ number_format($stdPenginapan * 0.3, 0, ',', '.') }})</label>
                        </div>
                        @endif
                        <input type="number" name="biaya_penginapan" id="penginapan{{ $personnel->id }}" class="form-control" value="{{ $personnel->biaya_penginapan }}" min="0" required>
                    </div>
                    @if($isEselon2)
                    <div class="form-group">
                        <label>Biaya Representasi (Rp)</label>
                        <input type="number" name="biaya_representasi" class="form-control" value="{{ $personnel->biaya_representasi }}" min="0" required>
                    </div>
                    @else
                        <input type="hidden" name="biaya_representasi" value="0">
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Biaya Rampung</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
