@component('layouts.kdmp')
@section('title', isset($travelOrder) ? 'Ubah Draf SPPD' : 'Ajukan SPPD')

@php
    $isEdit = isset($travelOrder);
    $pageTitle = $isEdit ? 'Ubah Draf SPPD' : 'Ajukan SPPD Baru';
    $selectedEmployeeIds = $isEdit
        ? $travelOrder->personnels->pluck('employee_id')->map(fn ($id) => (string) $id)->values()
        : collect([]);
    $selectedVehicles = $isEdit
        ? $travelOrder->personnels->mapWithKeys(fn ($personnel) => [(string) $personnel->employee_id => $personnel->jenis_kendaraan ?? 'mobil'])->all()
        : [];
    $initialTipe = $isEdit ? $travelOrder->tipe_perjalanan : 'Dalam Daerah';
    $initialKategori = '';
    if ($isEdit && $initialTipe === 'Luar Daerah') {
        $initialKategori = $luarDaerahKalbarDestinations->contains($travelOrder->tempat_tujuan)
            ? 'Dalam Provinsi'
            : 'Luar Provinsi';
    }

    $employeeOptions = $employees->map(fn ($employee) => [
        'id' => (string) $employee->id,
        'nama' => $employee->nama,
        'nip' => $employee->nip,
        'jabatan' => $employee->jabatan,
        'golongan' => $employee->golongan,
    ])->values();
@endphp

<div class="space-y-6" x-data="stafTravelOrderCreate({
    employees: @js($employeeOptions),
    dalamDaerah: @js($dalamDaerahDestinations),
    luarDaerahKalbar: @js($luarDaerahKalbarDestinations),
    luarDaerahLuarProvinsi: @js($luarDaerahLuarProvinsiDestinations),
    initialTipe: @js(old('tipe_perjalanan', $initialTipe)),
    initialKategori: @js(old('kategori_tujuan', $initialKategori)),
    initialTujuan: @js(old('tempat_tujuan', $isEdit ? $travelOrder->tempat_tujuan : '')),
    initialStart: @js(old('tanggal_berangkat', $isEdit ? $travelOrder->tanggal_berangkat?->format('Y-m-d') : null)),
    initialEnd: @js(old('tanggal_kembali', $isEdit ? $travelOrder->tanggal_kembali?->format('Y-m-d') : null)),
    initialSurat: @js(old('tanggal_surat', $isEdit ? $travelOrder->tanggal_surat?->format('Y-m-d') : now()->toDateString())),
    initialSelected: @js(collect(old('employees', $selectedEmployeeIds))->map(fn ($id) => (string) $id)->values()),
    initialVehicles: @js(old('kendaraan', $selectedVehicles))
})">
    <x-ui.toast />

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>
                {{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg">
                <i data-lucide="plane" class="w-3.5 h-3.5"></i>
                Perjalanan Dinas
            </span>
        </div>
        <a href="{{ $isEdit ? route('staf.packages.travel-orders.show', [$package, $travelOrder]) : route('staf.sppd.index') }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $isEdit ? route('staf.packages.travel-orders.update', [$package, $travelOrder]) : route('staf.packages.travel-orders.store', $package) }}"
        class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-6 items-start"
        @submit="ensurePersonnel($event)">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="tanggal_berangkat" :value="start || ''">
        <input type="hidden" name="tanggal_kembali" :value="end || ''">

        <div class="hidden">
            <template x-for="employee in selectedEmployees" :key="'selected-input-' + employee.id">
                <span>
                    <input type="hidden" name="employees[]" :value="employee.id">
                    <input type="hidden" :name="`kendaraan[${employee.id}]`" :value="vehicles[employee.id] || 'mobil'">
                </span>
            </template>
        </div>

        <div class="space-y-6 min-w-0">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $pageTitle }}</p>
                        @if(isset($eligiblePackages) && $eligiblePackages->isNotEmpty())
                            <div class="mt-3">
                                <label for="package_id" class="block text-sm font-bold text-slate-700 mb-1.5">
                                    Sub Kegiatan & Paket Perjalanan Dinas <span class="text-rose-500">*</span>
                                </label>
                                <select id="package_id" name="package_id" class="w-full max-w-2xl rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($eligiblePackages as $pkg)
                                        <option value="{{ $pkg->id }}" {{ $pkg->id == $package->id ? 'selected' : '' }}>
                                            [{{ $pkg->subActivity?->kode }}] {{ Str::limit($pkg->subActivity?->nama, 60) }} — Paket: {{ $pkg->nama_paket }} ({{ $pkg->id_rup ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500">Pilih sub kegiatan dan paket belanja yang akan digunakan.</p>
                            </div>
                        @else
                            <h1 class="mt-2 text-2xl font-bold text-slate-900 leading-tight">{{ $package->nama_paket }}</h1>
                            <p class="mt-2 text-sm text-slate-500">{{ $package->account?->kode ?? '-' }} {{ $package->account?->nama ?? '' }}</p>
                        @endif
                    </div>
                </div>

                <div class="p-5 lg:p-6 grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-5 items-start">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 min-w-0">
                        <div>
                            <label for="tipe_perjalanan" class="block text-sm font-bold text-slate-700 mb-1.5">Tipe Perjalanan <span class="text-rose-500">*</span></label>
                            <select id="tipe_perjalanan" name="tipe_perjalanan" x-model="tipe" @change="kategori = ''; syncTujuan(true)"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Dalam Daerah">Dalam Daerah</option>
                                <option value="Luar Daerah">Luar Daerah</option>
                            </select>
                            @error('tipe_perjalanan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="tipe === 'Luar Daerah'" style="display: none;">
                            <label for="kategori_tujuan" class="block text-sm font-bold text-slate-700 mb-1.5">Kategori Tujuan</label>
                            <select id="kategori_tujuan" name="kategori_tujuan" x-model="kategori" @change="syncTujuan(true)"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Pilih kategori</option>
                                <option value="Dalam Provinsi">Dalam Provinsi Kalbar</option>
                                <option value="Luar Provinsi">Luar Provinsi</option>
                            </select>
                        </div>

                        <div :class="tipe === 'Luar Daerah' ? 'md:col-span-2' : 'md:col-span-1'">
                            <label for="tempat_tujuan" class="block text-sm font-bold text-slate-700 mb-1.5">Tempat Tujuan <span class="text-rose-500">*</span></label>
                            {{-- x-effect: terapkan ulang nilai tersimpan setelah x-for selesai merender <option>,
                                 karena x-model gagal memilih opsi yang belum ada di DOM saat inisialisasi --}}
                            <select id="tempat_tujuan" name="tempat_tujuan" x-model="tujuan"
                                x-effect="tujuanOptions; $nextTick(() => { if (tujuan) $el.value = tujuan })"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-for="option in tujuanOptions" :key="option">
                                    <option :value="option" x-text="option"></option>
                                </template>
                            </select>
                            @error('tempat_tujuan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="dasar_pelaksanaan" class="block text-sm font-bold text-slate-700 mb-1.5">Dasar Pelaksanaan</label>
                            <textarea id="dasar_pelaksanaan" name="dasar_pelaksanaan" rows="4"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Nomor surat, DPA, atau dasar penugasan">{{ old('dasar_pelaksanaan', $isEdit ? $travelOrder->dasar_pelaksanaan : '') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="maksud_perjalanan" class="block text-sm font-bold text-slate-700 mb-1.5">Maksud Perjalanan <span class="text-rose-500">*</span></label>
                            <textarea id="maksud_perjalanan" name="maksud_perjalanan" rows="4"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                placeholder="Tuliskan maksud/agenda perjalanan">{{ old('maksud_perjalanan', $isEdit ? $travelOrder->maksud_perjalanan : '') }}</textarea>
                            @error('maksud_perjalanan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-4 min-w-0">
                        <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                            <div class="px-3 py-2.5 border-b border-slate-100 flex items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="calendar-days" class="w-4 h-4 text-blue-500"></i>
                                    Kalender
                                </h2>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="prevMonth()" class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                                        <i data-lucide="chevron-left" class="w-3.5 h-3.5 mx-auto"></i>
                                    </button>
                                    <div class="w-24 text-center text-xs font-bold text-slate-800" x-text="monthLabel"></div>
                                    <button type="button" @click="nextMonth()" class="w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 mx-auto"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-3">
                                <div class="grid grid-cols-7 gap-1 mb-1.5">
                                    <template x-for="day in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']" :key="day">
                                        <div class="h-5 flex items-center justify-center text-[10px] font-bold uppercase tracking-wide text-slate-400" x-text="day"></div>
                                    </template>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <template x-for="(cell, index) in cells" :key="cell.iso || `empty-${index}`">
                                        <div>
                                            <button x-show="cell.iso" type="button" @click="pickDate(cell.iso)"
                                                class="w-full h-9 rounded-lg text-sm font-bold transition-colors"
                                                :class="cellClass(cell.iso)"
                                                x-text="cell.d"></button>
                                            <div x-show="!cell.iso" class="w-full h-9"></div>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Berangkat</p>
                                        <p class="mt-1 text-xs font-bold text-slate-800" x-text="displayDate(start)"></p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Kembali</p>
                                        <p class="mt-1 text-xs font-bold text-slate-800" x-text="displayDate(end)"></p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Durasi</p>
                                        <p class="mt-1 text-xs font-bold text-slate-800"><span x-text="duration"></span> hari</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('tanggal_berangkat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        @error('tanggal_kembali') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                        <div>
                            <label for="tanggal_surat" class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal Surat</label>
                            <input type="date" id="tanggal_surat" name="tanggal_surat" x-model="tanggalSurat"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <p class="mt-2 text-xs text-slate-500">Default hari ini, bisa disesuaikan.</p>
                            @error('tanggal_surat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-emerald-500"></i>
                            Peserta Perjalanan Dinas
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Seret pegawai dari daftar tersedia ke peserta terpilih.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg bg-slate-100 text-slate-600">
                        <span x-text="selectedEmployees.length"></span> dipilih
                    </span>
                </div>

                @error('employees') <div class="mx-6 mt-4 rounded-lg border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div> @enderror

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 p-4 sm:p-6">
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 overflow-hidden transition-colors"
                        :class="draggedId && !selectedIds.includes(draggedId) ? 'border-slate-300' : (draggedId ? 'border-blue-300 bg-blue-50/40' : '')"
                        @dragover.prevent
                        @drop.prevent="dropToAvailable()">
                        <div class="px-4 py-3 border-b border-slate-200 bg-white flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800">Daftar Pegawai</h3>
                            <span class="text-xs font-bold text-slate-400" x-text="availableEmployees.length + ' tersedia'"></span>
                        </div>
                        <div class="p-3 space-y-2 h-[360px] overflow-y-auto">
                            <template x-for="employee in availableEmployees" :key="employee.id">
                                <div draggable="true" @dragstart="dragEmployee(employee.id, $event)" @dragend="draggedId = null"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm cursor-grab active:cursor-grabbing hover:border-emerald-200 hover:bg-emerald-50/40 select-none"
                                    :class="draggedId === employee.id ? 'opacity-50 ring-2 ring-emerald-200' : ''">
                                    <div class="flex items-center gap-3 min-h-[52px]">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                            <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-sm text-slate-900 leading-snug truncate" x-text="employee.nama"></p>
                                            <p class="mt-1 text-xs text-slate-500 leading-relaxed truncate">
                                                <span x-text="'NIP ' + (employee.nip || '-')"></span>
                                                <span> &bull; </span>
                                                <span x-text="employee.jabatan || 'Jabatan belum tersedia'"></span>
                                                <span> &bull; Gol. </span>
                                                <span x-text="employee.golongan || '-'"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="availableEmployees.length === 0" class="py-10 text-center text-sm font-semibold text-slate-400">
                                Semua pegawai sudah dipilih.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-emerald-300 bg-emerald-50/50 overflow-hidden transition-colors"
                        :class="draggedId && !selectedIds.includes(draggedId) ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-100' : ''"
                        @dragover.prevent
                        @drop.prevent="dropToSelected()">
                        <div class="px-4 py-3 border-b border-emerald-100 bg-white flex items-center justify-between">
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-800">Peserta Terpilih</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Seret untuk mengubah urutan peserta.</p>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 shrink-0" x-text="selectedEmployees.length + ' peserta'"></span>
                        </div>
                        <div class="p-3 space-y-2 h-[360px] overflow-y-auto">
                            <template x-for="(employee, idx) in selectedEmployees" :key="employee.id">
                                <div draggable="true"
                                    @dragstart="dragEmployee(employee.id, $event)"
                                    @dragend="draggedId = null; dragOverId = null"
                                    @dragover.prevent="dragOverId = employee.id"
                                    @dragleave="if (dragOverId === employee.id) dragOverId = null"
                                    @drop.prevent.stop="dropBeforeSelected(employee.id)"
                                    class="rounded-lg border bg-white px-3 py-2 shadow-sm cursor-grab active:cursor-grabbing select-none transition-all"
                                    :class="(draggedId === employee.id ? 'opacity-50 ring-2 ring-emerald-200 ' : '')
                                        + (dragOverId === employee.id && draggedId !== employee.id ? 'border-t-2 border-t-emerald-500 ' : '')
                                        + (idx === 0 ? 'border-amber-200 bg-amber-50/40' : 'border-emerald-100')">
                                    <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_140px] gap-2 items-center min-h-[58px]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex flex-col items-center gap-0.5 shrink-0">
                                                <i data-lucide="grip-vertical" class="w-4 h-4 text-slate-300"></i>
                                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black"
                                                    :class="idx === 0 ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500'"
                                                    x-text="idx + 1"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-sm leading-snug truncate"
                                                    :class="idx === 0 ? 'text-amber-700' : 'text-slate-900'"
                                                    x-text="employee.nama"></p>
                                                <p class="mt-1 text-xs text-slate-500 leading-relaxed truncate">
                                                    <span x-text="'NIP ' + (employee.nip || '-')"></span>
                                                    <span> &bull; Gol. </span>
                                                    <span x-text="employee.golongan || '-'"></span>
                                                </p>
                                            </div>
                                        </div>
                                        <select x-model="vehicles[employee.id]" @dragstart.stop
                                            class="h-9 rounded-lg border-slate-300 text-xs font-bold focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="mobil">Mobil</option>
                                            <option value="motor">Motor</option>
                                            <option value="pesawat">Pesawat</option>
                                            <option value="pengikut">Pengikut</option>
                                        </select>
                                    </div>
                                </div>
                            </template>
                            <div x-show="selectedEmployees.length === 0" class="py-10 text-center">
                                <div class="mx-auto w-12 h-12 rounded-2xl border border-dashed border-emerald-300 flex items-center justify-center text-emerald-300">
                                    <i data-lucide="user-plus" class="w-6 h-6"></i>
                                </div>
                                <p class="mt-3 text-sm font-bold text-slate-500">Tarik pegawai ke area ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-20">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-500"></i>
                        Ringkasan Input
                    </h2>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tipe</p>
                        <p class="mt-1 font-bold text-slate-900" x-text="tipe"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tujuan</p>
                        <p class="mt-1 font-bold text-slate-900" x-text="tujuan || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Jadwal</p>
                        <p class="mt-1 font-bold text-slate-900">
                            <span x-text="displayDate(start)"></span>
                            <span class="text-slate-400">s.d.</span>
                            <span x-text="displayDate(end)"></span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Lama Perjalanan</p>
                        <p class="mt-1 font-bold" :class="duration > 0 ? 'text-amber-700' : 'text-slate-400'">
                            <span x-text="duration > 0 ? duration + ' hari' : '-'"></span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tanggal Surat</p>
                        <p class="mt-1 font-bold text-slate-900" x-text="displayDate(tanggalSurat)"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Peserta</p>
                        <p class="mt-1 font-bold text-slate-900"><span x-text="selectedEmployees.length"></span> pegawai</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-black shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Buat Surat' }}
                </button>
                <a href="{{ $isEdit ? route('staf.packages.travel-orders.show', [$package, $travelOrder]) : route('staf.sppd.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm">
                    Batal
                </a>
            </div>
        </aside>
    </form>
</div>

<script>
    function stafTravelOrderCreate(config) {
        const pad = (value) => String(value).padStart(2, '0');
        const toIso = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        const fromIso = (value) => value ? new Date(value + 'T00:00:00') : null;
        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const anchor = fromIso(config.initialStart) || new Date();
        const initialSelected = (config.initialSelected || []).map(String);

        return {
            employees: config.employees || [],
            dalamDaerah: config.dalamDaerah || [],
            luarDaerahKalbar: config.luarDaerahKalbar || [],
            luarDaerahLuarProvinsi: config.luarDaerahLuarProvinsi || [],
            tipe: config.initialTipe || 'Dalam Daerah',
            kategori: config.initialKategori || '',
            tujuan: config.initialTujuan || '',
            tujuanOptions: [],
            start: config.initialStart || null,
            end: config.initialEnd || null,
            tanggalSurat: config.initialSurat || toIso(new Date()),
            viewYear: anchor.getFullYear(),
            viewMonth: anchor.getMonth(),
            todayIso: toIso(new Date()),
            selectedIds: initialSelected,
            vehicles: config.initialVehicles || {},
            draggedId: null,
            dragOverId: null,

            init() {
                this.syncTujuan();
                this.selectedIds.forEach((id) => {
                    if (!this.vehicles[id]) this.vehicles[id] = 'mobil';
                });
            },
            get monthLabel() {
                return `${monthNames[this.viewMonth]} ${this.viewYear}`;
            },
            get cells() {
                const first = new Date(this.viewYear, this.viewMonth, 1);
                const total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                const cells = [];
                for (let i = 0; i < first.getDay(); i++) cells.push({ d: null, iso: null });
                for (let day = 1; day <= total; day++) {
                    cells.push({ d: day, iso: toIso(new Date(this.viewYear, this.viewMonth, day)) });
                }
                return cells;
            },
            get duration() {
                if (!this.start || !this.end) return 0;
                return Math.round((fromIso(this.end) - fromIso(this.start)) / 86400000) + 1;
            },
            get availableEmployees() {
                return this.employees.filter((employee) => !this.selectedIds.includes(employee.id));
            },
            get selectedEmployees() {
                return this.selectedIds
                    .map((id) => this.employees.find((employee) => employee.id === id))
                    .filter(Boolean);
            },
            prevMonth() {
                if (--this.viewMonth < 0) {
                    this.viewMonth = 11;
                    this.viewYear--;
                }
            },
            nextMonth() {
                if (++this.viewMonth > 11) {
                    this.viewMonth = 0;
                    this.viewYear++;
                }
            },
            pickDate(iso) {
                if (!this.start || (this.start && this.end)) {
                    this.start = iso;
                    this.end = null;
                } else if (iso < this.start) {
                    this.end = this.start;
                    this.start = iso;
                } else {
                    this.end = iso;
                }
            },
            cellClass(iso) {
                const isToday = iso === this.todayIso;
                if (iso === this.start) return 'bg-emerald-600 text-white shadow-sm shadow-emerald-200' + (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                if (iso === this.end) return 'bg-blue-600 text-white shadow-sm shadow-blue-200' + (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                if (this.start && this.end && iso > this.start && iso < this.end) return 'bg-emerald-100 text-emerald-700';
                if (isToday) return 'ring-2 ring-inset ring-amber-400 bg-amber-50 text-amber-700 hover:bg-amber-100';
                return 'text-slate-600 hover:bg-slate-100';
            },
            displayDate(iso) {
                if (!iso) return '-';
                const date = fromIso(iso);
                return `${date.getDate()} ${monthNames[date.getMonth()].slice(0, 3)} ${date.getFullYear()}`;
            },
            syncTujuan(isUserChange = false) {
                let opts = [];
                if (this.tipe === 'Dalam Daerah') opts = this.dalamDaerah;
                else if (this.kategori === 'Dalam Provinsi') opts = this.luarDaerahKalbar;
                else if (this.kategori === 'Luar Provinsi') opts = this.luarDaerahLuarProvinsi;
                
                this.tujuanOptions = Array.isArray(opts) ? opts : Object.values(opts || {});

                if (isUserChange) {
                    this.tujuan = this.tujuanOptions.length > 0 ? this.tujuanOptions[0] : '';
                } else {
                    if (this.tujuan && !this.tujuanOptions.includes(this.tujuan)) {
                        this.tujuanOptions.unshift(this.tujuan);
                    }
                }
            },
            dragEmployee(id, event) {
                this.draggedId = id;
                if (event?.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', id);
                }
            },
            dropToSelected() {
                // Drop di area kosong kotak terpilih -> tambah ke bagian bawah
                if (!this.draggedId) return;
                if (!this.selectedIds.includes(this.draggedId)) {
                    this.selectedIds.push(this.draggedId);
                    if (!this.vehicles[this.draggedId]) this.vehicles[this.draggedId] = 'mobil';
                }
                this.draggedId = null;
                this.dragOverId = null;
                this.refreshIcons();
            },
            dropBeforeSelected(targetId) {
                // Sisipkan peserta yang diseret tepat sebelum targetId (untuk reorder / masuk ke posisi tertentu)
                if (!this.draggedId || this.draggedId === targetId) {
                    this.dragOverId = null;
                    return;
                }
                // Lepas dari posisi lama bila sudah terpilih
                this.selectedIds = this.selectedIds.filter((id) => id !== this.draggedId);
                if (!this.vehicles[this.draggedId]) this.vehicles[this.draggedId] = 'mobil';
                let targetIndex = this.selectedIds.indexOf(targetId);
                if (targetIndex === -1) targetIndex = this.selectedIds.length;
                this.selectedIds.splice(targetIndex, 0, this.draggedId);
                this.draggedId = null;
                this.dragOverId = null;
                this.refreshIcons();
            },
            dropToAvailable() {
                if (!this.draggedId) return;
                this.selectedIds = this.selectedIds.filter((id) => id !== this.draggedId);
                this.draggedId = null;
                this.dragOverId = null;
                this.refreshIcons();
            },
            ensurePersonnel(event) {
                if (this.selectedIds.length === 0) {
                    event.preventDefault();
                    alert('Silakan pilih minimal 1 peserta dengan drag & drop ke kotak Peserta Terpilih.');
                    return;
                }
                if (!this.start || !this.end) {
                    event.preventDefault();
                    alert('Silakan pilih tanggal berangkat dan tanggal kembali pada kalender.');
                }
            },
            refreshIcons() {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },
        };
    }
</script>
@endcomponent
