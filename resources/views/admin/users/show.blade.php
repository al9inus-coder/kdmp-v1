@component('layouts.kdmp')
@section('title', 'Detail User')

<x-ui.toast />

@php
    $isSelf = auth()->id() === $user->id;
    $isSuper = $user->hasRole('Super Admin');
    $roleBadge = fn($name) => match ($name) {
        'Super Admin' => 'danger',
        'Admin' => 'info',
        'Kabid' => 'warning',
        'Staff' => 'draft',
        default => 'draft',
    };
@endphp

<x-ui.workspace title="Detail User" description="Informasi lengkap akun pengguna.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.users.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.users.edit', $user) }}">
            <i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit
        </x-ui.button>
    </x-slot:actions>

    <div class="max-w-3xl space-y-6">
        {{-- Identitas --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 flex items-center gap-4 border-b border-slate-100 bg-slate-50/60">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-2xl shrink-0 border border-emerald-100">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-slate-900 truncate">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500 flex items-center gap-1.5"><i data-lucide="mail" class="w-4 h-4"></i> {{ $user->email }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        @forelse($user->roles as $r)
                            <x-ui.badge :variant="$roleBadge($r->name)">{{ $r->name }}</x-ui.badge>
                        @empty
                            <span class="text-xs text-slate-400 italic">Tanpa role</span>
                        @endforelse
                        @if($user->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                <i data-lucide="circle-slash" class="w-3.5 h-3.5"></i> Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Dibuat</p>
                    <p class="text-sm font-semibold text-slate-700 flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i> {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Login Terakhir</p>
                    <p class="text-sm font-semibold text-slate-700 flex items-center gap-1.5"><i data-lucide="log-in" class="w-4 h-4 text-slate-400"></i> {{ $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah login' }}</p>
                </div>
            </div>
        </section>

        {{-- Aksi --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Aksi</h3>
            </div>
            <div class="p-6 flex flex-wrap items-center gap-3">
                <x-ui.button variant="secondary" size="md" href="{{ route('admin.users.reset-password', $user) }}">
                    <i data-lucide="key-round" class="w-4 h-4 mr-2"></i> Reset Password
                </x-ui.button>

                @if(!$isSelf && !$isSuper)
                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                        onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user {{ $user->name }}?');">
                        @csrf
                        @method('PATCH')
                        @if($user->is_active)
                            <x-ui.button variant="secondary" size="md" type="submit">
                                <i data-lucide="power-off" class="w-4 h-4 mr-2"></i> Nonaktifkan
                            </x-ui.button>
                        @else
                            <x-ui.button variant="success" size="md" type="submit">
                                <i data-lucide="power" class="w-4 h-4 mr-2"></i> Aktifkan
                            </x-ui.button>
                        @endif
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="danger" size="md" type="submit">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Hapus User
                        </x-ui.button>
                    </form>
                @else
                    <span class="inline-flex items-center gap-2 text-sm text-slate-400">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        {{ $isSelf ? 'Ini akun Anda sendiri — sebagian aksi dibatasi.' : 'Akun Super Admin terlindungi.' }}
                    </span>
                @endif
            </div>
        </section>
    </div>
</x-ui.workspace>
@endcomponent
