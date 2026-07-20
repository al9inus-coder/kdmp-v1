@component('layouts.kdmp')

@section('title', 'Detail Paket Pekerjaan')

<div class="space-y-6">

    <x-ui.toast />

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="file-text" class="w-6 h-6 text-indigo-600"></i>
                Detail Paket Pekerjaan
            </h1>
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('staf.packages.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Utama & Klasifikasi -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Card Informasi Utama -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                    <i data-lucide="box" class="w-5 h-5 text-indigo-500"></i>
                    <h3 class="font-bold text-slate-800">Informasi Utama</h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-sm text-left text-slate-600">
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <th class="w-1/3 py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Tahun Anggaran</th>
                                <td class="py-3 px-4">{{ $package->fiscalYear->tahun ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">ID RUP</th>
                                <td class="py-3 px-4 font-mono text-indigo-600">{{ $package->id_rup ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Nama Paket</th>
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $package->nama_paket }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Pagu Anggaran</th>
                                <td class="py-3 px-4 font-bold text-emerald-600">Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card Klasifikasi & Sumber Dana -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                    <i data-lucide="tags" class="w-5 h-5 text-indigo-500"></i>
                    <h3 class="font-bold text-slate-800">Klasifikasi & Sumber Dana</h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-sm text-left text-slate-600">
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <th class="w-1/3 py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Program</th>
                                <td class="py-3 px-4">{{ $package->program?->kode }} {{ $package->program ? '- '.$package->program->nama : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Kegiatan</th>
                                <td class="py-3 px-4">{{ $package->activity?->kode }} {{ $package->activity ? '- '.$package->activity->nama : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Sub Kegiatan</th>
                                <td class="py-3 px-4">{{ $package->subActivity?->kode }} {{ $package->subActivity ? '- '.$package->subActivity->nama : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Rekening Belanja</th>
                                <td class="py-3 px-4">{{ $package->account?->kode }} {{ $package->account ? '- '.$package->account->nama : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Jenis Pengadaan</th>
                                <td class="py-3 px-4">{{ $package->jenis_pengadaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Metode Pengadaan</th>
                                <td class="py-3 px-4">{{ $package->metode_pengadaan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Jadwal & Riwayat -->
        <div class="space-y-6">
            <!-- Card Jadwal -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                    <i data-lucide="calendar" class="w-5 h-5 text-indigo-500"></i>
                    <h3 class="font-bold text-slate-800">Jadwal Pelaksanaan</h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-sm text-left text-slate-600">
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <th class="w-1/2 py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Mulai Pemilihan</th>
                                <td class="py-3 px-4">{{ bulanIndonesia($package->pemilihan_mulai_bulan) }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Selesai Pemilihan</th>
                                <td class="py-3 px-4">{{ bulanIndonesia($package->pemilihan_selesai_bulan) }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Mulai Kontrak</th>
                                <td class="py-3 px-4">{{ bulanIndonesia($package->kontrak_mulai_bulan) }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Selesai Kontrak</th>
                                <td class="py-3 px-4">{{ bulanIndonesia($package->kontrak_selesai_bulan) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card Riwayat Status -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-500"></i>
                    <h3 class="font-bold text-slate-800">Riwayat Status</h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-sm text-left text-slate-600">
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <th class="w-1/2 py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Status Saat Ini</th>
                                <td class="py-3 px-4">
                                    @if($package->status === 'needs_review')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Needs Review
                                        </span>
                                    @elseif($package->status === 'draft')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i> Draft
                                        </span>
                                    @elseif($package->status === 'submitted')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i> Submitted
                                        </span>
                                    @elseif($package->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $package->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Dibuat pada</th>
                                <td class="py-3 px-4">{{ $package->created_at?->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Diajukan pada</th>
                                <td class="py-3 px-4">{{ $package->submitted_at?->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="py-3 px-4 bg-slate-50/30 font-semibold text-slate-700">Disetujui pada</th>
                                <td class="py-3 px-4">{{ $package->approved_at?->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Action Buttons Footer -->
            <div class="flex items-center justify-end gap-3 mt-4">
                @can('update', $package)
                    @if(in_array($package->status, ['needs_review', 'draft']))
                        <a href="{{ route('staf.packages.edit', $package) }}" class="inline-flex justify-center items-center gap-2 w-32 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                            Edit
                        </a>
                    @endif
                @endcan

                @can('submit', $package)
                    @if($package->status === 'draft')
                        <form action="{{ route('staf.packages.submit', $package) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex justify-center items-center gap-2 w-32 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition-colors">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Ajukan
                            </button>
                        </form>
                    @endif
                @endcan

                @can('delete', $package)
                    <form action="{{ route('staf.packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="source" value="staf">
                        <button type="submit" class="inline-flex justify-center items-center gap-2 w-32 py-2.5 text-sm font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition-colors shadow-sm shadow-rose-200">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</div>
@endcomponent
