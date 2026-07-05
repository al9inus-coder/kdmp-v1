@php
    $initialItems = ($procurementPackage->technicalSpecification?->items ?? collect())->map(fn($item) => [
        'nama_barang_jasa' => $item->nama_barang_jasa,
        'spesifikasi'      => $item->spesifikasi,
        'volume'           => $item->volume !== null ? (float) $item->volume : null,
        'satuan'           => $item->satuan,
        'harga_satuan_dpa' => $item->harga_satuan_dpa !== null ? (float) $item->harga_satuan_dpa : null,
        'pdn'              => (bool) $item->pdn,
        'tkdn'             => $item->tkdn !== null ? (float) $item->tkdn : null,
        'kode_mak'         => $item->kode_mak,
    ])->values();
@endphp

<div x-data="kabidBarangJasa(@js($initialItems), {{ (float) ($procurementPackage->package->pagu ?? 0) }})">
    <form method="POST" id="form-barang-jasa" action="{{ route('kabid.procurement-packages.items.update', $procurementPackage->package) }}">
        @csrf
        @method('PUT')

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                <span x-text="items.length"></span> item
            </span>
            <button type="button" @click="addItem()"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Item
            </button>
        </div>

        {{-- Tabel item --}}
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-[11px] text-slate-500 bg-slate-50 border-b border-slate-200 uppercase font-semibold">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">No</th>
                            <th class="px-3 py-3 min-w-[180px]">Nama Barang/Jasa</th>
                            <th class="px-3 py-3 min-w-[200px]">Spesifikasi</th>
                            <th class="px-3 py-3 text-center w-20">Volume</th>
                            <th class="px-3 py-3 text-center w-24">Satuan</th>
                            <th class="px-3 py-3 text-right w-36">Harga DPA</th>
                            <th class="px-3 py-3 text-right w-36">Subtotal</th>
                            <th class="px-3 py-3 text-center w-14">PDN</th>
                            <th class="px-3 py-3 text-center w-20">TKDN %</th>
                            <th class="px-3 py-3 min-w-[130px]">Kode MAK</th>
                            <th class="px-3 py-3 text-center w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(item, i) in items" :key="i">
                            <tr class="hover:bg-slate-50/50 align-top">
                                <td class="px-3 py-2.5 text-center text-slate-400 font-semibold pt-4" x-text="i + 1"></td>
                                <td class="px-3 py-2.5">
                                    <input type="text" :name="`items[${i}][nama_barang_jasa]`" x-model="item.nama_barang_jasa"
                                        placeholder="Nama barang/jasa"
                                        class="w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5">
                                    <textarea :name="`items[${i}][spesifikasi]`" x-model="item.spesifikasi" rows="1"
                                        placeholder="Spesifikasi"
                                        class="w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" min="0" step="any" :name="`items[${i}][volume]`" x-model.number="item.volume"
                                        class="w-20 text-center rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="text" :name="`items[${i}][satuan]`" x-model="item.satuan" placeholder="Unit"
                                        class="w-24 text-center rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" min="0" step="any" :name="`items[${i}][harga_satuan_dpa]`" x-model.number="item.harga_satuan_dpa"
                                        class="w-36 text-right rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5 text-right pt-4">
                                    <span class="font-semibold text-slate-700" x-text="formatRp(subtotal(item))"></span>
                                </td>
                                <td class="px-3 py-2.5 text-center pt-4">
                                    <input type="checkbox" value="1" :name="`items[${i}][pdn]`" x-model="item.pdn"
                                        class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" min="0" max="100" step="0.01" :name="`items[${i}][tkdn]`" x-model.number="item.tkdn"
                                        class="w-20 text-center rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="text" :name="`items[${i}][kode_mak]`" x-model="item.kode_mak"
                                        class="w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </td>
                                <td class="px-3 py-2.5 text-center pt-3.5">
                                    <button type="button" @click="removeItem(i)"
                                        class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-md transition-colors" title="Hapus item">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        {{-- Empty state --}}
                        <tr x-show="items.length === 0">
                            <td colspan="11" class="px-4 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <i data-lucide="package-open" class="w-6 h-6 text-slate-400"></i>
                                    </div>
                                    <p class="font-medium">Belum ada barang/jasa.</p>
                                    <p class="text-xs mt-1">Klik "Tambah Item" untuk mulai mengisi rincian.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan anggaran --}}
            <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
                    <div class="flex-1">
                        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                            <span class="text-slate-500 uppercase tracking-wide">Total Estimasi vs Pagu</span>
                            <span :class="overBudget ? 'text-rose-600' : 'text-emerald-600'" x-text="percentPagu + '%'"></span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                :class="overBudget ? 'bg-gradient-to-r from-rose-400 to-rose-600' : 'bg-gradient-to-r from-emerald-400 to-emerald-500'"
                                :style="`width: ${Math.min(percentPagu, 100)}%`"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Estimasi</p>
                            <p class="font-bold" :class="overBudget ? 'text-rose-600' : 'text-slate-800'" x-text="formatRp(total)"></p>
                        </div>
                        <div class="text-right border-l border-slate-200 pl-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pagu Anggaran</p>
                            <p class="font-bold text-emerald-600" x-text="formatRp(pagu)"></p>
                        </div>
                    </div>
                </div>
                <p x-show="overBudget" x-transition.opacity class="mt-2 text-xs font-semibold text-rose-600 flex items-center gap-1.5" style="display: none;">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                    Total estimasi melebihi pagu anggaran — periksa kembali volume atau harga satuan.
                </p>
            </div>
        </div>

    </form>
</div>

<script>
    function kabidBarangJasa(initialItems, pagu) {
        return {
            items: initialItems,
            pagu: pagu,
            addItem() {
                this.items.push({
                    nama_barang_jasa: '', spesifikasi: '', volume: null, satuan: '',
                    harga_satuan_dpa: null, pdn: false, tkdn: null, kode_mak: '',
                });
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            },
            removeItem(i) {
                this.items.splice(i, 1);
            },
            subtotal(item) {
                return (Number(item.volume) || 0) * (Number(item.harga_satuan_dpa) || 0);
            },
            get total() {
                return this.items.reduce((sum, item) => sum + this.subtotal(item), 0);
            },
            get percentPagu() {
                return this.pagu > 0 ? Math.round(this.total / this.pagu * 100) : 0;
            },
            get overBudget() {
                return this.total > this.pagu && this.pagu > 0;
            },
            formatRp(n) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n || 0);
            },
        };
    }
</script>
