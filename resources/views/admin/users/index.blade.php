@component('layouts.kdmp')
@section('title', 'Manajemen User')

<x-ui.toast />

@php
    $roleBadge = fn($name) => match ($name) {
        'Super Admin' => 'danger',
        'Admin' => 'info',
        'Kabid' => 'warning',
        'Staff' => 'draft',
        default => 'draft',
    };
@endphp

<x-ui.workspace title="Manajemen User" description="Kelola akun pengguna, role, dan status akses aplikasi.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="users" class="w-4 h-4 text-emerald-500"></i>
            {{ $users->total() }} User
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.users.create') }}">
            <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> Tambah User
        </x-ui.button>
    </x-slot:actions>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="p-4 flex flex-col lg:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / email..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <select name="role" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all lg:w-48">
                <option value="">Semua Role</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}" @selected($role === $r)>{{ $r }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all lg:w-40">
                <option value="">Semua Status</option>
                <option value="1" @selected($status === '1')>Aktif</option>
                <option value="0" @selected($status === '0')>Nonaktif</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                </a>
            </div>
        </form>
    </x-ui.card>

    {{-- Tabel --}}
    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-12 text-center">No</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-32">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php $isSelf = auth()->id() === $user->id; $isSuper = $user->hasRole('Super Admin'); @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0 border border-emerald-100">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 leading-snug">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500 flex items-center gap-1"><i data-lucide="mail" class="w-3 h-3"></i> {{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @forelse($user->roles as $r)
                                    <x-ui.badge :variant="$roleBadge($r->name)">{{ $r->name }}</x-ui.badge>
                                @empty
                                    <span class="text-xs text-slate-400 italic">Tanpa role</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        <i data-lucide="circle-slash" class="w-3.5 h-3.5"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> {{ $user->created_at?->format('d M Y') ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('admin.users.reset-password', $user) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 bg-amber-50 border border-amber-100 hover:bg-amber-100 transition-colors" title="Reset Password">
                                        <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                                    </a>
                                    @if(!$isSelf && !$isSuper)
                                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST"
                                            onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user {{ $user->name }}?');">
                                            @csrf
                                            @method('PATCH')
                                            @if($user->is_active)
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors" title="Nonaktifkan">
                                                    <i data-lucide="power-off" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @else
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Aktifkan">
                                                    <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed" title="{{ $isSelf ? 'Akun Anda sendiri' : 'Super Admin terlindungi' }}">
                                            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10">
                                <x-ui.empty-state icon="users" title="Belum Ada User" description="Klik tombol Tambah User untuk menambahkan akun pengguna pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.users.create') }}">
                                        <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> Tambah User
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $users->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($users->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @foreach($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        @if($page == $users->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
