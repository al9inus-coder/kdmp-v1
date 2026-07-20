@component('layouts.kdmp')
@section('title', 'Form Koreksi Data')

<x-ui.toast />

<div class="max-w-4xl mx-auto"
    x-effect="selected; $nextTick(() => window.lucide && lucide.createIcons())"
    x-data="{
        fields: @js($fields),
        selected: @js(old('field_key')),
        newValue: @js(old('new_value', '')),
        reason: @js(old('reason', '')),
        confirmOpen: false,
        get f() { return this.selected ? (this.fields[this.selected] ?? null) : null },
        pick(key) {
            this.selected = key;
            this.newValue = this.fields[key].old ?? '';
        },
        get changed() { return this.f && String(this.newValue ?? '').trim() !== String(this.f.old ?? '') },
        get reasonOk() { return this.reason.trim().length >= 20 },
        get canSubmit() { return this.f && String(this.newValue ?? '').trim() !== '' && this.changed && this.reasonOk },
    }">

    {{-- Header objek: satu-satunya area glass --}}
    <x-ui.card variant="glass" class="mb-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl {{ $def['iconBg'] }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $def['icon'] }}" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $def['chip'] }}">
                        {{ $def['label'] }}
                    </span>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-1.5 leading-snug">{{ $title }}</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @if($historyCount > 0)
                    <x-ui.button variant="secondary" size="sm" href="{{ route('admin.data-corrections.history', [$type, $object->getKey()]) }}">
                        <i data-lucide="history" class="w-4 h-4 mr-1.5"></i> Riwayat ({{ $historyCount }})
                    </x-ui.button>
                @endif
                <x-ui.button variant="ghost" size="sm" href="{{ route('admin.data-corrections.index') }}">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Kembali
                </x-ui.button>
            </div>
        </div>

        {{-- Kartu status read-only --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="bg-white/70 rounded-xl border border-slate-200/60 p-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="git-branch" class="w-3.5 h-3.5"></i> Status Workflow
                </p>
                <p class="text-sm font-bold text-slate-800 mt-1.5 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i> {{ $statusLabel }}
                </p>
            </div>
            <div class="bg-white/70 rounded-xl border border-slate-200/60 p-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Status Approval
                </p>
                @if($approval)
                    <p class="text-sm font-bold text-emerald-700 mt-1.5">
                        {{ $approval['label'] }}
                        <span class="font-medium text-slate-500">&middot; {{ $approval['date']->translatedFormat('d M Y') }}</span>
                    </p>
                @else
                    <p class="text-sm font-medium text-slate-400 mt-1.5">&mdash;</p>
                @endif
            </div>
            <div class="bg-white/70 rounded-xl border border-slate-200/60 p-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Koreksi Terakhir
                </p>
                @if($lastCorrection)
                    <p class="text-sm font-bold text-slate-800 mt-1.5">
                        {{ $lastCorrection->created_at->translatedFormat('d M Y') }}
                        <span class="font-medium text-slate-500">&middot; {{ $lastCorrection->user?->name ?? '-' }}</span>
                    </p>
                @else
                    <p class="text-sm font-medium text-slate-400 mt-1.5">Belum pernah dikoreksi</p>
                @endif
            </div>
        </div>

        <div class="flex items-start gap-2.5 mt-4 px-4 py-3 rounded-xl bg-blue-50/80 border border-blue-100 text-blue-800">
            <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <p class="text-xs leading-relaxed">
                Koreksi hanya mengubah isi field yang dipilih. <span class="font-semibold">Status workflow, approval, dan riwayat audit tidak akan berubah.</span>
                Seluruh koreksi tercatat permanen di riwayat.
            </p>
        </div>
    </x-ui.card>

    {{-- Pilih field --}}
    <x-ui.card class="mb-6">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="pencil-line" class="w-4 h-4 text-slate-400"></i> Field yang Dapat Dikoreksi
        </h2>
        <div class="flex flex-wrap gap-2 mt-4">
            @foreach($fields as $key => $field)
                <button type="button" @click="pick('{{ $key }}')"
                    :class="selected === '{{ $key }}'
                        ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm'
                        : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:text-emerald-700'"
                    class="px-3.5 py-2 rounded-xl border text-sm font-medium transition-all">
                    {{ $field['label'] }}
                </button>
            @endforeach
        </div>
        @error('field_key')
            <p class="text-xs text-rose-600 mt-3 flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</p>
        @enderror
    </x-ui.card>

    {{-- Form koreksi --}}
    <form x-ref="form" method="POST" enctype="multipart/form-data"
        action="{{ route('admin.data-corrections.update', [$type, $object->getKey()]) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="field_key" :value="selected">
        <input type="hidden" name="expected_old" :value="f ? f.old : ''">

        <template x-if="!selected">
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                <i data-lucide="mouse-pointer-click" class="w-8 h-8 text-slate-300 mx-auto"></i>
                <p class="text-sm text-slate-400 mt-3 font-medium">Pilih field di atas untuk memulai koreksi.</p>
            </div>
        </template>

        <div x-show="selected" x-cloak>
            <x-ui.card>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="file-pen-line" class="w-4 h-4 text-slate-400"></i>
                    Form Koreksi: <span class="text-emerald-600 normal-case" x-text="f ? f.label : ''"></span>
                </h2>

                <div class="space-y-5 mt-6">
                    {{-- Nilai lama (read-only) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Nilai Lama <span class="font-medium normal-case text-slate-400">(otomatis, tidak dapat diubah)</span>
                        </label>
                        <div class="flex items-start gap-2.5 px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700">
                            <i data-lucide="lock" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span class="whitespace-pre-wrap break-words" x-text="f ? f.oldDisplay : ''"></span>
                        </div>
                    </div>

                    {{-- Nilai baru --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Nilai Baru <span class="text-rose-500">*</span>
                        </label>
                        <template x-if="f && f.type === 'date'">
                            <input type="date" name="new_value" x-model="newValue"
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </template>
                        <template x-if="f && f.type === 'textarea'">
                            <textarea name="new_value" x-model="newValue" rows="3"
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </template>
                        <template x-if="f && f.type === 'text'">
                            <input type="text" name="new_value" x-model="newValue"
                                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </template>

                        <p x-show="f && !changed && String(newValue ?? '').trim() !== ''" x-cloak
                            class="text-xs text-amber-600 mt-1.5 flex items-center gap-1.5">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Nilai baru masih identik dengan nilai lama.
                        </p>
                        @error('new_value')
                            <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Alasan Koreksi <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-medium" :class="reasonOk ? 'text-emerald-600' : 'text-slate-400'"
                                x-text="reason.trim().length + ' / min. 20 karakter'"></span>
                        </div>
                        <textarea name="reason" x-model="reason" rows="3"
                            placeholder="Contoh: ID RUP berubah setelah revisi kaji ulang di SIRUP tanggal ..."
                            class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        @error('reason')
                            <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lampiran --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Lampiran <span class="font-medium normal-case text-slate-400">(opsional — bukti pendukung, PDF/JPG/PNG maks. 5 MB)</span>
                        </label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-slate-500 border border-dashed border-slate-300 rounded-xl px-4 py-3
                                file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        @error('attachment')
                            <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                    <x-ui.button variant="ghost" href="{{ route('admin.data-corrections.index') }}">Batal</x-ui.button>
                    <x-ui.button type="button" x-bind:disabled="!canSubmit" @click="confirmOpen = true">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Koreksi
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>

        {{-- Dialog Konfirmasi (Halaman 3) --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="confirmOpen = false"></div>

            <div x-show="confirmOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6"
                @keydown.escape.window="confirmOpen = false">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Konfirmasi Koreksi Data</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $def['label'] }} &middot; {{ Str::limit($title, 60) }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-200">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Field: <span class="text-slate-700" x-text="f ? f.label : ''"></span></p>
                    </div>
                    <div class="px-4 py-3 bg-rose-50/60">
                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-wider mb-1">Nilai Lama</p>
                        <p class="text-sm text-rose-800 whitespace-pre-wrap break-words" x-text="f ? f.oldDisplay : ''"></p>
                    </div>
                    <div class="flex justify-center py-1.5 bg-white border-y border-slate-100">
                        <i data-lucide="arrow-down" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <div class="px-4 py-3 bg-emerald-50/60">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-1">Nilai Baru</p>
                        <p class="text-sm text-emerald-800 font-medium whitespace-pre-wrap break-words" x-text="newValue"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alasan</p>
                    <p class="text-sm text-slate-600 italic">"<span x-text="reason.trim()"></span>"</p>
                </div>

                <div class="flex items-start gap-2 mt-4 px-3 py-2.5 rounded-lg bg-slate-50 border border-slate-100">
                    <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 text-slate-400 shrink-0"></i>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Koreksi tercatat permanen di riwayat &amp; audit trail dan tidak dapat dihapus. Workflow tidak berubah.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <x-ui.button type="button" variant="secondary" @click="confirmOpen = false" x-ref="recheck">
                        Periksa Lagi
                    </x-ui.button>
                    <x-ui.button type="submit">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i> Konfirmasi
                    </x-ui.button>
                </div>
            </div>
        </div>
    </form>
</div>
@endcomponent
