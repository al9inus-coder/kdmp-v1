@extends('adminlte::page')

@section('title', 'Master SBU Lembur')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Master Standar Biaya Lembur</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Data
        </button>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">1.24.1 UANG LEMBUR</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Golongan / Kategori</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-right">Besaran (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($uangLemburs as $item)
                                <tr>
                                    <td>{{ $item->golongan }}</td>
                                    <td class="text-center">{{ $item->satuan }}</td>
                                    <td class="text-right">{{ number_format($item->besaran, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('sbu-lemburs.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title">Edit Biaya Lembur</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('sbu-lemburs.update', $item) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body text-left">
                                                    <div class="form-group">
                                                        <label>Jenis</label>
                                                        <select name="jenis" class="form-control" required>
                                                            <option value="Uang Lembur" {{ $item->jenis == 'Uang Lembur' ? 'selected' : '' }}>Uang Lembur</option>
                                                            <option value="Uang Makan Lembur" {{ $item->jenis == 'Uang Makan Lembur' ? 'selected' : '' }}>Uang Makan Lembur</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Kategori / Golongan</label>
                                                        <input type="text" name="golongan" class="form-control" value="{{ $item->golongan }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Satuan (Misal: OJ, OH)</label>
                                                        <input type="text" name="satuan" class="form-control" value="{{ $item->satuan }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Besaran (Rp)</label>
                                                        <input type="number" name="besaran" class="form-control" value="{{ $item->besaran }}" min="0" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">1.24.2 UANG MAKAN LEMBUR</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Golongan / Kategori</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-right">Besaran (Rp)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($uangMakanLemburs as $item)
                                <tr>
                                    <td>{{ $item->golongan }}</td>
                                    <td class="text-center">{{ $item->satuan }}</td>
                                    <td class="text-right">{{ number_format($item->besaran, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('sbu-lemburs.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title">Edit Biaya Lembur</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('sbu-lemburs.update', $item) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body text-left">
                                                    <div class="form-group">
                                                        <label>Jenis</label>
                                                        <select name="jenis" class="form-control" required>
                                                            <option value="Uang Lembur" {{ $item->jenis == 'Uang Lembur' ? 'selected' : '' }}>Uang Lembur</option>
                                                            <option value="Uang Makan Lembur" {{ $item->jenis == 'Uang Makan Lembur' ? 'selected' : '' }}>Uang Makan Lembur</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Kategori / Golongan</label>
                                                        <input type="text" name="golongan" class="form-control" value="{{ $item->golongan }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Satuan (Misal: OJ, OH)</label>
                                                        <input type="text" name="satuan" class="form-control" value="{{ $item->satuan }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Besaran (Rp)</label>
                                                        <input type="number" name="besaran" class="form-control" value="{{ $item->besaran }}" min="0" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah SBU Lembur</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sbu-lemburs.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Jenis</label>
                            <select name="jenis" class="form-control" required>
                                <option value="Uang Lembur">Uang Lembur</option>
                                <option value="Uang Makan Lembur">Uang Makan Lembur</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori / Golongan</label>
                            <input type="text" name="golongan" class="form-control" placeholder="Contoh: Golongan IV" required>
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" name="satuan" class="form-control" placeholder="Contoh: OJ atau OH" required>
                        </div>
                        <div class="form-group">
                            <label>Besaran (Rp)</label>
                            <input type="number" name="besaran" class="form-control" placeholder="Contoh: 36000" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
