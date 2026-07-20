@component('layouts.kdmp')
@section('title', 'Master SBU Lembur')

<x-ui.toast />

<div x-data="lemburPage()">
<x-ui.workspace title="Master SBU Lembur" description="Standar biaya uang lembur dan uang makan lembur.">
    <x-slot:actions>
        <x-ui.button variant="primary" size="md" type="button" x-on:click="openAdd()">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Data
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Uang Lembur --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center"><i data-lucide="clock" class="w-4 h-4"></i></div>
                <h3 class="text-sm font-bold text-slate-900">1.24.1 &middot; Uang Lembur</h3>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Golongan / Kategori</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Besaran</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uangLemburs as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $item->golongan }}</td>
                            <td class="px-5 py-3.5 text-center text-slate-600">{{ $item->satuan }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($item->besaran, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                        x-on:click="openEdit({ jenis: 'Uang Lembur', golongan: @js($item->golongan), satuan: @js($item->satuan), besaran: {{ $item->besaran }}, action: '{{ route('admin.sbu-lemburs.update', $item) }}' })">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form action="{{ route('admin.sbu-lemburs.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{-- Uang Makan Lembur --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-sky-50/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center"><i data-lucide="utensils" class="w-4 h-4"></i></div>
                <h3 class="text-sm font-bold text-slate-900">1.24.2 &middot; Uang Makan Lembur</h3>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Golongan / Kategori</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Besaran</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($uangMakanLemburs as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $item->golongan }}</td>
                            <td class="px-5 py-3.5 text-center text-slate-600">{{ $item->satuan }}</td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($item->besaran, 0, ',', '.') }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                        x-on:click="openEdit({ jenis: 'Uang Makan Lembur', golongan: @js($item->golongan), satuan: @js($item->satuan), besaran: {{ $item->besaran }}, action: '{{ route('admin.sbu-lemburs.update', $item) }}' })">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form action="{{ route('admin.sbu-lemburs.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-ui.workspace>

    {{-- Modal Tambah / Edit --}}
    <div x-show="open" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="close()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" x-transition.scale.origin.center>
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900" x-text="mode === 'add' ? 'Tambah SBU Lembur' : 'Edit Biaya Lembur'"></h3>
                <button type="button" x-on:click="close()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="form.action" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'add' ? 'POST' : 'PUT'">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis</label>
                        <x-ui.select name="jenis" x-model="form.jenis" required>
                            <option value="Uang Lembur">Uang Lembur</option>
                            <option value="Uang Makan Lembur">Uang Makan Lembur</option>
                        </x-ui.select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori / Golongan</label>
                        <x-ui.input type="text" name="golongan" x-model="form.golongan" placeholder="Contoh: Golongan IV" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Satuan (Misal: OJ, OH)</label>
                        <x-ui.input type="text" name="satuan" x-model="form.satuan" placeholder="Contoh: OJ atau OH" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Besaran (Rp)</label>
                        <x-ui.input type="number" name="besaran" x-model="form.besaran" min="0" placeholder="Contoh: 36000" required />
                    </div>
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
    function lemburPage() {
        return {
            open: false,
            mode: 'add',
            form: { action: '', jenis: 'Uang Lembur', golongan: '', satuan: '', besaran: '' },
            openAdd() {
                this.mode = 'add';
                this.form = { action: '{{ route('admin.sbu-lemburs.store') }}', jenis: 'Uang Lembur', golongan: '', satuan: '', besaran: '' };
                this.open = true;
            },
            openEdit(data) {
                this.mode = 'edit';
                this.form = { ...data };
                this.open = true;
            },
            close() { this.open = false; },
        };
    }
</script>
@endcomponent
