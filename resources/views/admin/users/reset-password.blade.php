@component('layouts.kdmp')
@section('title', 'Reset Password')

<x-ui.toast />

<x-ui.workspace title="Reset Password" description="Atur ulang password untuk akun pengguna ini.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.users.show', $user) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <div class="max-w-2xl">
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

        {{-- Info user target --}}
        <div class="flex items-center gap-3 p-4 mb-6 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg shrink-0 border border-emerald-100">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-900 leading-snug">{{ $user->name }}</p>
                <p class="text-xs text-slate-500 flex items-center gap-1"><i data-lucide="mail" class="w-3 h-3"></i> {{ $user->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update-password', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Password Baru</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <x-ui.input type="password" name="password" id="password" :invalid="$errors->has('password')"
                            placeholder="Minimal 8 karakter" required />
                        @error('password') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Konfirmasi Password <span class="text-rose-500">*</span>
                        </label>
                        <x-ui.input type="password" name="password_confirmation" id="password_confirmation"
                            placeholder="Ulangi password baru" required />
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <x-ui.button variant="secondary" size="md" href="{{ route('admin.users.show', $user) }}">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
                </x-ui.button>
                <x-ui.button variant="primary" size="lg" type="submit">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Password
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.workspace>
@endcomponent
