@extends('adminlte::page')

@section('title', 'Pelaksanaan Kontrak')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="font-weight-bold text-dark">
            <i class="fas fa-play-circle text-primary mr-2"></i> Pelaksanaan Kontrak
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('procurement-packages.index') }}">Daftar Paket</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procurement-packages.show', $procurementPackage->package) }}">{{ $procurementPackage->package->id_rup }}</a></li>
            <li class="breadcrumb-item active">Pelaksanaan Kontrak</li>
        </ol>
    </div>
@stop

@section('content')

    {{-- Progress Workflow --}}
    @include('components.workflow-progress', ['procurementPackage' => $procurementPackage])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Detail Kontrak -->
        <div class="col-md-5">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature text-primary mr-2"></i> Informasi Detail Kontrak</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%">Program</th>
                                <td>{{ $procurementPackage->package->program->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kegiatan</th>
                                <td>{{ $procurementPackage->package->activity->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Sub Kegiatan</th>
                                <td>{{ $procurementPackage->package->subActivity->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Paket</th>
                                <td>{{ $procurementPackage->package->nama_paket ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>ID RUP</th>
                                <td><span class="badge badge-info">{{ $procurementPackage->package->id_rup ?? '-' }}</span></td>
                            </tr>
                            <tr>
                            <tr>
                                <th>Nama PPK</th>
                                <td>{{ $procurementPackage->nama_ppk ?? '-' }}</td>
                            </tr>  
                            <tr>
                                <th>Nama Penyedia</th>
                                <td>{{ $process->nama_penyedia ?? '-' }}</td>
                            </tr>                                                          
                                <th>Jenis Kontrak</th>
                                <td>{{ $procurementPackage->jenis_kontrak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nomor Surat Pesanan</th>
                                <td>{{ $process->nomor_surat_pesanan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai Kontrak</th>
                                <td>{{ optional($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Berakhir Kontrak</th>
                                <td>{{ optional($process->tanggal_barang_diterima)->translatedFormat('d F Y') }}</td>
                            </tr>
                                <th>Nilai Kontrak</th>
                                <td class="font-weight-bold text-success">Rp {{ number_format($process->nilai_kontrak, 0, ',', '.') }}</td>
                            </tr>                            
                            <tr>
                                <th>Lama Kontrak</th>
                                @php
                                    $durasiHari = 0;
                                    if ($process->tanggal_surat_pesanan && $process->tanggal_barang_diterima) {
                                        $durasiHari = $process->tanggal_surat_pesanan->diffInDays($process->tanggal_barang_diterima);
                                    }
                                @endphp
                                <td><span class="badge badge-warning">{{ $durasiHari }} Hari Kalender</span></td>
                            </tr>
                            <tr>
                                <th>Lokasi Pekerjaan</th>
                                <td>Kecamatan Bengkayang, Kabupaten Bengkayang</td>
                            </tr>                            
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ route('procurement-packages.procurement-process.show', $procurementPackage->package) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Surat Pesanan
                    </a>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Timeline Progress -->
        <div class="col-md-7">
            <div class="card card-outline card-info shadow-sm h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-tasks text-info mr-2"></i> Progress Pelaksanaan Kontrak</h3>
                </div>
                <div class="card-body">
                    @php
                        $start = clone optional($process->tanggal_surat_pesanan)->startOfDay() ?? now()->startOfDay();
                        $end = clone optional($process->tanggal_barang_diterima)->startOfDay() ?? now()->startOfDay();
                        $today = now()->startOfDay();
                        
                        $durasiHari = $start->diffInDays($end);
                        if ($durasiHari <= 0) $durasiHari = 1; // Prevent division by zero
                        
                        if ($today->lt($start)) {
                            $passedDays = 0;
                            $progress = 0;
                            $statusText = "Belum Dimulai";
                            $statusColor = "text-secondary";
                        } elseif ($today->gt($end)) {
                            $passedDays = $durasiHari;
                            $progress = 100;
                            $statusText = "Selesai / Melewati Batas Waktu";
                            $statusColor = "text-danger";
                        } else {
                            $passedDays = $start->diffInDays($today);
                            $progress = round(($passedDays / $durasiHari) * 100);
                            $statusText = "Sedang Berjalan (Hari ke-" . $passedDays . " dari " . $durasiHari . " Hari)";
                            $statusColor = "text-success";
                        }
                    @endphp

                    <div class="text-center mt-3 mb-4">
                        <h1 class="display-4 font-weight-bold {{ $statusColor }}">{{ $progress }}%</h1>
                        <span class="text-muted font-weight-bold" style="font-size: 1.1rem;">{{ $statusText }}</span>
                    </div>

                    <div class="progress mb-3 shadow-sm" style="height: 25px; border-radius: 12px; background-color: #e9ecef;">
                        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated font-weight-bold" 
                             role="progressbar" 
                             aria-valuenow="{{ $progress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100" 
                             style="width: {{ $progress }}%; font-size: 0.9rem;">
                            {{ $progress > 5 ? $progress . '%' : '' }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between text-muted mb-5 px-1">
                        <div class="text-left">
                            <small class="d-block text-uppercase font-weight-bold">Mulai Kontrak</small>
                            <span class="text-dark"><i class="far fa-calendar-alt mr-1"></i> {{ $start->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <small class="d-block text-uppercase font-weight-bold">Akhir Kontrak</small>
                            <span class="text-dark"><i class="far fa-calendar-check mr-1"></i> {{ $end->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    <hr class="mt-5">
                    <div class="callout callout-info mt-4 bg-light">
                        <h5><i class="fas fa-info-circle text-info mr-2"></i> Informasi Pelaksanaan</h5>
                        <p class="mb-0 text-muted">Pastikan progres fisik pekerjaan di lapangan selalu dimonitor dengan saksama agar dapat diselesaikan tepat waktu sebelum batas akhir kontrak pada <strong class="text-dark">{{ $end->translatedFormat('d F Y') }}</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kalender Row -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-2"></i> Kalender Pelaksanaan</h3>
                    <div class="card-tools">
                        <span class="badge badge-info"><i class="fas fa-info-circle mr-1"></i> Klik 2 kali pada tanggal untuk aksi tambahan</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilihan Aksi -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold" id="actionModalLabel">Pilih Tindakan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-4">Anda mengklik tanggal: <strong id="selectedDateDisplay"></strong></p>
                    <div class="d-flex justify-content-center" style="gap: 15px;">
                        <button type="button" class="btn btn-outline-warning btn-lg px-4" onclick="openAdendumModal()">
                            <i class="fas fa-edit mb-2 d-block fa-2x"></i>
                            Adendum<br>Kontrak
                        </button>
                        <button type="button" class="btn btn-outline-success btn-lg px-4" onclick="openPaymentModal()">
                            <i class="fas fa-check-circle mb-2 d-block fa-2x"></i>
                            Pekerjaan<br>Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adendum -->
    <div class="modal fade" id="adendumModal" tabindex="-1" role="dialog" aria-labelledby="adendumModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('procurement-payments.adendum.store', $procurementPackage->package) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title font-weight-bold" id="adendumModalLabel"><i class="fas fa-edit mr-2"></i> Adendum Kontrak</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nomor Adendum <span class="text-danger">*</span></label>
                            <input type="text" name="nomor" class="form-control" required placeholder="Contoh: ADD-01/SP/2026">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir Kontrak (Baru) <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_akhir_baru" id="adendumDateInput" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Alasan Adendum <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Masukkan alasan adendum..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Simpan Adendum</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pekerjaan Selesai (Pembayaran) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('procurement-payments.store', $procurementPackage->package) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success">
                        <h5 class="modal-title font-weight-bold text-white" id="paymentModalLabel"><i class="fas fa-check-circle mr-2"></i> Form Penyelesaian Pekerjaan & Tagihan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> Mengisi form ini akan mengubah status paket ke tahap <strong>Pembayaran</strong>.
                        </div>
                        
                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4 text-primary">Data BAST & Invoice</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nomor BAST <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_bast" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal BAST <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_bast" id="paymentBastDate" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Nomor Invoice <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_invoice" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal Invoice <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_invoice" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4 text-primary">Data Berita Acara Pembayaran (BAP)</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nomor BAP (Angka Saja) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">BAP-</span>
                                    </div>
                                    <input type="number" name="nomor_bap" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal BAP <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_bap" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4 text-primary">Data Kwitansi</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nomor Kwitansi (Angka Saja) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">KWT-</span>
                                    </div>
                                    <input type="number" name="nomor_kwitansi" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal Kwitansi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kwitansi" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4 text-primary">Data PPTK (Untuk BAP)</h6>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Nama PPTK <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pptk" class="form-control" placeholder="Contoh: ALGINUS, S.Si" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>NIP PPTK <span class="text-danger">*</span></label>
                                <input type="text" name="nip_pptk" class="form-control" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Pangkat / Golongan <span class="text-danger">*</span></label>
                                <input type="text" name="pangkat_golongan_pptk" class="form-control" placeholder="Contoh: Penata Tingkat I / III/d" required>
                            </div>
                        </div>

                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-4 text-primary">Dokumen Tambahan</h6>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Tanggal Ringkasan Kontrak <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_ringkasan_kontrak" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Surat Pernyataan Non-PKP</label>
                                <div class="custom-control custom-switch mt-1">
                                    <input type="checkbox" class="custom-control-input" id="is_non_pkp" name="is_non_pkp" value="1" onchange="toggleNonPkpDate()">
                                    <label class="custom-control-label" for="is_non_pkp">Lampirkan Surat Non-PKP</label>
                                </div>
                            </div>
                            <div class="col-md-6 form-group" id="nonPkpDateGroup" style="display: none;">
                                <label>Tanggal Surat Non-PKP <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_non_pkp" id="tanggal_non_pkp" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan & Lanjut Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('css')
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <style>
        .fc-toolbar-title { font-size: 1.2rem !important; font-weight: bold; }
        .fc-event { cursor: pointer; padding: 3px; font-weight: bold; border:none; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .fc-event-title { white-space: normal; }
        .fc-daygrid-day { cursor: pointer; transition: background-color 0.2s; }
        .fc-daygrid-day:hover { background-color: #f8f9fa; }
    </style>
@endpush

@push('js')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
    
    <script>
        let selectedDateStr = '';
        let clickTimer = null;

        function toggleNonPkpDate() {
            const isChecked = document.getElementById('is_non_pkp').checked;
            const dateGroup = document.getElementById('nonPkpDateGroup');
            const dateInput = document.getElementById('tanggal_non_pkp');
            
            if(isChecked) {
                dateGroup.style.display = 'block';
                dateInput.setAttribute('required', 'required');
            } else {
                dateGroup.style.display = 'none';
                dateInput.removeAttribute('required');
                dateInput.value = '';
            }
        }

        function openAdendumModal() {
            $('#actionModal').modal('hide');
            $('#adendumDateInput').val(selectedDateStr);
            setTimeout(() => { $('#adendumModal').modal('show'); }, 400);
        }

        function openPaymentModal() {
            $('#actionModal').modal('hide');
            $('#paymentBastDate').val(selectedDateStr);
            setTimeout(() => { $('#paymentModal').modal('show'); }, 400);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            var startDate = '{{ optional($process->tanggal_surat_pesanan)->format('Y-m-d') }}';
            var endDateStr = '{{ optional($process->tanggal_barang_diterima)->format('Y-m-d') }}';
            
            var endDate = new Date(endDateStr);
            endDate.setDate(endDate.getDate() + 1);
            var endFormat = endDate.toISOString().split('T')[0];

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'id',
                initialView: 'dayGridMonth',
                initialDate: startDate || new Date(),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: [
                    {
                        title: 'Pelaksanaan Kontrak',
                        start: startDate,
                        end: endFormat,
                        backgroundColor: '#17a2b8',
                        borderColor: '#117a8b'
                    }
                ],
                height: 'auto',
                dateClick: function(info) {
                    // Implement double click logic
                    if (clickTimer == null) {
                        clickTimer = setTimeout(function() {
                            clickTimer = null;
                            // Single click logic (optional, do nothing or show tooltip)
                        }, 250);
                    } else {
                        clearTimeout(clickTimer);
                        clickTimer = null;
                        
                        // Double click logic
                        selectedDateStr = info.dateStr;
                        
                        // Format display date
                        const d = new Date(selectedDateStr);
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        document.getElementById('selectedDateDisplay').textContent = d.toLocaleDateString('id-ID', options);
                        
                        $('#actionModal').modal('show');
                    }
                }
            });

            calendar.render();
        });
    </script>
@endpush
