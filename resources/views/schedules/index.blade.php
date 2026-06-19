@extends('adminlte::page')

@section('title', 'Jadwal Rencana Pengadaan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            <h2 class="font-weight-bold text-dark" style="letter-spacing: -1px;">
                Jadwal <span class="text-primary">Pengadaan</span>
            </h2>
            <p class="text-muted mb-0">Timeline Rencana Umum Pengadaan (RUP) Tahun Anggaran Berjalan.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('schedules.index') }}" class="form-inline">
                <label class="mr-2 font-weight-bold">Tahun Anggaran:</label>
                <select name="fiscal_year_id" class="form-control rounded-pill border-primary" style="font-weight: bold; padding-left: 20px; padding-right: 20px;" onchange="this.form.submit()">
                    @foreach($fiscalYears as $year)
                        <option value="{{ $year->id }}" {{ $fiscalYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->tahun }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
@stop

@section('content')
    <div class="card" style="border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
            <h5 class="font-weight-bold mb-0"><i class="fas fa-calendar-alt text-primary mr-2"></i> Timeline Master RUP</h5>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><i class="fas fa-filter mr-1"></i> Top {{ $packages->count() }} Paket (Pagu Tertinggi)</span>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="table-responsive rounded border" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.85rem; min-width: 900px;">
                    <thead class="bg-light" style="position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <tr>
                            <th rowspan="2" class="align-middle text-left bg-light" style="width: 300px; padding-left: 15px;">Nama Paket</th>
                            <th colspan="12" class="bg-light py-2">Bulan Pelaksanaan</th>
                        </tr>
                        <tr>
                            @for($i=1; $i<=12; $i++)
                                <th class="bg-light pb-2" style="width: 50px;">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('M') }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $paket)
                            <tr>
                                <td class="text-left font-weight-bold align-middle" style="max-width: 300px; white-space: normal; padding-left: 15px;" title="{{ $paket->nama_paket }}">
                                    <a href="{{ route('procurement-packages.show', $paket) }}" class="text-dark">{{ Str::limit($paket->nama_paket, 50) }}</a>
                                    <div class="text-xs text-muted font-weight-normal mt-1">
                                        <i class="fas fa-square text-warning mr-1"></i>Pemilihan 
                                        <i class="fas fa-square text-info ml-2 mr-1"></i>Kontrak
                                    </div>
                                </td>
                                @for($i=1; $i<=12; $i++)
                                    @php
                                        $isPemilihan = ($i >= $paket->pemilihan_mulai_bulan && $i <= $paket->pemilihan_selesai_bulan);
                                        $isKontrak = ($i >= $paket->kontrak_mulai_bulan && $i <= $paket->kontrak_selesai_bulan);
                                        
                                        $bgStyle = '';
                                        if ($isPemilihan && $isKontrak) {
                                            $bgStyle = 'background: linear-gradient(135deg, #ffc107 50%, #17a2b8 50%);';
                                        } elseif ($isPemilihan) {
                                            $bgStyle = 'background-color: #ffc107;'; // Warning (Kuning)
                                        } elseif ($isKontrak) {
                                            $bgStyle = 'background-color: #17a2b8;'; // Info (Biru)
                                        }
                                    @endphp
                                    <td class="align-middle" style="{{ $bgStyle }} border-radius: 4px; border: 2px solid white;"></td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-muted py-5 text-center">
                                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-25 d-block"></i>
                                    Belum ada data jadwal paket (Bulan Pemilihan/Kontrak belum diatur di Master Paket).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 border-top pt-3 text-muted text-sm d-flex align-items-center">
                <i class="fas fa-info-circle mr-2 text-info"></i>
                <p class="mb-0">Data ini ditarik secara otomatis dari Rencana Umum Pengadaan (RUP). Kotak kuning menandakan rentang jadwal <strong>Pemilihan Penyedia</strong>, sedangkan kotak biru menandakan rentang jadwal <strong>Pelaksanaan Kontrak</strong>.</p>
            </div>
        </div>
    </div>
@stop
