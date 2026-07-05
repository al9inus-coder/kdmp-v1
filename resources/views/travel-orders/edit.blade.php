@extends('adminlte::page')

@section('title', 'Edit Perjalanan Dinas')
@section('plugins.Select2', true)

@section('content_header')
    <h1>Edit Perjalanan Dinas (Swakelola)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('packages.travel-orders.update', [$package, $travelOrder]) }}" method="POST" id="travelOrderForm">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Informasi Dasar</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tipe Perjalanan <span class="text-danger">*</span></label>
                            <select name="tipe_perjalanan" id="tipe_perjalanan" class="form-control" required>
                                <option value="Dalam Daerah" {{ strtolower($travelOrder->tipe_perjalanan) == 'dalam daerah' || $travelOrder->tipe_perjalanan == 'dalam_daerah' ? 'selected' : '' }}>Dalam Daerah (Wilayah Kab. Bengkayang)</option>
                                <option value="Luar Daerah" {{ strtolower($travelOrder->tipe_perjalanan) == 'luar daerah' || $travelOrder->tipe_perjalanan == 'luar_daerah' ? 'selected' : '' }}>Luar Daerah</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="kategori_tujuan_container" style="display: none;">
                            <label>Kategori Tujuan <span class="text-danger">*</span></label>
                            <select id="kategori_tujuan" class="form-control">
                                <option value="">Pilih Kategori...</option>
                                <option value="Dalam Provinsi">Dalam Provinsi (Kalimantan Barat)</option>
                                <option value="Luar Provinsi">Luar Provinsi</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3" id="tempat_tujuan_container">
                            <label>Tempat Tujuan <span class="text-danger">*</span></label>
                            <select name="tempat_tujuan" id="tempat_tujuan" class="form-control" required data-initial="{{ old('tempat_tujuan', $travelOrder->tempat_tujuan) }}">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Berangkat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berangkat" class="form-control" value="{{ old('tanggal_berangkat', $travelOrder->tanggal_berangkat->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Kembali <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kembali" class="form-control" value="{{ old('tanggal_kembali', $travelOrder->tanggal_kembali->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Dasar Pelaksanaan (Nomor Surat / DPA)</label>
                            <textarea name="dasar_pelaksanaan" class="form-control" rows="3">{{ old('dasar_pelaksanaan', $travelOrder->dasar_pelaksanaan) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Maksud Perjalanan <span class="text-danger">*</span></label>
                            <textarea name="maksud_perjalanan" class="form-control" rows="3" required>{{ old('maksud_perjalanan', $travelOrder->maksud_perjalanan) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Peserta Perjalanan Dinas (Drag & Drop)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Daftar Pegawai (Tersedia)</h5>
                            <ul id="available-employees" class="list-group" style="min-height: 200px; border: 2px dashed #ccc; padding: 10px; background: #f9f9f9;">
                                @foreach($employees as $emp)
                                    @if(!in_array($emp->id, $selectedEmployees))
                                    <li class="list-group-item cursor-move" data-id="{{ $emp->id }}">
                                        <i class="fas fa-grip-vertical text-muted mr-2"></i>
                                        <strong>{{ $emp->nama }}</strong><br>
                                        <small class="text-muted">NIP: {{ $emp->nip ?? '-' }} | Gol: {{ $emp->golongan ?? '-' }}</small>
                                        <div class="mt-2 kendaraan-select d-none w-100">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <label><small>Transportasi Darat:</small></label>
                                                    <select class="form-control form-control-sm transport-darat-select">
                                                        <option value="mobil">Mobil</option>
                                                        <option value="motor">Motor</option>
                                                        <option value="pengikut">Pengikut / Penumpang (Rp 0)</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6 transport-udara-container" style="display: none;">
                                                    <label><small>Transportasi Udara:</small></label>
                                                    <div class="custom-control custom-switch mt-1">
                                                        <input type="checkbox" class="custom-control-input pesawat-toggle" id="pesawat_toggle_{{ $emp->id }}" value="pesawat">
                                                        <label class="custom-control-label" for="pesawat_toggle_{{ $emp->id }}"><small>Pesawat</small></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Peserta Terpilih <span class="text-danger">*</span></h5>
                            <ul id="selected-employees" class="list-group" style="min-height: 200px; border: 2px dashed #28a745; padding: 10px; background: #e9ffe9;">
                                @php
                                    $empKendaraan = [];
                                    foreach($travelOrder->personnels as $p) {
                                        $empKendaraan[$p->employee_id] = $p->jenis_kendaraan ?? 'mobil';
                                    }
                                @endphp
                                @foreach($employees as $emp)
                                    @if(in_array($emp->id, $selectedEmployees))
                                    @php $k = $empKendaraan[$emp->id] ?? 'mobil'; @endphp
                                    <li class="list-group-item cursor-move" data-id="{{ $emp->id }}">
                                        <i class="fas fa-grip-vertical text-muted mr-2"></i>
                                        <strong>{{ $emp->nama }}</strong><br>
                                        <small class="text-muted">NIP: {{ $emp->nip ?? '-' }} | Gol: {{ $emp->golongan ?? '-' }}</small>
                                        <div class="mt-2 kendaraan-select w-100">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <label><small>Transportasi Darat:</small></label>
                                                    <select class="form-control form-control-sm transport-darat-select">
                                                        <option value="mobil" {{ $k == 'mobil' || $k == 'pesawat' ? 'selected' : '' }}>Mobil</option>
                                                        <option value="motor" {{ $k == 'motor' ? 'selected' : '' }}>Motor</option>
                                                        <option value="pengikut" {{ $k == 'pengikut' ? 'selected' : '' }}>Pengikut / Penumpang (Rp 0)</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6 transport-udara-container" style="display: {{ strtolower($travelOrder->tipe_perjalanan) == 'luar daerah' || strtolower($travelOrder->tipe_perjalanan) == 'luar_daerah' ? 'block' : 'none' }};">
                                                    <label><small>Transportasi Udara:</small></label>
                                                    <div class="custom-control custom-switch mt-1">
                                                        <input type="checkbox" class="custom-control-input pesawat-toggle" id="pesawat_toggle_sel_{{ $emp->id }}" value="pesawat" {{ $k == 'pesawat' ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="pesawat_toggle_sel_{{ $emp->id }}"><small>Pesawat</small></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                            @error('employees')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div id="hidden-inputs"></div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Perubahan</button>
                    <a href="{{ route('packages.travel-orders.show', [$package, $travelOrder]) }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // Data from Controller
    const dalamDaerah = @json($dalamDaerahDestinations);
    const luarDaerahKalbar = @json($luarDaerahKalbarDestinations);
    const luarDaerahLuarProvinsi = @json($luarDaerahLuarProvinsiDestinations);

    $(document).ready(function () {
        var availableList = document.getElementById('available-employees');
        var selectedList = document.getElementById('selected-employees');
        var hiddenInputsContainer = document.getElementById('hidden-inputs');

        var tipeSelect = $('#tipe_perjalanan');
        var kategoriContainer = $('#kategori_tujuan_container');
        var kategoriSelect = $('#kategori_tujuan');
        var tempatContainer = $('#tempat_tujuan_container');
        var tempatSelect = $('#tempat_tujuan');
        var pesawatContainers = $('.transport-udara-container');
        
        var initialDest = tempatSelect.data('initial');
        var isFirstLoad = true;

        function updateDropdowns() {
            var tipe = tipeSelect.val();
            var kategori = kategoriSelect.val();
            
            tempatSelect.empty();
            var options = [];

            if (tipe === 'Dalam Daerah') {
                kategoriContainer.hide();
                kategoriSelect.removeAttr('required');
                tempatContainer.removeClass('col-md-8').addClass('col-md-8');
                options = dalamDaerah;
                
                // Hide pesawat toggle
                pesawatContainers.hide();
                $('.pesawat-toggle').prop('checked', false);
            } else {
                kategoriContainer.show();
                kategoriSelect.attr('required', 'required');
                tempatContainer.removeClass('col-md-8').addClass('col-md-4');
                
                if (isFirstLoad && initialDest) {
                    if (luarDaerahKalbar.includes(initialDest)) {
                        kategoriSelect.val('Dalam Provinsi');
                        kategori = 'Dalam Provinsi';
                    } else if (luarDaerahLuarProvinsi.includes(initialDest)) {
                        kategoriSelect.val('Luar Provinsi');
                        kategori = 'Luar Provinsi';
                    }
                }
                
                if (kategori === 'Dalam Provinsi') {
                    options = luarDaerahKalbar;
                } else if (kategori === 'Luar Provinsi') {
                    options = luarDaerahLuarProvinsi;
                }
                
                // Show pesawat toggle
                pesawatContainers.show();
            }

            if (options.length === 0 && tipe === 'Luar Daerah') {
                var newOption = new Option('Pilih Kategori Tujuan dulu...', '', false, false);
                $(newOption).prop('disabled', true);
                tempatSelect.append(newOption);
            } else {
                options.forEach(function(item) {
                    var newOption = new Option(item, item, false, false);
                    tempatSelect.append(newOption);
                });
            }
            
            if (isFirstLoad && initialDest && options.includes(initialDest)) {
                tempatSelect.val(initialDest);
            }
            
            tempatSelect.trigger('change');
            updateHiddenInputs();
        }

        tipeSelect.on('change', function() {
            if (!isFirstLoad) {
                kategoriSelect.val('').trigger('change');
            }
            updateDropdowns();
        });
        
        kategoriSelect.on('change', function() {
            if (!isFirstLoad) {
                updateDropdowns();
            }
        });

        // Initialize on load
        updateDropdowns();
        isFirstLoad = false;

        function updateHiddenInputs() {
            hiddenInputsContainer.innerHTML = '';
            var items = selectedList.querySelectorAll('li');
            items.forEach(function(item) {
                var empId = item.getAttribute('data-id');
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'employees[]';
                input.value = empId;
                hiddenInputsContainer.appendChild(input);

                var kendaraanInput = document.createElement('input');
                kendaraanInput.type = 'hidden';
                kendaraanInput.name = 'kendaraan[' + empId + ']';
                
                var daratSelect = item.querySelector('.transport-darat-select');
                var pesawatToggle = item.querySelector('.pesawat-toggle');
                
                if (pesawatToggle && pesawatToggle.checked) {
                    kendaraanInput.value = 'pesawat';
                } else if (daratSelect) {
                    kendaraanInput.value = daratSelect.value;
                } else {
                    kendaraanInput.value = 'mobil';
                }
                
                hiddenInputsContainer.appendChild(kendaraanInput);
            });
        }

        selectedList.addEventListener('change', function(e) {
            if (e.target.tagName.toLowerCase() === 'select' || e.target.type === 'checkbox') {
                updateHiddenInputs();
            }
        });

        // Sortable JS logic
        new Sortable(availableList, {
            group: 'shared',
            animation: 150,
            onAdd: function (evt) {
                var selDiv = evt.item.querySelector('.kendaraan-select');
                if (selDiv) selDiv.classList.add('d-none');
                updateHiddenInputs();
            }
        });

        new Sortable(selectedList, {
            group: 'shared',
            animation: 150,
            onAdd: function (evt) {
                var selDiv = evt.item.querySelector('.kendaraan-select');
                if (selDiv) selDiv.classList.remove('d-none');
                updateHiddenInputs();
            },
            onRemove: function (evt) {
                updateHiddenInputs();
            },
            onUpdate: function (evt) {
                updateHiddenInputs();
            }
        });

        // Run once on load
        updateHiddenInputs();

        // Validate form
        document.getElementById('travelOrderForm').addEventListener('submit', function(e) {
            if (hiddenInputsContainer.children.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal 1 peserta dengan drag & drop ke kotak Peserta Terpilih.');
            }
        });
    });
</script>
<style>
    .cursor-move { cursor: grab; }
    .cursor-move:active { cursor: grabbing; }
</style>
@stop
