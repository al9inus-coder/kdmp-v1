@component('layouts.kdmp')

@section('title', 'Detail Paket (Dikecualikan)')

@slot('header')
    <h1 class="mb-1 text-slate-800 font-bold">
        <i class="fas fa-folder-open text-blue-600 mr-2"></i> Detail Informasi Paket (Dikecualikan)
    </h1>
@endslot



    @if(session('error'))
        <div class="p-6 mb-6 rounded-lg bg-red-50 text-red-800 border border-red-200 flex items-start alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-ban mr-1"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-6 mb-6 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-start alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check mr-1"></i> {{ session('success') }}
        </div>
    @endif

<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
    <div class="md:col-span-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-blue-400 shadow-sm h-full mb-0">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold">
                    <i class="fas fa-info-circle text-blue-400 mr-2"></i> Informasi Utama
                </h3>
            </div>
            <div class="p-6 p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-200 mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 35%;" class="pl-4">ID RUP</th>
                                <td class="font-bold">{{ $procurementPackage->package->id_rup ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4">Nama Paket</th>
                                <td>{{ $procurementPackage->package->nama_paket }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4">Tahun Anggaran</th>
                                <td>{{ $procurementPackage->package->fiscalYear->tahun ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4 border-b-0">Pagu</th>
                                <td class="text-blue-600 font-bold border-b-0">Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="md:col-span-6 mt-6 mt-md-0">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-emerald-500 shadow-sm h-full mb-0">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold">
                    <i class="fas fa-tags text-emerald-600 mr-2"></i> Klasifikasi Paket
                </h3>
            </div>
            <div class="p-6 p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-200 mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 35%;" class="pl-4">Jenis Pengadaan</th>
                                <td>
                                    <span class="badge badge-secondary px-2 py-1">{{ $procurementPackage->package->jenis_pengadaan ?? '-' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="pl-4 border-b-0">Metode Pengadaan</th>
                                <td class="border-b-0">{{ $procurementPackage->package->metode_pengadaan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden-outline bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden-warning shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold mb-0">
            <i class="fas fa-edit text-amber-500 mr-2"></i> Pengaturan Dikecualikan
        </h3>
    </div>
    <form method="POST" action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.dikecualikan.update', $procurementPackage) }}">
        @csrf
        @method('PATCH')
        <div class="p-6">
            <div class="mb-6">
                <label>Pilih Tipe Dikecualikan</label>
                <div class="input-group">
                    <select name="dikecualikan_type" id="dikecualikan_type" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required {{ $procurementPackage->status === 'complete' ? 'disabled' : '' }}>
                        <option value="">-- Pilih --</option>
                        <option value="di_luar_sistem" @selected(old('dikecualikan_type', $procurementPackage->dikecualikan_type) == 'di_luar_sistem')>Di Luar Sistem</option>
                        <option value="di_dalam_sistem" @selected(old('dikecualikan_type', $procurementPackage->dikecualikan_type) == 'di_dalam_sistem')>Di Dalam Sistem</option>
                    </select>
                    @if($procurementPackage->status !== 'complete')
                    <div class="input-group-append">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            <i class="fas fa-save mr-1"></i> Simpan Tipe
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="p-6 border-top">

            <!-- Bagian Di Luar Sistem -->
            <div id="section_di_luar_sistem" style="display: none; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                <div class="flex justify-between items-center mb-6">
                    <h5 class="text-blue-600 font-bold mb-0"><i class="fas fa-list-alt mr-2"></i> Daftar Riwayat Transaksi Eksternal</h5>
                    @if($procurementPackage->status !== 'complete')
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 -sm rounded-pill" data-toggle="modal" data-target="#modalAddExternalRecord">
                        <i class="fas fa-plus mr-1"></i> Tambah Transaksi
                    </button>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-200-bordered w-full text-left text-sm text-slate-600 divide-y divide-slate-200 mb-0">
                        <thead class="bg-primary text-white text-center">
                            <tr>
                                <th>No</th>
                                <th>Surat Pesanan</th>
                                <th>Tagihan</th>
                                <th>BAST</th>
                                <th>BAP</th>
                                <th>Kwitansi</th>
                                <th>Nilai Kontrak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procurementPackage->externalRecords as $index => $record)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="small"><b>No:</b> {{ $record->surat_pesanan_no ?? '-' }}</div>
                                        <div class="small text-slate-500"><b>Tgl:</b> {{ $record->surat_pesanan_tgl ? \Carbon\Carbon::parse($record->surat_pesanan_tgl)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><b>No:</b> {{ $record->surat_tagihan_no ?? '-' }}</div>
                                        <div class="small text-slate-500"><b>Tgl:</b> {{ $record->surat_tagihan_tgl ? \Carbon\Carbon::parse($record->surat_tagihan_tgl)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><b>No:</b> {{ $record->bast_no ?? '-' }}</div>
                                        <div class="small text-slate-500"><b>Tgl:</b> {{ $record->bast_tgl ? \Carbon\Carbon::parse($record->bast_tgl)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><b>No:</b> {{ $record->bap_no ?? '-' }}</div>
                                        <div class="small text-slate-500"><b>Tgl:</b> {{ $record->bap_tgl ? \Carbon\Carbon::parse($record->bap_tgl)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><b>No:</b> {{ $record->kwitansi_no ?? '-' }}</div>
                                        <div class="small text-slate-500"><b>Tgl:</b> {{ $record->kwitansi_tgl ? \Carbon\Carbon::parse($record->kwitansi_tgl)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td class="text-right font-bold text-emerald-600">
                                        Rp {{ number_format($record->nilai_kontrak, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($procurementPackage->status !== 'complete')
                                        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.destroy', [$procurementPackage, $record]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 -xs" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 py-3">Belum ada riwayat transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($procurementPackage->externalRecords->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total Realisasi / Transaksi:</th>
                                <th class="text-right text-blue-600 font-bold">Rp {{ number_format($procurementPackage->externalRecords->sum('nilai_kontrak'), 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <div class="text-right mt-6">
                    @if($procurementPackage->status === 'complete')
                        <span class="badge badge-success px-4 py-2" style="font-size: 14px;">
                            <i class="fas fa-check-circle mr-1"></i> Paket Selesai
                        </span>
                    @else
                        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.complete', $procurementPackage) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan paket ini? Data akan dikunci dan tidak dapat diubah lagi.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-bold">
                                <i class="fas fa-check-double mr-1"></i> Selesaikan Paket
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Bagian Di Dalam Sistem -->
            <div id="section_di_dalam_sistem" style="display: none; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fdfdfd;">
                <div class="flex justify-between items-center mb-6">
                    <h5 class="text-emerald-600 font-bold mb-0"><i class="fas fa-receipt mr-2"></i> Modul Kwitansi</h5>
                    @if($procurementPackage->status !== 'complete')
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 -sm rounded-pill" data-toggle="modal" data-target="#modalAddKwitansi">
                        <i class="fas fa-plus mr-1"></i> Buat Kwitansi
                    </button>
                    @endif
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-200-bordered w-full text-left text-sm text-slate-600 divide-y divide-slate-200 mb-0">
                        <thead class="bg-success text-white text-center">
                            <tr>
                                <th>No</th>
                                <th>Nomor Kwitansi</th>
                                <th>Tanggal</th>
                                <th>Nilai Kontrak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procurementPackage->externalRecords as $index => $record)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $record->kwitansi_no ?? '-' }}</td>
                                    <td class="text-center">{{ $record->kwitansi_tgl ? \Carbon\Carbon::parse($record->kwitansi_tgl)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-right font-bold text-emerald-600">
                                        Rp {{ number_format($record->nilai_kontrak, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" onclick="printKwitansi('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.print', [$procurementPackage, $record]) }}')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 -xs" title="Cetak Kwitansi">
                                            <i class="fas fa-print"></i> Cetak
                                        </button>
                                        @if($procurementPackage->status !== 'complete')
                                        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.destroy', [$procurementPackage, $record]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 -xs" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-500 py-3">Belum ada riwayat kwitansi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($procurementPackage->externalRecords->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total Realisasi:</th>
                                <th class="text-right text-emerald-600 font-bold">Rp {{ number_format($procurementPackage->externalRecords->sum('nilai_kontrak'), 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <div class="text-right mt-6">
                    @if($procurementPackage->status === 'complete')
                        <span class="badge badge-success px-4 py-2" style="font-size: 14px;">
                            <i class="fas fa-check-circle mr-1"></i> Paket Selesai
                        </span>
                    @else
                        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.complete', $procurementPackage) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan paket ini? Data akan dikunci dan tidak dapat diubah lagi.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-bold">
                                <i class="fas fa-check-double mr-1"></i> Selesaikan Paket
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
</div>

<!-- Modal Tambah Transaksi -->
<div class="modal fade" id="modalAddExternalRecord" tabindex="-1" role="dialog" aria-labelledby="modalAddExternalRecordLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAddExternalRecordLabel"><i class="fas fa-plus-circle mr-2"></i>Tambah Transaksi Eksternal</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.store', $procurementPackage) }}" method="POST">
          @csrf
          <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 mb-6">
                        <label>No. Surat Pesanan</label>
                        <input type="text" name="surat_pesanan_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-6 mb-6">
                        <label>Tanggal Surat Pesanan</label>
                        <input type="date" name="surat_pesanan_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 mb-6">
                        <label>No. Surat Tagihan</label>
                        <input type="text" name="surat_tagihan_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-6 mb-6">
                        <label>Tanggal Surat Tagihan</label>
                        <input type="date" name="surat_tagihan_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 mb-6">
                        <label>No. BAST</label>
                        <input type="text" name="bast_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-6 mb-6">
                        <label>Tanggal BAST</label>
                        <input type="date" name="bast_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 mb-6">
                        <label>No. BAP</label>
                        <input type="text" name="bap_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-6 mb-6">
                        <label>Tanggal BAP</label>
                        <input type="date" name="bap_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-6 mb-6">
                        <label>No. Kwitansi</label>
                        <input type="text" name="kwitansi_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-6 mb-6">
                        <label>Tanggal Kwitansi</label>
                        <input type="date" name="kwitansi_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <label>Nilai Kontrak / Nilai Tagihan <span class="text-red-600">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="number" step="0.01" name="nilai_kontrak" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    </div>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 rounded-pill" data-dismiss="modal">Batal</button>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 rounded-pill"><i class="fas fa-save mr-1"></i> Simpan Transaksi</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Kwitansi -->
<div class="modal fade" id="modalAddKwitansi" tabindex="-1" role="dialog" aria-labelledby="modalAddKwitansiLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalAddKwitansiLabel"><i class="fas fa-receipt mr-2"></i>Buat Kwitansi Baru</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.store', $procurementPackage) }}" method="POST">
          @csrf
          <div class="modal-body">
                <div class="mb-6">
                    <label>No. Kwitansi</label>
                    <input type="text" name="kwitansi_no" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Contoh: 001/PERKIMPLH-C/2026">
                </div>

                <div class="mb-6">
                    <label>Tanggal Kwitansi</label>
                    <input type="date" name="kwitansi_tgl" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                </div>

                <div class="mb-6">
                    <label>Nilai Kontrak / Uang Sejumlah <span class="text-red-600">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
                        <input type="number" step="0.01" name="nilai_kontrak" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" required>
                    </div>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 rounded-pill" data-dismiss="modal">Batal</button>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 rounded-pill"><i class="fas fa-save mr-1"></i> Simpan Kwitansi</button>
          </div>
      </form>
    </div>
  </div>
</div>


@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectType = document.getElementById('dikecualikan_type');
        const sectionLuar = document.getElementById('section_di_luar_sistem');
        const sectionDalam = document.getElementById('section_di_dalam_sistem');

        function toggleSections() {
            if (selectType.value === 'di_luar_sistem') {
                sectionLuar.style.display = 'block';
                sectionDalam.style.display = 'none';
            } else if (selectType.value === 'di_dalam_sistem') {
                sectionLuar.style.display = 'none';
                sectionDalam.style.display = 'block';
            } else {
                sectionLuar.style.display = 'none';
                sectionDalam.style.display = 'none';
            }
        }

        selectType.addEventListener('change', toggleSections);
        toggleSections(); // run on load
    });

    function printKwitansi(url) {
        let iframe = document.getElementById('print_iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print_iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
    }
</script>
@endpush
@endcomponent
