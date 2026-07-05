@if($locked)
    <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>Paket Selesai &amp; Terkunci
    </span>
@else
    <form action="{{ route('procurement-packages.complete', $procurementPackage) }}" method="POST"
        onsubmit="return confirm('Selesaikan paket ini? Data akan dikunci dan tidak dapat diubah lagi.');">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors">
            <i data-lucide="check-check" class="w-4 h-4"></i>Selesaikan Paket
        </button>
    </form>
@endif
