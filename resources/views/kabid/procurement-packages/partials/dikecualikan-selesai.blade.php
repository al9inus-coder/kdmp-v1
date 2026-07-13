@if($locked)
    <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl">
            <i data-lucide="check-circle-2" class="w-4 h-4"></i>Paket Selesai &amp; Terkunci
        </span>
        @if(auth()->user()->hasRole('Admin'))
            <form action="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.unlock', $procurementPackage->package) }}" method="POST"
                onsubmit="return confirm('Buka kunci paket ini? Data akan kembali ke status draf.');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm shadow-rose-200 transition-colors">
                    <i data-lucide="unlock" class="w-4 h-4"></i>Buka Kunci Paket
                </button>
            </form>
        @endif
    </div>
@else
    <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.complete', $procurementPackage) }}" method="POST"
        onsubmit="return confirm('Selesaikan paket ini? Data akan dikunci dan tidak dapat diubah lagi.');">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors">
            <i data-lucide="check-check" class="w-4 h-4"></i>Selesaikan Paket
        </button>
    </form>
@endif
