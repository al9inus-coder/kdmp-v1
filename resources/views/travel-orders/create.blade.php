@component('layouts.kdmp')

@section('title', 'Buat Perjalanan Dinas')
@section('plugins.Select2', true)

@slot('header')
    <h1>Buat Perjalanan Dinas (Swakelola)</h1>
@endslot


<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <div class="md:col-span-12">
        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'packages.travel-orders.store', $package) }}" method="POST" id="travelOrderForm">
            @csrf
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 bg-primary text-white">
                    <h3 class="text-lg font-semibold text-slate-800 flex items-center">Informasi Dasar</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-4 mb-6">
                            <label>Tipe Perjalanan <span class="text-red-600">*</span></label>
                            <select name="tipe_perjalanan" id="tipe_perjalanan" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                                <option value="Dalam Daerah">Dalam Daerah (Wilayah Kab. Bengkayang)</option>
                                <option value="Luar Daerah">Luar Daerah</option>
                            </select>
                        </div>
                        <div class="md:col-span-4 mb-6" id="kategori_tujuan_container" style="display: none;">
                            <label>Kategori Tujuan <span class="text-red-600">*</span></label>
                            <select id="kategori_tujuan" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                <option value="">Pilih Kategori...</option>
                                <option value="Dalam Provinsi">Dalam Provinsi (Kalimantan Barat)</option>
                                <option value="Luar Provinsi">Luar Provinsi</option>
                            </select>
                        </div>
                        <div class="md:col-span-8 mb-6" id="tempat_tujuan_container">
                            <label>Tempat Tujuan <span class="text-red-600">*</span></label>
                            <select name="tempat_tujuan" id="tempat_tujuan" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                                <!-- Populated by JS -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-4 mb-6">
                            <label>Tanggal Berangkat <span class="text-red-600">*</span></label>
                            <input type="date" name="tanggal_berangkat" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" value="{{ old('tanggal_berangkat') }}" required>
                        </div>
                        <div class="md:col-span-4 mb-6">
                            <label>Tanggal Kembali <span class="text-red-600">*</span></label>
                            <input type="date" name="tanggal_kembali" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" value="{{ old('tanggal_kembali') }}" required>
                        </div>
                        <div class="md:col-span-4 mb-6">
                            <label>Tanggal Surat <span class="text-red-600">*</span></label>
                            <input type="date" name="tanggal_surat" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-6 mb-6">
                            <label>Dasar Pelaksanaan (Nomor Surat / DPA)</label>
                            <textarea name="dasar_pelaksanaan" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" rows="3">{{ old('dasar_pelaksanaan') }}</textarea>
                        </div>
                        <div class="md:col-span-6 mb-6">
                            <label>Maksud Perjalanan <span class="text-red-600">*</span></label>
                            <textarea name="maksud_perjalanan" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" rows="3" required>{{ old('maksud_perjalanan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 bg-info text-white">
                    <h3 class="text-lg font-semibold text-slate-800 flex items-center">Peserta Perjalanan Dinas (Drag & Drop)</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-6">
                            <h5>Daftar Pegawai (Tersedia)</h5>
                            <ul id="available-employees" class="list-group" style="min-height: 200px; border: 2px dashed #ccc; padding: 10px; background: #f9f9f9;">
                                @foreach($employees as $emp)
                                    <li class="list-group-item cursor-move" data-id="{{ $emp->id }}">
                                        <i class="fas fa-grip-vertical text-slate-500 mr-2"></i>
                                        <strong>{{ $emp->nama }}</strong><br>
                                        <small class="text-slate-500">NIP: {{ $emp->nip ?? '-' }} | Gol: {{ $emp->golongan ?? '-' }}</small>
                                        <div class="mt-2 kendaraan-select d-none w-full">
                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                                <div class="col-sm-6">
                                                    <label><small>Transportasi Darat:</small></label>
                                                    <select class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm-sm transport-darat-select">
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
                                @endforeach
                            </ul>
                        </div>
                        <div class="md:col-span-6">
                            <h5>Peserta Terpilih <span class="text-red-600">*</span></h5>
                            <ul id="selected-employees" class="list-group" style="min-height: 200px; border: 2px dashed #28a745; padding: 10px; background: #e9ffe9;">
                                <!-- Dragged items go here -->
                            </ul>
                            @error('employees')
                                <div class="text-red-600 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div id="hidden-inputs"></div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" id="btnSubmit">Simpan & Buat Surat</button>
                    <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.show', $package) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">Batal</a>
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

            tempatSelect.trigger('change');
            
            updateHiddenInputs();
        }

        tipeSelect.on('change', function() {
            kategoriSelect.val('').trigger('change');
            updateDropdowns();
        });
        kategoriSelect.on('change', updateDropdowns);

        // Initialize on load
        updateDropdowns();

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
            onUpdate: function() {
                updateHiddenInputs();
            },
            onRemove: function() {
                updateHiddenInputs();
            }
        });

        // Run once on load just in case
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

@endcomponent
