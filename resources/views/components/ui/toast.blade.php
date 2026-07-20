{{-- Toast notifikasi flash session (success / error), auto-dismiss 4 detik --}}
@php
    $isSuccess = session()->has('success');
    $message   = session('success') ?? session('error');
@endphp

@if($message)
    <div id="app-toast" class="fixed top-20 right-4 sm:right-6 z-50 w-[calc(100%-2rem)] sm:w-auto sm:max-w-sm">
        <div class="relative flex items-start gap-3 p-4 bg-white rounded-2xl shadow-xl border {{ $isSuccess ? 'border-emerald-200' : 'border-rose-200' }} overflow-hidden"
            style="animation: toastIn .35s cubic-bezier(.21,1.02,.73,1) both;">
            <div class="p-2 rounded-full shrink-0 {{ $isSuccess ? 'bg-emerald-100' : 'bg-rose-100' }}">
                <i data-lucide="{{ $isSuccess ? 'check-circle' : 'alert-circle' }}" class="w-5 h-5 {{ $isSuccess ? 'text-emerald-600' : 'text-rose-600' }}"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-bold text-sm {{ $isSuccess ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ $isSuccess ? 'Berhasil!' : 'Gagal!' }}
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ $message }}</p>
            </div>
            <button type="button" onclick="dismissAppToast()" class="text-slate-400 hover:text-slate-600 shrink-0 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            <div class="absolute bottom-0 left-0 h-0.5 {{ $isSuccess ? 'bg-emerald-500' : 'bg-rose-500' }}"
                style="animation: toastBar 4s linear forwards;"></div>
        </div>
    </div>

    <style>
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(1.5rem); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes toastBar {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>

    <script>
        function dismissAppToast() {
            const toast = document.getElementById('app-toast');
            if (!toast) return;
            toast.style.transition = 'opacity .4s, transform .4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(1.5rem)';
            setTimeout(() => toast.remove(), 400);
        }
        setTimeout(dismissAppToast, 4000);
    </script>
@endif
