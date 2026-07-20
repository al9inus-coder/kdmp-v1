@component('layouts.kdmp')
@section('title', 'Riwayat Koreksi')

<x-ui.toast />

<div class="max-w-4xl mx-auto">
    {{-- Header objek (glass) --}}
    <x-ui.card variant="glass" class="mb-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-12 h-12 rounded-2xl {{ $def['iconBg'] }} flex items-center justify-center shrink-0">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $def['chip'] }}">
                        {{ $def['label'] }}
                    </span>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-1.5 leading-snug">Riwayat Koreksi — {{ $title }}</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }} &middot; Status: {{ $statusLabel }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-ui.button size="sm" href="{{ route('admin.data-corrections.edit', [$type, $object->getKey()]) }}">
                    <i data-lucide="file-pen-line" class="w-4 h-4 mr-1.5"></i> Koreksi
                </x-ui.button>
                <x-ui.button variant="ghost" size="sm" href="{{ route('admin.data-corrections.index') }}">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i> Kembali
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    {{-- Timeline --}}
    <x-ui.card>
        @if($corrections->isEmpty())
            <x-ui.empty-state icon="file-clock" title="Belum Ada Koreksi"
                description="Objek ini belum pernah dikoreksi. Seluruh koreksi yang dilakukan akan tercatat permanen di sini." />
        @else
            <div class="relative pl-6">
                {{-- Garis vertikal timeline --}}
                <div class="absolute left-[7px] top-2 bottom-2 w-px bg-slate-200"></div>

                <div class="space-y-6">
                    @foreach($corrections as $c)
                        <div class="relative">
                            <div class="absolute -left-6 top-1.5 w-[15px] h-[15px] rounded-full bg-emerald-500 border-[3px] border-white ring-1 ring-emerald-200"></div>

                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                {{ $c->created_at->translatedFormat('d F Y, H:i') }} WIB
                            </p>

                            <div class="mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        <i data-lucide="pencil-line" class="w-3 h-3"></i> {{ $c->field_label }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                                        <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                                        {{ $c->user?->name ?? 'User terhapus' }}
                                    </span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mt-3 text-sm">
                                    <span class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-800 line-through decoration-rose-300 break-words">{{ $c->old_display }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 shrink-0 hidden sm:block"></i>
                                    <i data-lucide="arrow-down" class="w-4 h-4 text-slate-400 shrink-0 sm:hidden"></i>
                                    <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 font-medium break-words">{{ $c->new_display }}</span>
                                </div>

                                <p class="flex items-start gap-2 mt-3 text-xs text-slate-500 leading-relaxed">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 mt-0.5 text-slate-400 shrink-0"></i>
                                    <span class="italic">"{{ $c->reason }}"</span>
                                </p>

                                @if($c->attachment_path)
                                    <a href="{{ route('admin.data-corrections.attachment', [$type, $object->getKey(), $c]) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 mt-3 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                        <i data-lucide="paperclip" class="w-3 h-3"></i> Lihat Lampiran
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Titik awal riwayat --}}
                    <div class="relative">
                        <div class="absolute -left-6 top-0.5 w-[15px] h-[15px] rounded-full bg-white border-[3px] border-slate-200"></div>
                        <p class="text-xs text-slate-400 italic">Awal riwayat koreksi.</p>
                    </div>
                </div>
            </div>

            @if($corrections->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100">
                    {{ $corrections->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endcomponent
