@csrf

@php
    $maksudTujuan = collect([
        $technicalSpecification->maksud,
        $technicalSpecification->tujuan,
    ])->filter()->unique()->implode("\n\n");

    $targetSasaran = $technicalSpecification->target_sasaran ?? $technicalSpecification->sasaran;
    $items = old('items');

    if ($items === null) {
        $items = $technicalSpecification->exists
            ? $technicalSpecification->items->map(fn ($item) => [
                'nama_barang_jasa' => $item->nama_barang_jasa,
                'spesifikasi' => $item->spesifikasi,
                'volume' => $item->volume,
                'satuan' => $item->satuan,
                'pdn' => $item->pdn ? 1 : 0,
                'tkdn' => $item->tkdn,
                'kode_mak' => $item->kode_mak,
            ])->values()->toArray()
            : [];
    }

    if ($items === []) {
        $items[] = [
            'nama_barang_jasa' => '',
            'spesifikasi' => '',
            'volume' => 0,
            'satuan' => '',
            'pdn' => 0,
            'tkdn' => '',
            'kode_mak' => '',
        ];
    }
@endphp

<div class="card border">
    <div class="card-header">
        <h5 class="mb-0 font-weight-bold">Narasi Spesifikasi Teknis</h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="latar_belakang">Latar Belakang</label>
            <textarea id="latar_belakang"
                      name="latar_belakang"
                      rows="4"
                      class="form-control @error('latar_belakang') is-invalid @enderror">{{ old('latar_belakang', $technicalSpecification->latar_belakang) }}</textarea>
            @error('latar_belakang')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="maksud_maksud">Maksud</label>
                    <textarea id="maksud_maksud"
                              name="maksud[Maksud]"
                              rows="4"
                              class="form-control @error('maksud.Maksud') is-invalid @enderror">{{ old('maksud.Maksud', $technicalSpecification->maksud['Maksud'] ?? '') }}</textarea>
                    @error('maksud.Maksud')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="maksud_tujuan">Tujuan</label>
                    <textarea id="maksud_tujuan"
                              name="maksud[Tujuan]"
                              rows="4"
                              class="form-control @error('maksud.Tujuan') is-invalid @enderror">{{ old('maksud.Tujuan', $technicalSpecification->maksud['Tujuan'] ?? '') }}</textarea>
                    @error('maksud.Tujuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="target_sasaran_target">Target</label>
                    <textarea id="target_sasaran_target"
                              name="target_sasaran[Target]"
                              rows="4"
                              class="form-control @error('target_sasaran.Target') is-invalid @enderror">{{ old('target_sasaran.Target', $technicalSpecification->target_sasaran['Target'] ?? '') }}</textarea>
                    @error('target_sasaran.Target')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="target_sasaran_sasaran">Sasaran</label>
                    <textarea id="target_sasaran_sasaran"
                              name="target_sasaran[Sasaran]"
                              rows="4"
                              class="form-control @error('target_sasaran.Sasaran') is-invalid @enderror">{{ old('target_sasaran.Sasaran', $technicalSpecification->target_sasaran['Sasaran'] ?? '') }}</textarea>
                    @error('target_sasaran.Sasaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="uraian_pekerjaan">Uraian Pekerjaan</label>
            <textarea id="uraian_pekerjaan"
                      name="uraian_pekerjaan"
                      rows="5"
                      class="form-control @error('uraian_pekerjaan') is-invalid @enderror">{{ old('uraian_pekerjaan', $technicalSpecification->uraian_pekerjaan) }}</textarea>
            @error('uraian_pekerjaan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="card border">
    <div class="card-header">
        <h5 class="mb-0 font-weight-bold">Informasi Kontrak</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jangka_waktu">Jangka Waktu</label>
                    <input type="number"
                           id="jangka_waktu"
                           name="jangka_waktu"
                           min="0"
                           class="form-control @error('jangka_waktu') is-invalid @enderror"
                           value="{{ old('jangka_waktu', $procurementPackage->jangka_waktu_nilai) }}">
                    @error('jangka_waktu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jangka_waktu_jenis">Jenis Jangka Waktu</label>
                    <select id="jangka_waktu_jenis"
                            name="jangka_waktu_jenis"
                            class="form-control @error('jangka_waktu_jenis') is-invalid @enderror">
                        <option value="">Pilih Jenis</option>
                        <option value="pengiriman_barang" @selected(old('jangka_waktu_jenis', $procurementPackage->jangka_waktu_satuan === 'hari' ? 'pengiriman_barang' : '') === 'pengiriman_barang')>
                            Pengiriman Barang
                        </option>
                        <option value="pekerjaan_jasa" @selected(old('jangka_waktu_jenis', $procurementPackage->jangka_waktu_satuan !== 'hari' && $procurementPackage->jangka_waktu_satuan ? 'pekerjaan_jasa' : '') === 'pekerjaan_jasa')>
                            Pekerjaan Jasa
                        </option>
                    </select>
                    @error('jangka_waktu_jenis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="garansi_nilai">Garansi</label>
                    <input type="number"
                           id="garansi_nilai"
                           name="garansi_nilai"
                           min="0"
                           class="form-control @error('garansi_nilai') is-invalid @enderror"
                           value="{{ old('garansi_nilai', $procurementPackage->garansi_nilai) }}">
                    @error('garansi_nilai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="garansi_satuan">Satuan Garansi</label>
                    <select id="garansi_satuan"
                            name="garansi_satuan"
                            class="form-control @error('garansi_satuan') is-invalid @enderror">
                        <option value="">Pilih Satuan</option>
                        <option value="hari" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) === 'hari')>Hari</option>
                        <option value="bulan" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) === 'bulan')>Bulan</option>
                        <option value="tahun" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) === 'tahun')>Tahun</option>
                    </select>
                    @error('garansi_satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="layanan_purna_jual">Layanan Purna Jual</label>
                    <select id="layanan_purna_jual"
                            name="layanan_purna_jual"
                            class="form-control @error('layanan_purna_jual') is-invalid @enderror">
                        <option value="0" @selected((string) old('layanan_purna_jual', (int) $procurementPackage->layanan_purna_jual) === '0')>Tidak</option>
                        <option value="1" @selected((string) old('layanan_purna_jual', (int) $procurementPackage->layanan_purna_jual) === '1')>Ya</option>
                    </select>
                    @error('layanan_purna_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jenis_kontrak">Jenis Kontrak</label>
                    <select id="jenis_kontrak"
                            name="jenis_kontrak"
                            class="form-control @error('jenis_kontrak') is-invalid @enderror">
                        <option value="">Pilih Jenis Kontrak</option>
                        @foreach(['Harga Satuan', 'Lump Sum', 'Gabungan Lump Sum dan Harga Satuan', 'Payung', 'Turnkey', 'Kontrak Kinerja'] as $jenisKontrak)
                            <option value="{{ $jenisKontrak }}" @selected(old('jenis_kontrak', $procurementPackage->jenis_kontrak) === $jenisKontrak)>
                                {{ $jenisKontrak }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_kontrak')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border">
    <div class="card-header">
        <h5 class="mb-0 font-weight-bold">Informasi PPK</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nama_ppk">Nama PPK</label>
                    <input type="text"
                           id="nama_ppk"
                           name="nama_ppk"
                           class="form-control @error('nama_ppk') is-invalid @enderror"
                           value="{{ old('nama_ppk', $procurementPackage->nama_ppk) }}">
                    @error('nama_ppk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="pangkat_gol_ppk">Pangkat/Golongan</label>
                    <input type="text"
                           id="pangkat_gol_ppk"
                           name="pangkat_gol_ppk"
                           class="form-control @error('pangkat_gol_ppk') is-invalid @enderror"
                           value="{{ old('pangkat_gol_ppk', $procurementPackage->pangkat_gol_ppk) }}">
                    @error('pangkat_gol_ppk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nip_ppk">NIP</label>
                    <input type="text"
                           id="nip_ppk"
                           name="nip_ppk"
                           class="form-control @error('nip_ppk') is-invalid @enderror"
                           value="{{ old('nip_ppk', $procurementPackage->nip_ppk) }}">
                    @error('nip_ppk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="npwp_instansi">NPWP Instansi</label>
                    <input type="text"
                           id="npwp_instansi"
                           name="npwp_instansi"
                           class="form-control @error('npwp_instansi') is-invalid @enderror"
                           value="{{ old('npwp_instansi', $procurementPackage->npwp_instansi) }}">
                    @error('npwp_instansi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="no_telp_ppk">No Telp PPK</label>
                    <input type="text"
                           id="no_telp_ppk"
                           name="no_telp_ppk"
                           class="form-control @error('no_telp_ppk') is-invalid @enderror"
                           value="{{ old('no_telp_ppk', $procurementPackage->no_telp_ppk) }}">
                    @error('no_telp_ppk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email_ppk">Email PPK</label>
                    <input type="email"
                           id="email_ppk"
                           name="email_ppk"
                           class="form-control @error('email_ppk') is-invalid @enderror"
                           value="{{ old('email_ppk', $procurementPackage->email_ppk) }}">
                    @error('email_ppk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold">Rincian Barang/Jasa</h5>
        <button type="button" class="btn btn-sm btn-primary" id="add-item-row">
            <i class="fas fa-plus mr-1"></i> Tambah Baris
        </button>
    </div>
    <div class="card-body">
        @error('items')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="table-responsive">
            <table class="table table-bordered" id="items-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="min-width: 180px;">Nama Barang/Jasa</th>
                        <th style="min-width: 220px;">Spesifikasi</th>
                        <th style="width: 130px;">Volume</th>
                        <th style="width: 130px;">Satuan</th>
                        <th style="width: 80px;">Harga Satuan DPA</th>
                        <th style="width: 80px;">PDN</th>
                        <th style="width: 120px;">TKDN</th>
                        <th style="width: 150px;">Kode MAK</th>
                        <th style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr class="item-row">
                            <td class="align-middle item-number">{{ $loop->iteration }}</td>
                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][nama_barang_jasa]"
                                       class="form-control @error('items.'.$index.'.nama_barang_jasa') is-invalid @enderror"
                                       value="{{ $item['nama_barang_jasa'] ?? '' }}">
                                @error('items.'.$index.'.nama_barang_jasa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <textarea name="items[{{ $index }}][spesifikasi]"
                                          rows="2"
                                          class="form-control @error('items.'.$index.'.spesifikasi') is-invalid @enderror">{{ $item['spesifikasi'] ?? '' }}</textarea>
                                @error('items.'.$index.'.spesifikasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][volume]"
                                       min="0"
                                       step="0.01"
                                       class="form-control @error('items.'.$index.'.volume') is-invalid @enderror"
                                       value="{{ $item['volume'] ?? 0 }}">
                                @error('items.'.$index.'.volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][satuan]"
                                       class="form-control @error('items.'.$index.'.satuan') is-invalid @enderror"
                                       value="{{ $item['satuan'] ?? '' }}">
                                @error('items.'.$index.'.satuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][harga_satuan_dpa]"
                                       min="0"
                                       step="0.01"
                                       class="form-control @error('items.'.$index.'.harga_satuan_dpa') is-invalid @enderror"
                                       value="{{ $item['harga_satuan_dpa'] ?? '' }}">
                                @error('items.'.$index.'.harga_satuan_dpa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td class="text-center align-middle">
                                <input type="hidden" name="items[{{ $index }}][pdn]" value="0">
                                <input type="checkbox"
                                       name="items[{{ $index }}][pdn]"
                                       value="1"
                                       @checked((bool) ($item['pdn'] ?? false))>
                            </td>
                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][tkdn]"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       class="form-control @error('items.'.$index.'.tkdn') is-invalid @enderror"
                                       value="{{ $item['tkdn'] ?? '' }}">
                                @error('items.'.$index.'.tkdn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][kode_mak]"
                                       class="form-control @error('items.'.$index.'.kode_mak') is-invalid @enderror"
                                       value="{{ $item['kode_mak'] ?? '' }}">
                                @error('items.'.$index.'.kode_mak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-sm btn-danger remove-item-row">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-success">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('procurement-packages.show', $procurementPackage->package) }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('#items-table tbody');
            const addButton = document.getElementById('add-item-row');

            const renumberRows = function () {
                tableBody.querySelectorAll('.item-row').forEach(function (row, index) {
                    row.querySelector('.item-number').textContent = index + 1;
                });
            };

            const buildRow = function (index) {
                return `
                    <tr class="item-row">
                        <td class="align-middle item-number"></td>
                        <td>
                            <input type="text" name="items[${index}][nama_barang_jasa]" class="form-control">
                        </td>
                        <td>
                            <textarea name="items[${index}][spesifikasi]" rows="2" class="form-control"></textarea>
                        </td>
                        <td>
                            <input type="number" name="items[${index}][volume]" min="0" step="0.01" value="0" class="form-control">
                        </td>
                        <td>
                            <input type="text" name="items[${index}][satuan]" class="form-control">
                        </td>
                        <td class="text-center align-middle">
                            <input type="hidden" name="items[${index}][pdn]" value="0">
                            <input type="checkbox" name="items[${index}][pdn]" value="1">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][tkdn]" min="0" max="100" step="0.01" class="form-control">
                        </td>
                        <td>
                            <input type="text" name="items[${index}][kode_mak]" class="form-control">
                        </td>
                        <td class="align-middle">
                            <button type="button" class="btn btn-sm btn-danger remove-item-row">Hapus</button>
                        </td>
                    </tr>
                `;
            };

            addButton.addEventListener('click', function () {
                const index = Date.now();
                tableBody.insertAdjacentHTML('beforeend', buildRow(index));
                renumberRows();
            });

            tableBody.addEventListener('click', function (event) {
                if (!event.target.classList.contains('remove-item-row')) {
                    return;
                }

                event.target.closest('tr').remove();
                renumberRows();
            });

            renumberRows();
        });
    </script>
@endpush
