<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Pengguna | KDMP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
<body class="bg-slate-900 text-slate-100 min-h-screen relative overflow-x-hidden" x-data="{ activeTab: 'pendahuluan' }">

    {{-- Background Flag --}}
    <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
        <img src="{{ asset('/images/bendera.jpg') }}"
             class="w-full h-full object-cover opacity-45 animate-flag"
             alt="Indonesia Flag">
    </div>
    <div class="fixed inset-0 bg-slate-950/70 -z-10 pointer-events-none"></div>

    <div class="container mx-auto px-4 py-8 max-w-6xl relative z-10">
        
        {{-- Navigation Header --}}
        <header class="flex items-center justify-between border-b border-slate-800 pb-6 mb-8">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center font-extrabold text-xl shadow-lg shadow-red-900/30">K</span>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white">KDMP</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Panduan Pengguna Sistem</p>
                </div>
            </div>
            
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition-all shadow-md shadow-red-900/20">
                <span>Masuk Sistem</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">
            
            {{-- Sidebar Navigation --}}
            <aside class="space-y-2 lg:sticky lg:top-8 bg-slate-950/40 border border-slate-800/80 rounded-2xl p-4 backdrop-blur-md">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-slate-900/60 transition-colors text-sm font-semibold mb-4 border-b border-slate-800/60 pb-4">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>Kembali ke Beranda</span>
                </a>
                
                <button type="button" @click="activeTab = 'pendahuluan'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all"
                    :class="activeTab === 'pendahuluan' ? 'bg-red-600/10 border border-red-500/20 text-red-400 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 border border-transparent'">
                    <i data-lucide="book-open" class="w-4.5 h-4.5 shrink-0"></i>
                    <span>1. Pendahuluan</span>
                </button>

                <button type="button" @click="activeTab = 'penyedia'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all"
                    :class="activeTab === 'penyedia' ? 'bg-red-600/10 border border-red-500/20 text-red-400 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 border border-transparent'">
                    <i data-lucide="briefcase-business" class="w-4.5 h-4.5 shrink-0"></i>
                    <span>2. Paket Penyedia</span>
                </button>

                <button type="button" @click="activeTab = 'swakelola'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all"
                    :class="activeTab === 'swakelola' ? 'bg-red-600/10 border border-red-500/20 text-red-400 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 border border-transparent'">
                    <i data-lucide="plane" class="w-4.5 h-4.5 shrink-0"></i>
                    <span>3. Swakelola &amp; SPD</span>
                </button>

                <button type="button" @click="activeTab = 'dikecualikan'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all"
                    :class="activeTab === 'dikecualikan' ? 'bg-red-600/10 border border-red-500/20 text-red-400 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 border border-transparent'">
                    <i data-lucide="file-warning" class="w-4.5 h-4.5 shrink-0"></i>
                    <span>4. Dikecualikan</span>
                </button>

                <button type="button" @click="activeTab = 'roles'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all"
                    :class="activeTab === 'roles' ? 'bg-red-600/10 border border-red-500/20 text-red-400 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 border border-transparent'">
                    <i data-lucide="users" class="w-4.5 h-4.5 shrink-0"></i>
                    <span>5. Hak Akses &amp; Peran</span>
                </button>
            </aside>

            {{-- Main Content Area --}}
            <main class="bg-slate-950/40 border border-slate-800/80 rounded-3xl p-6 sm:p-8 backdrop-blur-md min-h-[500px]">
                
                {{-- TAB: PENDAHULUAN --}}
                <div x-show="activeTab === 'pendahuluan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="book-open" class="w-7 h-7 text-red-500"></i>
                        Pendahuluan KDMP
                    </h2>
                    <p class="text-slate-300 leading-relaxed">
                        <strong>KDMP (Kelola Digital Manajemen Pengadaan)</strong> adalah platform digital terpadu yang dirancang khusus untuk memfasilitasi dan mendokumentasikan siklus pengadaan barang/jasa pemerintah di lingkup SKPD secara transparan, akurat, dan akuntabel.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-850">
                            <span class="w-10 h-10 rounded-xl bg-red-600/10 border border-red-500/20 flex items-center justify-center text-red-400 mb-4">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </span>
                            <h4 class="font-bold text-white mb-2">Keamanan &amp; Akuntabilitas</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">Seluruh data dikunci secara otomatis begitu proses diselesaikan untuk mencegah perubahan data tanpa koordinasi.</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-850">
                            <span class="w-10 h-10 rounded-xl bg-emerald-600/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            </span>
                            <h4 class="font-bold text-white mb-2">Pemantauan Real-time</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">Menyediakan dashboard grafis serapan anggaran untuk memantau sisa pagu di setiap sub-kegiatan dinas.</p>
                        </div>
                    </div>
                </div>

                {{-- TAB: PENYEDIA --}}
                <div x-show="activeTab === 'penyedia'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display:none">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="briefcase-business" class="w-7 h-7 text-red-500"></i>
                        Manajemen Paket Penyedia
                    </h2>
                    <p class="text-slate-300 leading-relaxed">
                        Modul ini menangani pengelolaan paket pengadaan yang dikerjakan oleh pihak ketiga (Penyedia Jasa). Pengguna dapat memantau progres pengerjaan melalui tahapan sistematis.
                    </p>
                    <div class="relative pl-6 border-l-2 border-slate-800 space-y-6 mt-6">
                        <div class="relative">
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-slate-900 border-2 border-slate-700 flex items-center justify-center"></span>
                            <h4 class="font-bold text-white text-sm">1. Persiapan Pengadaan</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Penyusunan data spesifikasi teknis, harga acuan, kelengkapan berkas, dan dokumen penunjang awal sebelum proses pemilihan dilakukan.</p>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-slate-900 border-2 border-slate-700 flex items-center justify-center"></span>
                            <h4 class="font-bold text-white text-sm">2. Pemilihan Penyedia</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Proses administrasi pemilihan penyedia jasa yang sah berdasarkan regulasi pengadaan barang/jasa pemerintah.</p>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-slate-900 border-2 border-slate-700 flex items-center justify-center"></span>
                            <h4 class="font-bold text-white text-sm">3. Pelaksanaan Kontrak</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Penyusunan rincian progres pekerjaan di lapangan, berita acara serah terima (BAST), serta pencatatan prestasi pekerjaan.</p>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-red-600 border-2 border-red-400 flex items-center justify-center"></span>
                            <h4 class="font-bold text-white text-sm">4. Pembayaran &amp; Selesai</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Penginputan nominal pembayaran yang terrealisasi sesuai SP2D, lalu penguncian paket secara permanen jika status selesai.</p>
                        </div>
                    </div>
                </div>

                {{-- TAB: SWAKELOLA --}}
                <div x-show="activeTab === 'swakelola'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display:none">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="plane" class="w-7 h-7 text-red-500"></i>
                        Swakelola &amp; Perjalanan Dinas
                    </h2>
                    <p class="text-slate-300 leading-relaxed">
                        KDMP menyediakan integrasi pengajuan dokumen perjalanan dinas (SPD/SPT) dan lembur staf secara swakelola berbasis sub-kegiatan dan kode rekening anggaran terkait.
                    </p>
                    <div class="bg-slate-900/60 rounded-2xl p-5 border border-slate-800 space-y-4">
                        <h4 class="font-bold text-white flex items-center gap-2 text-sm">
                            <i data-lucide="calendar" class="w-4 h-4 text-emerald-400"></i>
                            Alur Pembuatan SPD:
                        </h4>
                        <ul class="space-y-3 text-xs text-slate-300 pl-4 list-disc leading-relaxed">
                            <li>
                                <strong>Kalender Kegiatan (Staf)</strong>: Pengguna Staf dapat memilih rentang tanggal perjalanan dinas langsung pada kalender interaktif (melalui klik-ganda atau klik-seret/drag).
                            </li>
                            <li>
                                <strong>Pengisian Detail &amp; Personel</strong>: Tentukan tujuan, maksud dinas, tanggal keberangkatan/kepulangan, dan pilih personel PNS/Non-PNS pelaksana kegiatan.
                            </li>
                            <li>
                                <strong>Persetujuan Kabid</strong>: Dokumen yang diajukan masuk ke kotak masuk Kabid untuk ditinjau (*Approved*, *Revision*, atau *Rejected*).
                            </li>
                            <li>
                                <strong>Pengajuan SPJ Biaya Rampung</strong>: Setelah kembali dari dinas, Staf mengunggah bukti kwitansi dan mengajukan Surat Pertanggungjawaban (SPJ) untuk divalidasi Kabid sebagai realisasi serapan anggaran.
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- TAB: DIKECUALIKAN --}}
                <div x-show="activeTab === 'dikecualikan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display:none">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="file-warning" class="w-7 h-7 text-red-500"></i>
                        Pengadaan Dikecualikan
                    </h2>
                    <p class="text-slate-300 leading-relaxed">
                        Pengadaan Dikecualikan adalah pengadaan barang/jasa pemerintah yang dikecualikan dari ketentuan Peraturan Presiden Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah yang biasa diterapkan (contoh: Belanja Materai, Belanja Bahan Bakar Minyak).
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800">
                            <h4 class="font-bold text-white mb-2 text-sm flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Di Dalam Sistem
                            </h4>
                            <p class="text-xs text-slate-400 leading-relaxed">Proses pengadaan yang pencatatan nota pesanan, SP, BAST, dan kwitansi belanjanya diinput langsung ke dalam tabel rincian transaksi KDMP secara bertahap.</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800">
                            <h4 class="font-bold text-white mb-2 text-sm flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                Di Luar Sistem
                            </h4>
                            <p class="text-xs text-slate-400 leading-relaxed">Untuk pengadaan dikecualikan yang pembayarannya tercatat langsung di SP2D eksternal, di mana data transaksi cukup diunggah secara kolektif sekali waktu.</p>
                        </div>
                    </div>
                </div>

                {{-- TAB: ROLES --}}
                <div x-show="activeTab === 'roles'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6" style="display:none">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="users" class="w-7 h-7 text-red-500"></i>
                        Hak Akses &amp; Peran (Roles)
                    </h2>
                    <p class="text-slate-300 leading-relaxed">
                        Sistem KDMP didesain dengan pemisahan peran yang jelas (*role segregation*) untuk menjamin kontrol administrasi yang ketat dan meminimalisir kesalahan data anggaran.
                    </p>
                    
                    <div class="space-y-4 mt-6">
                        <div class="p-4 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-start gap-4">
                            <span class="w-10 h-10 rounded-xl bg-blue-600/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-white text-sm">Staf (Sub Bagian / Bidang)</h4>
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Memiliki hak akses untuk menginput rencana paket RUP, membuat draf pengajuan SPD/SPT dinas, mengunggah bukti kwitansi SPJ, serta mengisi kelengkapan dokumen teknis pengadaan.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-start gap-4">
                            <span class="w-10 h-10 rounded-xl bg-emerald-600/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-white text-sm">Kabid (Kepala Bidang / PPTK)</h4>
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Memiliki otoritas penuh untuk meninjau ajukan paket, melakukan persetujuan (approval) rencana RUP, melakukan verifikasi SPD &amp; SPJ perjalanan dinas secara sistem, serta memantau realisasi monev.</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-start gap-4">
                            <span class="w-10 h-10 rounded-xl bg-red-600/10 border border-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-white text-sm">Admin (Pengawas &amp; Kontrol)</h4>
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">Berfungsi sebagai kontrol data dan input data standar.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
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
