@csrf

<div class="card card-outline card-primary shadow-sm">

    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-file-signature mr-2"></i>
            Informasi Surat Permohonan Pengadaan
        </h5>
    </div>

    <div class="card-body">

        <div class="row">

            {{-- NOMOR SURAT --}}
            <div class="col-md-6">

                <div class="form-group">
                    <label for="nomor_surat">
                        Nomor Urut Surat
                    </label>

                    <input type="text"
                           id="nomor_surat"
                           name="nomor_surat"
                           class="form-control @error('nomor_surat') is-invalid @enderror"
                           placeholder="Contoh: 015"
                           value="{{ old('nomor_surat', $procurementRequest->nomor_surat) }}">

                    <small class="text-muted">
                        Isi nomor urut surat saja.
                    </small>

                    @error('nomor_surat')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>

            {{-- TANGGAL SURAT --}}
            <div class="col-md-6">

                <div class="form-group">
                    <label for="tanggal_surat">
                        Tanggal Surat
                    </label>

                    <input type="date"
                           id="tanggal_surat"
                           name="tanggal_surat"
                           class="form-control @error('tanggal_surat') is-invalid @enderror"
                           value="{{ old('tanggal_surat', optional($procurementRequest->tanggal_surat)->format('Y-m-d')) }}">

                    @error('tanggal_surat')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>

        </div>

        {{-- PREVIEW NOMOR SURAT --}}
        <div class="alert alert-light border mb-4">

            <strong>Preview Nomor Surat:</strong>

            <div class="mt-1 text-primary font-weight-bold">
                000.3.2/{{ old('nomor_surat', $procurementRequest->nomor_surat ?: 'XXX') }}/SP-PBJ/2.11.11/PERKIMPLH-C
            </div>

        </div>

        {{-- PEJABAT PENGADAAN --}}
        <div class="form-group">

            <label for="nama_pejabat_pengadaan">
                Nama Pejabat Pengadaan
            </label>

            <input type="text"
                   id="nama_pejabat_pengadaan"
                   name="nama_pejabat_pengadaan"
                   class="form-control @error('nama_pejabat_pengadaan') is-invalid @enderror"
                   value="{{ old('nama_pejabat_pengadaan', $procurementRequest->nama_pejabat_pengadaan) }}"
                   placeholder="Masukkan nama pejabat pengadaan">

            @error('nama_pejabat_pengadaan')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>

        {{-- PENYEDIA --}}
        <div class="form-group">

            <label for="nama_penyedia">
                Penyedia yang Dipilih
            </label>

            <select id="nama_penyedia"
                    name="nama_penyedia"
                    class="form-control @error('nama_penyedia') is-invalid @enderror">

                <option value="">
                    -- Pilih Penyedia --
                </option>

                @foreach($vendors as $vendor)

                    <option value="{{ $vendor }}"
                        @selected(
                            old(
                                'nama_penyedia',
                                $procurementRequest->nama_penyedia
                            ) == $vendor
                        )>

                        {{ $vendor }}

                    </option>

                @endforeach

            </select>

            @error('nama_penyedia')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>

        {{-- ALASAN PEMILIHAN --}}
        <div class="form-group">

            <label for="alasan_pemilihan_penyedia">
                Alasan Pemilihan Penyedia
            </label>

            <textarea
                id="alasan_pemilihan_penyedia"
                name="alasan_pemilihan_penyedia"
                rows="4"
                class="form-control @error('alasan_pemilihan_penyedia') is-invalid @enderror"
                placeholder="Jelaskan alasan pemilihan penyedia...">{{ old('alasan_pemilihan_penyedia', $procurementRequest->alasan_pemilihan_penyedia) }}</textarea>

            @error('alasan_pemilihan_penyedia')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>

        {{-- KETERANGAN TAMBAHAN --}}
        <div class="form-group">

            <label for="isi_surat">
                Keterangan Tambahan
            </label>

            <textarea
                id="isi_surat"
                name="isi_surat"
                rows="3"
                class="form-control @error('isi_surat') is-invalid @enderror"
                placeholder="Keterangan tambahan (opsional)">{{ old('isi_surat', $procurementRequest->isi_surat) }}</textarea>

            @error('isi_surat')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

        </div>

    </div>

</div>

<div class="d-flex justify-content-between mt-4">

    <a href="{{ route('procurement-packages.show', $procurementPackage->package) }}"
       class="btn btn-secondary">

        <i class="fas fa-arrow-left mr-1"></i>

        Kembali

    </a>

    <button type="submit"
            class="btn btn-success">

        <i class="fas fa-save mr-1"></i>

        {{ $submitLabel }}

    </button>

</div>