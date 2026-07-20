@php
    $isEdit = $user->exists;
    $isSuper = $isEdit && $user->hasRole('Super Admin');
    $currentRole = old('role', $user->roles->first()?->name);
    $statusVal = (int) old('is_active', $user->is_active ?? 1);
    $val = fn($field, $default = null) => old($field, $user->{$field} ?? $default);
    $hasError = fn($field) => $errors->has($field);
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
        <div class="p-1.5 rounded-full bg-rose-100 shrink-0">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
        </div>
        <div>
            <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
            <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="max-w-2xl">
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Informasi Akun</h3>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Nama <span class="text-rose-500">*</span>
                </label>
                <x-ui.input type="text" name="name" id="name" maxlength="255" :value="$val('name')"
                    :invalid="$hasError('name')" placeholder="Nama lengkap pengguna" required />
                @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Email <span class="text-rose-500">*</span>
                </label>
                <x-ui.input type="email" name="email" id="email" maxlength="255" :value="$val('email')"
                    :invalid="$hasError('email')" placeholder="nama@kdmp.local" required />
                @error('email') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            @unless($isEdit)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <x-ui.input type="password" name="password" id="password" :invalid="$hasError('password')"
                            placeholder="Minimal 8 karakter" required />
                        @error('password') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Konfirmasi Password <span class="text-rose-500">*</span>
                        </label>
                        <x-ui.input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="Ulangi password" required />
                    </div>
                </div>
            @endunless

            {{-- Role --}}
            <div>
                <label for="role" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Role <span class="text-rose-500">*</span>
                </label>
                @if($isSuper)
                    <div class="flex items-center gap-2 px-3 py-2.5 rounded-md border border-slate-200 bg-slate-50 text-sm text-slate-600">
                        <i data-lucide="shield-check" class="w-4 h-4 text-rose-500"></i>
                        <span class="font-semibold">Super Admin</span>
                        <span class="text-xs text-slate-400">— role terlindungi, tidak dapat diubah</span>
                    </div>
                @else
                    <x-ui.select name="role" id="role" :invalid="$hasError('role')" required>
                        <option value="">Pilih Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected($currentRole === $r)>{{ $r }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('role') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                @endif
            </div>

            {{-- Status --}}
            <div x-data="{ status: {{ $isSuper ? 1 : $statusVal }} }">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                <input type="hidden" name="is_active" :value="status">
                @if($isSuper)
                    <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Aktif <span class="text-xs font-normal text-slate-400">(terkunci)</span>
                    </div>
                @else
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                        <button type="button" @click="status = 1"
                            :class="status === 1 ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-emerald-100' : 'text-slate-500 hover:text-slate-700'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-semibold transition-all">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i> Aktif
                        </button>
                        <button type="button" @click="status = 0"
                            :class="status === 0 ? 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-semibold transition-all">
                            <i data-lucide="circle-slash" class="w-4 h-4"></i> Nonaktif
                        </button>
                    </div>
                @endif
                @error('is_active') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('admin.users.index') }}">
            <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>
