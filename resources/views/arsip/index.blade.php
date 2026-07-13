@component('layouts.kdmp')
    @section('title', 'Arsip Dokumen')

    <div class="space-y-6" x-data="archiveApp()">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="folder-open" class="w-6 h-6 text-indigo-600"></i>
                    Arsip <span class="text-indigo-600">Dokumen</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Semua dokumen yang dihasilkan aplikasi, tersusun per tahun anggaran, lalu per perjalanan dinas / paket pengadaan.</p>
            </div>
        </div>

        {{-- Breadcrumb + pencarian --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 text-sm font-semibold flex-wrap">
                <button type="button" @click="goTo(-1)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg transition-colors"
                    :class="path.length === 0 ? 'text-indigo-700 bg-indigo-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                    <i data-lucide="archive" class="w-4 h-4"></i> Arsip
                </button>

                <template x-for="(segment, index) in path" :key="index">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        <button type="button" @click="goTo(index)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg transition-colors"
                            :class="index === path.length - 1 ? 'text-indigo-700 bg-indigo-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                            
                            {{-- Ikon khusus untuk level tertentu di breadcrumb --}}
                            <template x-if="index === 0">
                                <i data-lucide="folder" class="w-4 h-4 text-amber-400"></i>
                            </template>
                            <template x-if="index === 1 && segment === 'SPD'">
                                <i data-lucide="plane" class="w-4 h-4 text-indigo-500"></i>
                            </template>
                            <template x-if="index === 1 && segment === 'PBJ'">
                                <i data-lucide="briefcase" class="w-4 h-4 text-indigo-500"></i>
                            </template>
                            
                            <span x-text="segment"></span>
                        </button>
                    </span>
                </template>
            </div>

            <div class="relative" x-show="isFilesView()" style="display:none">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" x-model="q" placeholder="Cari dokumen..."
                    class="w-56 pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>
        </div>

        {{-- Konten --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 min-h-[320px]">
            {{-- VIEW: GOOGLE DRIVE (embedded) --}}
            <template x-if="driveEmbed">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <i data-lucide="hard-drive" class="w-4 h-4 text-emerald-600"></i>
                            Google Drive
                        </p>
                        <div class="flex items-center gap-2">
                            <a :href="driveRaw" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka di Drive
                            </a>
                            <button type="button" @click="driveEmbed = null; refreshIcons()"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali
                            </button>
                        </div>
                    </div>
                    <iframe :src="driveEmbed" class="w-full rounded-xl border border-slate-200 bg-slate-50" style="height: 540px;" loading="lazy"></iframe>
                    <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3 h-3"></i>
                        Pratinjau read-only. Klik file untuk membukanya di Google Drive. Folder harus di-share "siapa saja yang memiliki link".
                    </p>
                </div>
            </template>

            {{-- VIEW: FOLDERS --}}
            <template x-if="!driveEmbed && !isFilesView()">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="(value, key) in currentFolder()" :key="key">
                        <div>
                            {{-- Pintasan Google Drive (link, bukan folder) --}}
                            <template x-if="key === '__gdrive__'">
                                <button type="button" @click="openDrive(value)"
                                    class="group w-full h-full flex flex-col items-center text-center gap-2 p-5 rounded-2xl border border-emerald-200 bg-emerald-50/40 hover:border-emerald-300 hover:bg-emerald-50 hover:shadow-md transition-all">
                                    <span class="w-14 h-14 rounded-2xl border flex items-center justify-center bg-white border-emerald-100 text-emerald-600 group-hover:scale-105 transition-transform">
                                        <i data-lucide="hard-drive" class="w-7 h-7"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 text-sm leading-tight">Google Drive</span>
                                    <span class="text-[11px] font-semibold text-emerald-600">Lihat isi folder</span>
                                </button>
                            </template>

                            {{-- Folder biasa --}}
                            <template x-if="key !== '__gdrive__'">
                                <button type="button" @click="openFolder(key)"
                                    class="group w-full h-full flex flex-col items-center text-center gap-2 p-5 rounded-2xl border border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md transition-all">

                                    {{-- Ikon Dinamis Berdasarkan Level --}}
                                    <template x-if="path.length === 0">
                                        <span class="relative">
                                            <i data-lucide="folder" class="w-14 h-14 text-amber-400 fill-amber-100 group-hover:scale-105 transition-transform"></i>
                                        </span>
                                    </template>

                                    <template x-if="path.length === 1">
                                        <span class="w-14 h-14 rounded-2xl border flex items-center justify-center bg-indigo-50 border-indigo-100 text-indigo-500 group-hover:scale-105 transition-transform">
                                            <template x-if="key === 'SPD'"><i data-lucide="plane" class="w-7 h-7"></i></template>
                                            <template x-if="key === 'PBJ'"><i data-lucide="briefcase" class="w-7 h-7"></i></template>
                                        </span>
                                    </template>

                                    <template x-if="path.length >= 2">
                                        <span class="w-14 h-14 rounded-2xl border flex items-center justify-center bg-slate-50 border-slate-100 text-slate-500 group-hover:scale-105 transition-transform">
                                            <template x-if="path.length === 2">
                                                <i data-lucide="folder-open" class="w-7 h-7"></i>
                                            </template>
                                            <template x-if="path.length > 2">
                                                <i data-lucide="package" class="w-7 h-7"></i>
                                            </template>
                                        </span>
                                    </template>

                                    <span class="font-bold text-slate-800 text-sm leading-tight line-clamp-2" x-text="key" :title="key"></span>

                                    <span class="text-[11px] font-semibold text-slate-400" x-text="countItems(value) + (Array.isArray(value) && (value.length === 0 || value[0].url) ? ' dokumen' : ' item')"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                    
                    {{-- Kosong --}}
                    <template x-if="Object.keys(currentFolder()).length === 0">
                        <div class="col-span-full py-10 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="folder-open" class="w-8 h-8 text-slate-400"></i>
                            </div>
                            <p class="font-medium text-slate-600">Folder masih kosong.</p>
                        </div>
                    </template>
                </div>
            </template>

            {{-- VIEW: FILES --}}
            <template x-if="!driveEmbed && isFilesView()">
                <div class="divide-y divide-slate-100">
                    <template x-if="currentFolder().length === 0">
                        <div class="py-10 text-center text-slate-400 text-sm">
                            Tidak ada dokumen.
                        </div>
                    </template>

                    <template x-for="f in currentFolder()" :key="f.label + f.url">
                        <div class="py-3 px-1 flex items-center justify-between gap-4"
                            x-show="match(f.label + ' ' + f.sub)">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 bg-emerald-50 border-emerald-100 text-emerald-500">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 text-sm leading-snug truncate" :title="f.label" x-text="f.label"></p>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate" x-text="f.sub"></p>
                                </div>
                            </div>
                            
                            <template x-if="f.action === 'download'">
                                <a :href="f.url"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh
                                </a>
                            </template>
                            <template x-if="f.action === 'popup'">
                                <button type="button"
                                    @click="window.open(f.url, 'arsip-cetak', 'width=900,height=700')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                </button>
                            </template>
                            <template x-if="f.action === 'tab'">
                                <button type="button"
                                    @click="window.open(f.url, '_blank')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('archiveApp', () => ({
                tree: @json($tree),
                path: [],
                q: '',
                driveEmbed: null,
                driveRaw: '',

                refreshIcons() {
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                },

                // Ubah URL folder Drive (share link) -> embedded folder view.
                toDriveEmbed(url) {
                    const m = String(url).match(/folders\/([\w-]+)/);
                    const id = m ? m[1] : '';
                    return id ? ('https://drive.google.com/embeddedfolderview?id=' + id + '#grid') : url;
                },
                openDrive(url) {
                    this.driveRaw = url;
                    this.driveEmbed = this.toDriveEmbed(url);
                    this.q = '';
                    this.refreshIcons();
                },

                currentFolder() {
                    let node = this.tree;
                    for (let p of this.path) {
                        if (node[p] === undefined) return [];
                        node = node[p];
                    }
                    return node;
                },
                
                isFilesView() {
                    let folder = this.currentFolder();
                    return Array.isArray(folder) && (folder.length === 0 || folder[0].url !== undefined);
                },
                
                openFolder(name) {
                    this.driveEmbed = null;
                    this.path.push(name);
                    this.q = '';
                    this.$nextTick(() => lucide.createIcons());
                },

                goTo(index) {
                    this.driveEmbed = null;
                    if (index === -1) {
                        this.path = [];
                    } else {
                        this.path = this.path.slice(0, index + 1);
                    }
                    this.q = '';
                    this.$nextTick(() => lucide.createIcons());
                },
                
                match(s) {
                    return !this.q || s.toLowerCase().includes(this.q.toLowerCase());
                },
                
                countItems(node) {
                    if (Array.isArray(node)) return node.length;
                    return Object.keys(node).length;
                },
                
                init() {
                    this.$watch('path', () => {
                        this.$nextTick(() => lucide.createIcons());
                    });
                }
            }));
        });
    </script>
@endcomponent
