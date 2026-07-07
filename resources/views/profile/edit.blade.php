@component('layouts.kdmp')
@section('title', 'Profil Saya')

<x-ui.toast />

@php
    $roleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();
@endphp

<x-ui.workspace title="Profil Saya" description="Kelola informasi akun dan ganti password Anda.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('dashboard') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kartu ringkas akun --}}
        <x-ui.card variant="flat" padding="lg" class="lg:col-span-1 h-fit">
            <div class="flex flex-col items-center text-center">
                <img class="w-24 h-24 rounded-full border border-slate-200 object-cover shadow-sm"
                     src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=2563EB&background=DBEAFE&size=128" alt="Foto Profil">
                <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>

                <div class="flex flex-wrap justify-center gap-1.5 mt-3">
                    @forelse($roleNames as $role)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i data-lucide="shield-check" class="w-3 h-3"></i> {{ $role }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">Tanpa peran</span>
                    @endforelse
                </div>

                @if(!is_null($user->last_login_at))
                    <p class="mt-4 text-[11px] text-slate-400">
                        Login terakhir: {{ $user->last_login_at->translatedFormat('d M Y, H:i') }}
                    </p>
                @endif
            </div>
        </x-ui.card>

        {{-- Kolom kanan: form-form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Akun --}}
            <x-ui.card variant="flat" padding="lg">
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800">Informasi Akun</h3>
                        <p class="text-xs text-slate-400">Perbarui nama dan alamat email Anda.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap</label>
                        <x-ui.input id="name" name="name" type="text" :value="old('name', $user->name)" :invalid="$errors->has('name')" required autocomplete="name" />
                        @error('name')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                        <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email)" :invalid="$errors->has('email')" required autocomplete="email" />
                        @error('email')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end pt-1">
                        <x-ui.button type="submit" variant="primary" size="md">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Perubahan
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Ganti Password --}}
            <x-ui.card variant="flat" padding="lg">
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800">Ganti Password</h3>
                        <p class="text-xs text-slate-400">Pastikan memakai password yang panjang dan acak agar aman.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-600 mb-1.5">Password Saat Ini</label>
                        <x-ui.input id="current_password" name="current_password" type="password" :invalid="$errors->updatePassword->has('current_password')" autocomplete="current-password" />
                        @error('current_password', 'updatePassword')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-600 mb-1.5">Password Baru</label>
                            <x-ui.input id="password" name="password" type="password" :invalid="$errors->updatePassword->has('password')" autocomplete="new-password" />
                            @error('password', 'updatePassword')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-600 mb-1.5">Konfirmasi Password Baru</label>
                            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-1">
                        <x-ui.button type="submit" variant="primary" size="md">
                            <i data-lucide="shield-check" class="w-4 h-4 mr-2"></i> Ganti Password
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-ui.workspace>
@endcomponent
