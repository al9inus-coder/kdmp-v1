@component('layouts.kdmp')
    @section('title', 'Koreksi Biaya Rampung')

    @php
        $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
        $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
        $nights = max(0, $days - 1);
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

        // Komponen biaya per pelaksana (sama seperti tab Biaya staf).
        $mk = function ($label, $koef, $satuan, $stored, $estAmt, $sbuRate = null, $riil = false) {
            $koef = max(0, $koef);
            $divisor = $koef > 0 ? $koef : 1;
            $amount = (int) ($stored > 0 ? $stored : ($estAmt ?? 0));
            return [
                'label' => $label, 'koef' => $koef, 'satuan' => $satuan,
                'rate' => $koef > 0 ? $amount / $divisor : 0,
                'std'  => $sbuRate ?? (($estAmt ?? 0) / $divisor),
                'riil' => (bool) $riil,
            ];
        };

        $spjTable = $travelOrder->personnels->mapWithKeys(function ($p) use ($estimates, $days, $nights, $isLuarDaerah, $mk) {
            $est = $estimates[$p->id] ?? [];
            $n = (int) ($est['nights'] ?? $nights);
            $comp = [];
            $comp['uang_harian'] = $mk('Uang harian', $days, 'hari', $p->uang_harian, $est['uang_harian'] ?? 0, $est['base_uang_harian'] ?? null);
            $comp['biaya_transport'] = $mk('Transport', 1, 'kali', $p->biaya_transport, $est['biaya_transport'] ?? 0, null, $p->transport_riil);
            if ($isLuarDaerah) {
                $comp['biaya_taksi'] = $mk('Taksi', 1, 'kali', $p->biaya_taksi ?? 0, $est['biaya_taksi'] ?? 0, null, $p->taksi_riil);
            }
            $comp['biaya_penginapan'] = $mk('Penginapan', $n, 'malam', $p->biaya_penginapan, $est['biaya_penginapan'] ?? 0, $est['base_penginapan'] ?? null, $p->penginapan_riil);
            $comp['biaya_representasi'] = $mk('Representasi', $days, 'hari', $p->biaya_representasi, $est['biaya_representasi'] ?? 0, $est['base_representasi'] ?? null);
            return [$p->id => $comp];
        });

        $alpineRows = $spjTable->map(fn ($comp) => collect($comp)->map(fn ($c) => [
            'koef' => $c['koef'], 'rate' => $c['rate'], 'std' => $c['std'], 'riil' => $c['riil'],
        ]))->toArray();
    @endphp

    <div class="space-y-6"
        x-data="{
            rows: @js($alpineRows),
            compTotal(pid, key) { const c = this.rows[pid][key]; return (Number(c.koef)||0) * (Number(c.rate)||0); },
            rowTotal(pid) { let s = 0; for (const k in this.rows[pid]) s += this.compTotal(pid, k); return s; },
            get grandTotal() { let s = 0; for (const pid in this.rows) s += this.rowTotal(pid); return s; },
            rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n) || 0); },
            fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n)) || 0); },
            parseNum(s) { return Number(String(s).replace(/[^\d]/g, '')) || 0; },
        }">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                    {{ $travelOrder->tempat_tujuan }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $travelOrder->spjStatusMeta()['badge'] }}">
                    <i data-lucide="{{ $travelOrder->spjStatusMeta()['icon'] }}" class="w-3.5 h-3.5"></i>
                    SPJ: {{ $travelOrder->spjStatusMeta()['label'] }}
                </span>
            </div>
            <a href="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.travel-orders.show', [$package, $travelOrder]) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Detail
            </a>
        </div>

        {{-- Banner mode koreksi --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="shield-alert" class="w-4 h-4"></i></span>
            <div>
                <p class="font-bold text-amber-800 text-sm">Mode Koreksi Admin</p>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                    Perbaiki angka biaya rampung yang keliru. Status SPPD/SPJ, daftar pelaksana, dan data perjalanan
                    <strong>tidak berubah</strong>. Setiap koreksi dicatat (siapa &amp; kapan).
                    @if($travelOrder->spjKoreksiBy)
                        <br>Koreksi terakhir: <strong>{{ $travelOrder->spjKoreksiBy->name }}</strong>, {{ $travelOrder->spj_koreksi_at?->locale('id')->diffForHumans() }}.
                    @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.travel-orders.koreksi-biaya.update', [$package, $travelOrder]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @error('personnels')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div>
            @enderror

            @foreach($travelOrder->personnels as $i => $personnel)
                @php $comp = $spjTable[$personnel->id]; @endphp
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $i === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold {{ $i === 0 ? 'text-amber-700' : 'text-slate-900' }} leading-snug truncate">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                                <p class="text-xs text-slate-400">{{ $personnel->employee?->jabatan ?? '-' }} &bull; Gol. {{ $personnel->employee?->golongan ?? '-' }} &bull; {{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total</p>
                            <p class="text-base font-extrabold text-emerald-700" x-text="rp(rowTotal({{ $personnel->id }}))"></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-semibold bg-white border-b border-slate-100">
                                    <th class="text-left font-semibold px-2.5 sm:px-4 py-2.5">Nama Biaya</th>
                                    <th class="text-center font-semibold px-2 sm:px-3 py-2.5">Koef.</th>
                                    <th class="hidden sm:table-cell text-right font-semibold px-3 py-2.5 w-32">Harga SBU</th>
                                    <th class="text-right font-semibold px-2 sm:px-3 py-2.5">Harga Satuan</th>
                                    <th class="text-right font-semibold px-2.5 sm:px-4 py-2.5">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($comp as $key => $c)
                                    <tr>
                                        <td class="px-2.5 sm:px-4 py-2.5 align-top font-semibold text-slate-700">
                                            {{ $c['label'] }}
                                            @if($key === 'biaya_representasi')
                                                <span class="block text-[10px] font-normal text-slate-400">Khusus Eselon II</span>
                                            @endif
                                            <span class="sm:hidden block text-[10px] font-normal text-slate-400 mt-0.5">SBU: {{ $money($c['std']) }}</span>
                                            @if(in_array($key, ['biaya_transport', 'biaya_taksi'], true))
                                                <label class="mt-1 flex items-start gap-1.5 text-[10px] font-semibold text-slate-500 cursor-pointer">
                                                    <input type="checkbox" class="mt-0.5 w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0"
                                                        x-model="rows[{{ $personnel->id }}]['{{ $key }}'].riil">
                                                    <span>Tanpa bukti &mdash; masuk pengeluaran riil</span>
                                                </label>
                                            @elseif($key === 'biaya_penginapan' && $c['koef'] > 0)
                                                <label class="mt-1 flex items-start gap-1.5 text-[10px] font-semibold text-slate-500 cursor-pointer">
                                                    <input type="checkbox" class="mt-0.5 w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0"
                                                        x-model="rows[{{ $personnel->id }}]['{{ $key }}'].riil"
                                                        @change="rows[{{ $personnel->id }}]['{{ $key }}'].rate = $event.target.checked
                                                            ? Math.round(rows[{{ $personnel->id }}]['{{ $key }}'].std * 0.3)
                                                            : rows[{{ $personnel->id }}]['{{ $key }}'].std">
                                                    <span>Tidak menginap di hotel &mdash; dibayar 30% tarif SBU</span>
                                                </label>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-3 py-2.5 align-top text-center whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1">
                                                <input type="text" inputmode="numeric" autocomplete="off"
                                                    :value="rows[{{ $personnel->id }}]['{{ $key }}'].koef"
                                                    @input="rows[{{ $personnel->id }}]['{{ $key }}'].koef = parseNum($event.target.value); $event.target.value = rows[{{ $personnel->id }}]['{{ $key }}'].koef"
                                                    class="w-12 text-center rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                <span class="text-[11px] text-slate-400">{{ $c['satuan'] }}</span>
                                            </div>
                                        </td>
                                        <td class="hidden sm:table-cell px-3 py-2.5 align-top text-right text-slate-500 whitespace-nowrap">
                                            {{ $money($c['std']) }}
                                        </td>
                                        <td class="px-2 sm:px-3 py-2.5 align-top">
                                            <input type="text" inputmode="numeric" autocomplete="off"
                                                @if($key === 'biaya_penginapan')
                                                    :disabled="Number(rows[{{ $personnel->id }}]['{{ $key }}'].koef) <= 0 || rows[{{ $personnel->id }}]['{{ $key }}'].riil"
                                                @else
                                                    :disabled="Number(rows[{{ $personnel->id }}]['{{ $key }}'].koef) <= 0"
                                                @endif
                                                :value="fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                @input="rows[{{ $personnel->id }}]['{{ $key }}'].rate = parseNum($event.target.value); $event.target.value = fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                class="w-full min-w-[76px] text-right rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                                            <input type="hidden" name="personnels[{{ $personnel->id }}][{{ $key }}]"
                                                :value="Math.round(compTotal({{ $personnel->id }}, '{{ $key }}'))">
                                            @if(in_array($key, ['biaya_transport', 'biaya_taksi', 'biaya_penginapan'], true))
                                                <input type="hidden" name="personnels[{{ $personnel->id }}][{{ str_replace('biaya_', '', $key) }}_riil]"
                                                    :value="rows[{{ $personnel->id }}]['{{ $key }}'].riil ? 1 : 0">
                                            @endif
                                        </td>
                                        <td class="px-2.5 sm:px-4 py-2.5 align-top">
                                            <input type="text" inputmode="numeric" autocomplete="off"
                                                @if($key === 'biaya_penginapan')
                                                    :disabled="Number(rows[{{ $personnel->id }}]['{{ $key }}'].koef) <= 0 || rows[{{ $personnel->id }}]['{{ $key }}'].riil"
                                                @else
                                                    :disabled="Number(rows[{{ $personnel->id }}]['{{ $key }}'].koef) <= 0"
                                                @endif
                                                :value="fmt(compTotal({{ $personnel->id }}, '{{ $key }}'))"
                                                @input="const v = parseNum($event.target.value); const c = rows[{{ $personnel->id }}]['{{ $key }}']; const k = Number(c.koef) || 0; c.rate = k > 0 ? v / k : 0; $event.target.value = fmt(compTotal({{ $personnel->id }}, '{{ $key }}'))"
                                                class="w-full min-w-[88px] text-right font-bold text-slate-800 rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-100 bg-slate-50/50">
                                    <td colspan="3" class="sm:hidden px-2.5 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total</td>
                                    <td colspan="4" class="hidden sm:table-cell px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total</td>
                                    <td class="px-2.5 sm:px-4 py-2.5 text-right font-extrabold text-emerald-700 whitespace-nowrap" x-text="rp(rowTotal({{ $personnel->id }}))"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @unless($isLuarDaerah)
                        <input type="hidden" name="personnels[{{ $personnel->id }}][biaya_taksi]" value="0">
                    @endunless
                </div>
            @endforeach

            {{-- Ringkasan + aksi --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total Realisasi (setelah koreksi)</p>
                    <p class="text-2xl font-extrabold text-emerald-700" x-text="rp(grandTotal)"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan koreksi <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}"
                        placeholder="Mis: koreksi salah input tarif penginapan"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit"
                    onclick="return confirm('Simpan koreksi biaya rampung? Status SPPD tidak akan berubah.')"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Koreksi
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
    </script>
@endcomponent
