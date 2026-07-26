@props([
    // 'penuh'  → halaman chat layar penuh (mobile/PWA)
    // 'panel'  → panel melayang di desktop
    'mode' => 'penuh',
])

{{--
    Percakapan + kolom ketik. Satu komponen dipakai dua tempat (halaman penuh
    dan panel desktop) supaya tidak ada dua implementasi chat yang harus
    dirawat berbarengan.

    Tidak ada kartu draf di sini: ringkasan draf disampaikan sebagai teks,
    persis seperti balasan lain, agar tetap terasa percakapan.
--}}
<div x-data="aiChat()" x-init="init()" class="flex flex-col h-full min-h-0 bg-white">

    {{-- Riwayat percakapan --}}
    <div x-ref="riwayat" class="flex-1 min-h-0 overflow-y-auto px-4 py-6 space-y-6">

        {{-- Sapaan saat percakapan masih kosong --}}
        <div x-show="pesan.length === 0" class="h-full flex flex-col items-center justify-center text-center px-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 via-violet-500 to-rose-500 flex items-center justify-center shadow-lg">
                <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
            </div>
            <h1 class="mt-5 text-2xl font-semibold text-slate-800">Halo, {{ auth()->user()->name ?? 'rekan' }}</h1>
            <p class="mt-1.5 text-sm text-slate-500">Ada yang bisa saya bantu hari ini?</p>
        </div>

        <template x-for="(m, i) in pesan" :key="i">
            <div>
                {{-- Bubble pengguna --}}
                <div x-show="m.dari === 'user'" class="flex justify-end">
                    <div class="max-w-[85%] px-4 py-2.5 rounded-2xl rounded-br-md bg-slate-100 text-slate-800 text-sm leading-relaxed whitespace-pre-wrap break-words">
                        <span x-text="m.teks"></span>
                    </div>
                </div>

                {{-- Balasan asisten --}}
                <div x-show="m.dari === 'ai'" class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 via-violet-500 to-rose-500 flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="text-sm leading-relaxed text-slate-800 break-words" x-html="m.html"></div>

                        {{-- Aksi menyusul balasan (mis. draf SPD siap disetujui).
                             Tombol biasa, bukan formulir — pengubahan dilakukan
                             dengan mengetik, sama seperti percakapan lain. --}}
                        <div x-show="m.aksi" class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="setujui(m)" :disabled="sibuk"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-slate-900 text-white text-xs font-medium hover:bg-black disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span x-text="m.aksi.label"></span>
                            </button>
                            <span class="text-xs text-slate-400">atau ketik perubahannya di bawah</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Menunggu balasan --}}
        <div x-show="sibuk" class="flex items-start gap-3" style="display:none">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 via-violet-500 to-rose-500 flex items-center justify-center shrink-0 animate-pulse">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-white"></i>
            </div>
            <div class="flex items-center gap-1 pt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>
    </div>

    {{-- Kolom ketik --}}
    {{-- pb: sisakan ruang untuk garis beranda iOS agar tombol tidak tertutup. --}}
    <div class="shrink-0 px-3 pt-2 bg-white" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
        {{-- Menu lampiran, muncul di atas tombol + --}}
        <div x-show="menuLampiran" x-transition.opacity @click.outside="menuLampiran = false"
             class="mb-2 ml-1 w-52 rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden" style="display:none">
            <button type="button" @click="pilihBerkas('kamera')" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50">
                <i data-lucide="camera" class="w-4 h-4 text-slate-400"></i> Ambil foto
            </button>
            <button type="button" @click="pilihBerkas('galeri')" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-t border-slate-100">
                <i data-lucide="image" class="w-4 h-4 text-slate-400"></i> Pilih foto
            </button>
            <button type="button" @click="pilihBerkas('berkas')" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-t border-slate-100">
                <i data-lucide="paperclip" class="w-4 h-4 text-slate-400"></i> Unggah berkas
            </button>
        </div>

        {{-- Lampiran terpilih --}}
        <div x-show="lampiran" class="mb-2 mx-1 flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-xs text-slate-600" style="display:none">
            <i data-lucide="paperclip" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="truncate" x-text="lampiran?.name"></span>
            <button type="button" @click="lampiran = null" class="ml-auto p-0.5 rounded hover:text-rose-500 shrink-0">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <div class="flex items-end gap-1.5 rounded-[26px] border border-slate-200 bg-white px-2 py-1.5 shadow-sm focus-within:border-slate-300 transition-colors">
            {{-- Kiri: tambah lampiran --}}
            <button type="button" @click="menuLampiran = !menuLampiran"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"
                    title="Tambahkan foto atau berkas">
                <i data-lucide="plus" class="w-5 h-5"></i>
            </button>

            {{-- min-w-0: tanpa ini textarea menolak menyusut dan kolomnya melar --}}
            <textarea x-ref="masukan" x-model="masukan" rows="1"
                      @input="aturTinggi()" @keydown.enter.prevent="$event.shiftKey ? sisipBaris() : kirim()"
                      {{-- Placeholder dijaga pendek: kalau membungkus dua baris,
                           teksnya terpotong oleh tinggi kolom yang cuma 1 baris. --}}
                      placeholder="Tanya apa saja…"
                      class="flex-1 min-w-0 resize-none border-0 bg-transparent px-1 py-2 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-0 max-h-40"></textarea>

            {{-- Kanan: mikrofon saat kosong, kirim saat ada isi --}}
            <button type="button" x-show="!masukan.trim()" @click="suara()"
                    :class="mendengar ? 'text-rose-500 bg-rose-50 animate-pulse' : 'text-slate-500 hover:bg-slate-100'"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                    title="Bicara">
                <i data-lucide="mic" class="w-5 h-5"></i>
            </button>
            <button type="button" x-show="masukan.trim()" @click="kirim()" :disabled="sibuk"
                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center bg-slate-900 text-white hover:bg-black disabled:opacity-40 transition-colors"
                    title="Kirim" style="display:none">
                <i data-lucide="arrow-up" class="w-5 h-5"></i>
            </button>
        </div>

        <p class="mt-2 text-center text-[11px] text-slate-400 px-4">
            Asisten bisa keliru. Periksa kembali dokumen resminya.
        </p>

        <input type="file" x-ref="berkas" @change="terimaBerkas($event)" class="hidden">
    </div>
</div>

@once
    @push('scripts')
    <script>
        function aiChat() {
            return {
                pesan: [],
                masukan: '',
                sibuk: false,
                mendengar: false,
                menuLampiran: false,
                lampiran: null,
                jobId: null,
                pengenalSuara: null,

                init() {
                    this.siapkanSuara();
                    // Fokus otomatis supaya papan ketik langsung siap. Sebagian
                    // peramban seluler menolak fokus tanpa sentuhan pengguna —
                    // di situ papan ketik baru muncul setelah kolom disentuh.
                    this.$nextTick(() => this.$refs.masukan?.focus());
                },

                // ── Percakapan ────────────────────────────────────

                async kirim() {
                    const teks = this.masukan.trim();
                    if (!teks || this.sibuk) return;

                    this.pesan.push({ dari: 'user', teks });
                    this.masukan = '';
                    this.aturTinggi();
                    this.sibuk = true;
                    this.keBawah();

                    try {
                        const data = await this.panggil('{{ route('ai.chat') }}', {
                            prompt: teks,
                            job_id: this.jobId,
                        });

                        if (data.status === 'success' && data.data) {
                            this.jobId = data.data.draft?.job_id ?? this.jobId;
                            this.tambahBalasan(data.data);
                        } else {
                            this.tambahTeks(data.message || 'Maaf, permintaan tidak dapat diproses.');
                        }
                    } catch (e) {
                        this.tambahTeks('Gagal terhubung ke asisten. Coba lagi sebentar.');
                    }

                    this.sibuk = false;
                    this.keBawah();
                },

                tambahBalasan(d) {
                    const draft = d.draft ?? null;
                    let teks = d.response_text || '';

                    // Ringkasan draf ditulis sebagai teks, bukan formulir.
                    if (draft) {
                        teks += "\n\n" + this.ringkasDraf(draft);
                    }

                    this.pesan.push({
                        dari: 'ai',
                        html: this.keHtml(teks),
                        aksi: draft?.lengkap ? { jenis: 'setujui_spd', jobId: draft.job_id, label: 'Setujui & buat SPD' } : null,
                    });
                },

                tambahTeks(teks) {
                    this.pesan.push({ dari: 'ai', html: this.keHtml(teks), aksi: null });
                },

                ringkasDraf(draft) {
                    const s = draft.slots || {};
                    const nama = (s.personel?.tampil || []).map(p => p.nama).join(', ');
                    const paket = s.paket?.tampil?.label ?? null;

                    const baris = [
                        ['Pelaksana', nama],
                        ['Tujuan', s.tujuan?.nilai],
                        ['Tipe', s.tipe_perjalanan?.nilai],
                        ['Tanggal', s.tanggal_berangkat?.tampil
                            ? (s.tanggal_kembali?.nilai && s.tanggal_kembali.nilai !== s.tanggal_berangkat.nilai
                                ? `${s.tanggal_berangkat.tampil} s/d ${s.tanggal_kembali.tampil}`
                                : `${s.tanggal_berangkat.tampil} (pergi-pulang)`)
                            : null],
                        ['Maksud', s.maksud?.nilai],
                        ['Paket', paket],
                        ['Dasar', s.dasar_pelaksanaan?.nilai],
                    ].filter(([, v]) => v);

                    return baris.map(([k, v]) => `**${k}:** ${v}`).join("\n");
                },

                async setujui(m) {
                    if (this.sibuk) return;
                    this.sibuk = true;

                    try {
                        const data = await this.panggil('{{ route('ai.approve') }}', { job_id: m.aksi.jobId });

                        if (data.status === 'success' && data.data) {
                            this.tambahTeks(data.data.message);
                            m.aksi = null;
                            this.jobId = null;
                            if (data.data.redirect_url) {
                                setTimeout(() => window.location.href = data.data.redirect_url, 900);
                            }
                        } else {
                            this.tambahTeks(data.message || 'Persetujuan ditolak.');
                        }
                    } catch (e) {
                        this.tambahTeks('Gagal memproses persetujuan.');
                    }

                    this.sibuk = false;
                    this.keBawah();
                },

                // ── Lampiran ──────────────────────────────────────

                pilihBerkas(jenis) {
                    const el = this.$refs.berkas;
                    el.accept = jenis === 'berkas' ? '' : 'image/*';
                    if (jenis === 'kamera') { el.setAttribute('capture', 'environment'); }
                    else { el.removeAttribute('capture'); }
                    this.menuLampiran = false;
                    el.click();
                },

                terimaBerkas(e) {
                    const f = e.target.files?.[0];
                    if (!f) return;
                    this.lampiran = f;
                    e.target.value = '';
                    // Pengunggahan & OCR belum tersedia — jangan berpura-pura bisa.
                    this.tambahTeks('Berkas sudah dipilih, tetapi pembacaan dokumen (OCR) belum tersedia. Sementara ini tuliskan saja isinya.');
                    this.keBawah();
                },

                // ── Suara ─────────────────────────────────────────

                siapkanSuara() {
                    const Pengenal = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!Pengenal) return;

                    this.pengenalSuara = new Pengenal();
                    this.pengenalSuara.lang = 'id-ID';
                    this.pengenalSuara.continuous = false;
                    this.pengenalSuara.interimResults = false;
                    this.pengenalSuara.onresult = (e) => {
                        this.masukan = e.results[0][0].transcript;
                        this.mendengar = false;
                        this.aturTinggi();
                    };
                    this.pengenalSuara.onerror = () => this.mendengar = false;
                    this.pengenalSuara.onend = () => this.mendengar = false;
                },

                suara() {
                    if (!this.pengenalSuara) {
                        this.tambahTeks('Peramban ini belum mendukung masukan suara.');
                        return;
                    }
                    if (this.mendengar) { this.pengenalSuara.stop(); this.mendengar = false; return; }
                    this.mendengar = true;
                    this.pengenalSuara.start();
                },

                // ── Peralatan ─────────────────────────────────────

                async panggil(url, isi) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(isi),
                    });

                    try {
                        return await res.json();
                    } catch (e) {
                        return {
                            status: 'error',
                            message: res.status === 419
                                ? 'Sesi Anda sudah berakhir. Muat ulang halaman lalu coba lagi.'
                                : `Server membalas kode ${res.status}.`,
                        };
                    }
                },

                keHtml(teks) {
                    const aman = (teks || '')
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return aman
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\n/g, '<br>');
                },

                sisipBaris() {
                    this.masukan += "\n";
                    this.aturTinggi();
                },

                aturTinggi() {
                    const el = this.$refs.masukan;
                    if (!el) return;
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight, 160) + 'px';
                },

                keBawah() {
                    this.$nextTick(() => {
                        const el = this.$refs.riwayat;
                        if (el) el.scrollTop = el.scrollHeight;
                        if (window.lucide) lucide.createIcons();
                    });
                },
            };
        }
    </script>
    @endpush
@endonce
