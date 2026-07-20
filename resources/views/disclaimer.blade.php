<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disclaimer | KDMP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        @keyframes wave-animation {
            0% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.05) rotate(0.5deg); }
            100% { transform: scale(1) rotate(0deg); }
        }

        .animate-flag {
            animation: wave-animation 12s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center relative p-4">

    {{-- Background Flag --}}
    <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
        <img src="{{ asset('/images/bendera.jpg') }}"
             class="w-full h-full object-cover opacity-55 animate-flag"
             alt="Indonesia Flag">
    </div>
    <div class="fixed inset-0 bg-slate-950/65 -z-10 pointer-events-none"></div>

    <div class="max-w-2xl w-full bg-slate-950/40 border border-slate-800/80 rounded-3xl p-6 sm:p-10 backdrop-blur-md shadow-2xl relative z-10 space-y-6">
        
        <div class="flex items-center gap-3 border-b border-slate-800/60 pb-5">
            <span class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center shadow-lg shadow-amber-950/30 shrink-0">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </span>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white uppercase">Disclaimer</h1>
                <p class="text-[11px] text-slate-400 font-semibold tracking-wide uppercase">Pernyataan Batas Tanggung Jawab</p>
            </div>
        </div>

        <div class="space-y-4 text-slate-300 text-sm leading-relaxed">
            <p>
                Tujuan pembuatan aplikasi <strong>KDMP (Kelola Digital Manajemen Pengadaan)</strong> ini <strong>bukan merupakan aplikasi resmi</strong> bentukan Pemerintah Kabupaten Bengkayang, khususnya Dinas Perumahan Rakyat dan Kawasan Permukiman, Pertanahan dan Lingkungan Hidup.
            </p>
            <div class="p-4 bg-slate-900/60 border border-slate-800 rounded-2xl flex gap-3">
                <i data-lucide="info" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Aplikasi ini dikembangkan secara mandiri dan terbatas hanya untuk mempermudah dan mendigitalisasi kegiatan internal pada <strong>Bidang Pengelolaan Persampahan dan Ruang Terbuka Hijau (RTH)</strong> dalam pembuatan dokumen pengadaan barang/jasa, kontrol data, serta monitoring serapan anggaran.
                </p>
            </div>
            <p>
                Oleh karena itu, akses dan penggunaan aplikasi ini ditujukan sepenuhnya bagi kalangan bidang internal saja dan <strong>tidak dipublikasikan untuk umum</strong>. Segala bentuk pemanfaatan data dalam aplikasi ini sepenuhnya dikelola secara mandiri demi efisiensi birokrasi dan administrasi internal bidang.
            </p>
        </div>

        <div class="border-t border-slate-800/60 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-white text-sm font-semibold transition-all shadow-sm">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                <span>Kembali ke Beranda</span>
            </a>
            
            <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition-all shadow-md shadow-red-950/20">
                <span>Masuk Sistem</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
