<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KDMP | Kelola Digital Manajemen Pengadaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body { font-family: 'Inter', sans-serif; }

        /* Animasi Bendera */
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

<body class="min-h-screen overflow-hidden">

    <div class="fixed inset-0 overflow-hidden">
        <img src="{{ asset('/images/bendera.jpg') }}"
             class="w-full h-full object-cover animate-flag"
             alt="Indonesia Flag">
    </div>

    <div class="fixed inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/30"></div>

    <div class="relative z-10 min-h-screen flex items-center">
        <div class="container mx-auto px-8">
            <div class="max-w-xl">

                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-sm mb-6 shadow-sm">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    Sistem Digital Pengadaan
                </div>

                <h1 class="text-white font-extrabold text-6xl md:text-7xl tracking-tight leading-none">
                    KDMP
                </h1>

                <div class="w-32 h-1.5 bg-red-600 rounded-full my-6"></div>

                <p class="text-white/90 text-xl font-medium leading-relaxed">
                    Kelola Digital Manajemen Pengadaan
                </p>

                <p class="text-white/70 mt-4 text-base leading-relaxed max-w-md">
                    Platform terpadu untuk mengelola seluruh proses pengadaan, administrasi kegiatan, serta dokumentasi secara cepat, transparan dan akuntabel.
                </p>

                <div class="flex flex-wrap gap-4 mt-10">
                    <a href="{{ route('login') }}"
                       class="group px-8 py-4 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow-xl shadow-red-900/40 transition-all duration-300 hover:scale-105">
                        Masuk Sistem <span class="group-hover:ml-2 transition-all">→</span>
                    </a>

                    <a href="{{ route('panduan') }}"
                       class="px-8 py-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white font-medium hover:bg-white/20 transition duration-300">
                        Pelajari Lebih Lanjut
                    </a>
                </div>

                <div class="mt-16 space-y-2 text-white/50 text-xs">
                    <div class="tracking-widest font-semibold">
                        © {{ date('Y') }} KDMP v1.0.1
                    </div>
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span>Aplikasi dikembangkan oleh <strong class="text-white/80">sunigla</strong></span>
                        <span>•</span>
                        <a href="{{ route('disclaimer') }}" class="text-red-400 hover:text-red-300 underline transition-colors">Disclaimer</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
