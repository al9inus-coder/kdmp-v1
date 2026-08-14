@component('layouts.kdmp')
@section('title', 'Master Pajak')

<x-ui.toast />

@php
    $jenisOptions = \App\Models\TarifPajak::jenisOptions();
    $berkunci = [\App\Models\TarifPajak::PPH21, \App\Models\TarifPajak::PPH4_2_KONSTRUKSI];

    $persen = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',') . '%';
@endphp

<div x-data="pajakPage()">
<x-ui.workspace title="Master Pajak" description="Acuan seluruh tarif pajak yang dipakai aplikasi — PPh 21, PPN, PPh 22/23, pajak restoran, dan PPh Final Pasal 4(2) konstruksi.">
    <x-slot:actions>
        <x-ui.button variant="primary" size="md" type="button" x-on:click="openAdd()">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Tarif
        </x-ui.button>
    </x-slot:actions>

    @if ($errors->any())
        <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
            <div class="p-1.5 rounded-full bg-rose-100 shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i></div>
            <div>
                <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
                <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="flex items-start gap-2.5 px-4 py-3 mb-6 rounded-xl bg-amber-50 border border-amber-200/80">
        <i data-lucide="lock" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
        <p class="text-[11px] text-amber-800 leading-relaxed">
            Mengubah tarif di sini <strong>tidak</strong> mengubah dokumen yang sudah tersimpan.
            Persentase dibekukan saat bulan lembur dikunci dan saat data penagihan disimpan, sehingga
            nominal yang sudah dibayarkan tidak bergeser. Tarif baru berlaku untuk yang berikutnya.
        </p>
    </div>

    <div class="space-y-6">
        @foreach($jenisOptions as $kode => $label)
            @php $baris = $tarif[$kode] ?? collect(); @endphp
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-slate-900">{{ $label }}</h3>
                        <p class="text-[11px] text-slate-400">
                            @if(in_array($kode, $berkunci))
                                Bertingkat — tiap baris punya {{ $kode === \App\Models\TarifPajak::PPH21 ? 'golongan' : 'kualifikasi' }} sendiri
                            @else
                                Berlaku menyeluruh
                            @endif
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/60 border-b border-slate-100">
                            <tr>
                                @if(in_array($kode, $berkunci))
                                    <th class="px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        {{ $kode === \App\Models\TarifPajak::PPH21 ? 'Golongan' : 'Kualifikasi' }}
                                    </th>
                                @endif
                                <th class="px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right w-24">Tarif</th>
                                <th class="px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center w-24">Dasar</th>
                                <th class="px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Keterangan</th>
                                <th class="px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($baris as $item)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $item->aktif ? '' : 'opacity-50' }}">
                                    @if(in_array($kode, $berkunci))
                                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $item->kunci ?: '—' }}</td>
                                    @endif
                                    <td class="px-5 py-3 text-right font-bold tabular-nums {{ (float) $item->persen > 0 ? 'text-slate-800' : 'text-emerald-600' }}">
                                        {{ $persen($item->persen) }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $item->dasar === \App\Models\TarifPajak::DASAR_DPP ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $item->dasar === \App\Models\TarifPajak::DASAR_DPP ? 'DPP' : 'Bruto' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 text-xs">
                                        {{ $item->keterangan ?: '—' }}
                                        @unless($item->aktif)
                                            <span class="ml-1 text-[10px] font-bold text-slate-400">(nonaktif)</span>
                                        @endunless
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" title="Edit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                                x-on:click="openEdit({ jenis: @js($item->jenis), kunci: @js($item->kunci), persen: {{ (float) $item->persen }}, dasar: @js($item->dasar), keterangan: @js($item->keterangan), aktif: {{ $item->aktif ? 'true' : 'false' }}, action: '{{ route('admin.pajak.update', $item) }}' })">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form action="{{ route('admin.pajak.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('Hapus tarif ini? Yang tidak punya tarif akan terhitung 0%.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Hapus"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-xs text-slate-400">
                                        Belum ada tarif. Selama kosong, jenis ini terhitung 0%.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>

    <div class="mt-6 space-y-1.5 text-[11px] text-slate-400 leading-relaxed max-w-3xl">
        <p>
            <span class="font-semibold text-slate-500">Dasar DPP</span> berarti tarif dihitung dari
            nilai kontrak setelah pajak konsumsinya dikeluarkan;
            <span class="font-semibold text-slate-500">Bruto</span> berarti langsung dari nilainya.
            Nilai kontrak selalu dianggap sudah termasuk pajak, jadi pembagi DPP diturunkan dari
            tarif ini juga — mengubah PPN otomatis mengubah pembaginya.
        </p>
        <p>
            Golongan dicocokkan sebagai kata utuh, bukan potongan kata — jadi
            <span class="font-semibold text-slate-500">Golongan VIII</span> tidak akan terbaca
            sebagai Golongan III. Golongan kosong atau di luar I–IV diperlakukan sebagai
            <span class="font-semibold text-slate-500">P3K</span>.
        </p>
    </div>
</x-ui.workspace>

    {{-- Modal Tambah / Edit --}}
    <div x-show="open" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="close()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" x-transition.scale.origin.center>
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900" x-text="mode === 'add' ? 'Tambah Tarif Pajak' : 'Edit Tarif Pajak'"></h3>
                <button type="button" x-on:click="close()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="form.action" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'add' ? 'POST' : 'PUT'">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Pajak</label>
                        <x-ui.select name="jenis" x-model="form.jenis" required>
                            @foreach($jenisOptions as $kode => $label)
                                <option value="{{ $kode }}">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Kunci hanya berarti pada jenis bertingkat, jadi ia menghilang
                         sendiri pada PPN dan PPh 22/23 yang berlaku menyeluruh. --}}
                    <div x-show="perluKunci()" x-cloak>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5" x-text="form.jenis === 'pph21' ? 'Golongan' : 'Kualifikasi'"></label>
                        <x-ui.input type="text" name="kunci" x-model="form.kunci" placeholder="Contoh: Golongan III atau Kualifikasi Kecil" />
                        <p class="text-[11px] text-slate-400 mt-1.5" x-show="form.jenis === 'pph21'">
                            Boleh menyebut lebih dari satu, misalnya <span class="font-semibold">Golongan I dan Golongan II</span>.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tarif (%)</label>
                            <x-ui.input type="number" name="persen" x-model="form.persen" min="0" max="100" step="0.01" placeholder="Contoh: 11" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Dasar</label>
                            <x-ui.select name="dasar" x-model="form.dasar" required>
                                <option value="dpp">DPP</option>
                                <option value="bruto">Bruto</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan</label>
                        <x-ui.input type="text" name="keterangan" x-model="form.keterangan" placeholder="Opsional" />
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="aktif" value="0">
                        <input type="checkbox" name="aktif" value="1" x-model="form.aktif"
                            class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <span class="text-sm font-semibold text-slate-600">Tarif aktif</span>
                    </label>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex items-center justify-end gap-3">
                    <x-ui.button variant="secondary" size="md" type="button" x-on:click="close()">Batal</x-ui.button>
                    <x-ui.button variant="primary" size="md" type="submit">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function pajakPage() {
        const kosong = { action: '', jenis: 'ppn', kunci: '', persen: '', dasar: 'dpp', keterangan: '', aktif: true };

        return {
            open: false,
            mode: 'add',
            form: { ...kosong },
            perluKunci() {
                return this.form.jenis === 'pph21' || this.form.jenis === 'pph4_2_konstruksi';
            },
            openAdd() {
                this.mode = 'add';
                this.form = { ...kosong, action: '{{ route('admin.pajak.store') }}' };
                this.open = true;
            },
            openEdit(data) {
                this.mode = 'edit';
                this.form = { ...data, kunci: data.kunci ?? '', keterangan: data.keterangan ?? '' };
                this.open = true;
            },
            close() { this.open = false; },
        };
    }
</script>
@endcomponent
