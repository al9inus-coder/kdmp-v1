<div x-data="geminiLightAssistant()"
     @open-ai-drawer.window="openDrawer($event.detail)"
     @open-camera-ocr.window="triggerCameraUpload()"
     class="relative">

    <!-- DESKTOP FLOATING ACTION BUTTON (BOT ICON) -->
    <div class="hidden md:block fixed bottom-6 right-6 z-50">
        <button @click="toggleDrawer()"
                class="w-14 h-14 rounded-full bg-[#1F1F1F] hover:bg-slate-800 text-white flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 ring-4 ring-slate-900/10 group"
                title="KDMP Gemini AI">
            <i data-lucide="bot" class="w-7 h-7 group-hover:rotate-12 transition-transform"></i>
            <span class="absolute top-1 right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
        </button>
    </div>

    <!-- GOOGLE GEMINI LIGHT MODE (PRISTINE OFF-WHITE #F8F9FA) FULLSCREEN MOBILE & DESKTOP CANVAS -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-98"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98"
         :class="isDarkMode ? 'bg-[#131314] text-[#E3E2E6] border-slate-800' : 'bg-[#F8F9FA] text-[#1F1F1F] border-slate-200'"
         class="fixed inset-0 md:inset-auto md:bottom-6 md:right-6 z-50 w-full md:w-[480px] md:h-[700px] md:rounded-3xl shadow-2xl flex flex-col overflow-hidden font-sans border-0 md:border transition-colors duration-300"
         style="display: none;">

        <!-- 1. Gemini Minimalist Top Header Bar -->
        <div :class="isDarkMode ? 'bg-[#131314] border-slate-800/60' : 'bg-[#F8F9FA] border-slate-200/80'"
             class="h-14 px-4 flex items-center justify-between shrink-0 border-b transition-colors">
            <div class="flex items-center gap-2">
                <!-- Gemini Logo Spark -->
                <svg class="w-6 h-6 text-cyan-400" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="url(#geminiGradLight)"/>
                    <defs>
                        <linearGradient id="geminiGradLight" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#4285F4"/>
                            <stop offset="0.5" stop-color="#9B51E0"/>
                            <stop offset="1" stop-color="#E91E63"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div :class="isDarkMode ? 'bg-[#1E1F22] text-slate-200 border-slate-800' : 'bg-white text-slate-800 border-slate-200/80 shadow-sm'"
                     class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                    <span>Gemini 1.5 Flash</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-60"></i>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button @click="toggleDarkMode()" :class="isDarkMode ? 'text-slate-400 hover:bg-[#1E1F22]' : 'text-slate-500 hover:bg-slate-200/60'" class="p-2 rounded-full transition-colors" title="Toggle Light/Dark Mode">
                    <i data-lucide="sun" x-show="isDarkMode" class="w-4 h-4 text-amber-400"></i>
                    <i data-lucide="moon" x-show="!isDarkMode" class="w-4 h-4 text-slate-600"></i>
                </button>

                <button @click="clearChat()" :class="isDarkMode ? 'text-slate-400 hover:bg-[#1E1F22]' : 'text-slate-500 hover:bg-slate-200/60'" class="p-2 rounded-full transition-colors" title="Percakapan Baru">
                    <i data-lucide="square-pen" class="w-4.5 h-4.5"></i>
                </button>
                
                <button @click="isOpen = false" :class="isDarkMode ? 'text-slate-400 hover:bg-[#1E1F22]' : 'text-slate-500 hover:bg-slate-200/60'" class="p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>
        </div>

        <!-- 2. Gemini Main Canvas Scroll Area -->
        <div class="flex-1 px-5 py-4 overflow-y-auto space-y-6 scrollbar-none" id="geminiChatBody">
            
            <!-- Gemini Light Welcome Greeting (Empty Chat State) -->
            <div x-show="messages.length === 0" class="pt-6 pb-4 space-y-8">
                
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-[#4285F4] via-[#9B51E0] to-[#E91E63] bg-clip-text text-transparent leading-tight">
                        Halo, {{ auth()->user()->name ?? 'Operator' }}
                    </h1>
                    <h2 :class="isDarkMode ? 'text-slate-400' : 'text-slate-500'" class="text-2xl font-semibold">
                        Ada yang bisa saya bantu?
                    </h2>
                </div>

                <!-- Horizontal Suggestion Cards -->
                <div class="space-y-3">
                    <p :class="isDarkMode ? 'text-slate-500' : 'text-slate-400'" class="text-xs font-medium">Saran Perintah Cepat</p>
                    <div class="flex flex-col gap-2.5">
                        
                        <button @click="sendQuickPrompt('buatkan surat perjalanan dinas atas nama damianus ke Bengkayang tanggal 27 Juli 2025')" 
                                :class="isDarkMode ? 'bg-[#1E1F22] hover:bg-[#2A2B2F] border-slate-800 text-slate-200' : 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-800 shadow-sm'"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-semibold text-emerald-600">✈️ Buat SPD Perjalanan Dinas (Kadiskominfo)</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-40 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="sendQuickPrompt('Buatkan Surat Tugas Survei Lapangan tanggal 29 Juli')" 
                                :class="isDarkMode ? 'bg-[#1E1F22] hover:bg-[#2A2B2F] border-slate-800 text-slate-200' : 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-800 shadow-sm'"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📝 Buatkan Draf Surat Tugas Survei Lapangan</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-40 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="sendQuickPrompt('Susun Berita Acara Serah Terima Barang dan Pekerjaan')" 
                                :class="isDarkMode ? 'bg-[#1E1F22] hover:bg-[#2A2B2F] border-slate-800 text-slate-200' : 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-800 shadow-sm'"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📜 Susun Berita Acara (BA) Serah Terima</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-40 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="triggerCameraUpload()" 
                                :class="isDarkMode ? 'bg-[#1E1F22] hover:bg-[#2A2B2F] border-slate-800 text-slate-200' : 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-800 shadow-sm'"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📷 Upload Foto Lembar Disposisi (OCR Scan)</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-40 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                    </div>
                </div>
            </div>

            <!-- Dynamic Chat Bubbles -->
            <template x-for="(msg, index) in messages" :key="index">
                <div class="space-y-4">
                    
                    <!-- USER BUBBLE -->
                    <div x-show="msg.sender === 'user'" class="flex justify-end">
                        <div :class="isDarkMode ? 'bg-[#2A2B2F] text-[#E3E2E6]' : 'bg-[#E9EEF6] text-[#1F1F1F]'"
                             class="px-4 py-3 rounded-2xl rounded-tr-xs text-xs leading-relaxed max-w-[85%] font-normal shadow-sm">
                            <span x-text="msg.text"></span>
                        </div>
                    </div>

                    <!-- GEMINI AI BUBBLE -->
                    <div x-show="msg.sender === 'bot'" class="flex items-start gap-3.5 py-1">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-cyan-400" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="url(#geminiGradMsgLight)"/>
                                <defs>
                                    <linearGradient id="geminiGradMsgLight" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#4285F4"/>
                                        <stop offset="0.5" stop-color="#9B51E0"/>
                                        <stop offset="1" stop-color="#E91E63"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div :class="isDarkMode ? 'text-[#E3E2E6]' : 'text-[#1F1F1F]'" class="text-xs leading-relaxed font-normal space-y-2" x-html="msg.formattedText"></div>

                            <!-- APPROVAL GATE CARD -->
                            <template x-if="msg.approvalRequired">
                                <div :class="isDarkMode ? 'bg-[#1E1F22] border-slate-700/60 text-slate-200' : 'bg-white border-slate-200 text-slate-800 shadow-sm'"
                                     class="p-4 rounded-2xl border space-y-3 mt-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-amber-500 flex items-center gap-1.5">
                                            <i data-lucide="shield-alert" class="w-4 h-4"></i> Konfirmasi Tindakan Pimpinan
                                        </span>
                                        <span class="text-[10px] opacity-60 font-mono" x-text="msg.jobId"></span>
                                    </div>
                                    <p class="text-xs leading-normal opacity-90" x-text="msg.actionSummary"></p>
                                    
                                    <div class="flex items-center gap-2 pt-1">
                                        <button @click="handleApproval(msg.jobId, 'APPROVE')" 
                                                class="flex-1 py-2 rounded-xl bg-[#4285F4] hover:bg-blue-600 text-white font-medium text-xs transition-colors shadow">
                                            ✅ Setujui & Buat Draf SPD
                                        </button>
                                        <button @click="handleApproval(msg.jobId, 'REJECT')" 
                                                :class="isDarkMode ? 'bg-[#2A2B2F] text-slate-300 border-slate-700 hover:bg-slate-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'"
                                                class="px-4 py-2 rounded-xl font-medium text-xs border transition-colors">
                                            ❌ Tolak
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Loading Shimmer Indicator -->
            <div x-show="isLoading" class="flex items-center gap-3 py-2" style="display: none;">
                <svg class="w-5 h-5 text-cyan-400 animate-spin" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="currentColor"/>
                </svg>
                <div :class="isDarkMode ? 'text-slate-500' : 'text-slate-400'" class="text-xs font-normal animate-pulse">
                    Gemini sedang memproses draf SPD...
                </div>
            </div>

        </div>

        <!-- Hidden Camera Input -->
        <input type="file" id="geminiCameraInput" accept="image/*" capture="environment" @change="handleCameraUpload($event)" class="hidden">

        <!-- 3. Iconic Floating Gemini Input Bar Pill (#F0F4F9) -->
        <div :class="isDarkMode ? 'bg-[#131314] border-slate-800/60' : 'bg-[#F8F9FA] border-slate-200/80'"
             class="p-4 shrink-0 border-t transition-colors">
            
            <div :class="isDarkMode ? 'bg-[#1E1F22] border-slate-700/50 text-white' : 'bg-[#F0F4F9] border-slate-300/80 text-slate-900'"
                 class="px-3.5 py-2.5 rounded-full border flex items-center gap-2 shadow-xl focus-within:border-slate-400 transition-all">
                
                <button @click="triggerCameraUpload()" 
                        class="p-1.5 opacity-60 hover:opacity-100 hover:text-purple-500 transition-colors"
                        title="Foto Disposisi OCR">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                </button>

                <button @click="toggleVoiceInput()" 
                        :class="isListening ? 'text-rose-500 animate-pulse' : 'opacity-60 hover:opacity-100 hover:text-cyan-500'"
                        class="p-1.5 transition-colors"
                        title="Voice Command">
                    <i data-lucide="mic" class="w-5 h-5"></i>
                </button>

                <input type="text" 
                       x-model="inputPrompt" 
                       @keyup.enter="sendMessage()" 
                       placeholder="Tanya Gemini atau minta buatkan SPD..." 
                       :class="isDarkMode ? 'text-white placeholder-slate-500' : 'text-slate-900 placeholder-slate-400'"
                       class="flex-1 bg-transparent border-0 px-1 text-xs focus:outline-none focus:ring-0">

                <button @click="sendMessage()" 
                        :class="inputPrompt.trim() ? (isDarkMode ? 'bg-white text-slate-900 shadow' : 'bg-[#1F1F1F] text-white shadow') : (isDarkMode ? 'bg-slate-700/60 text-slate-500' : 'bg-slate-300 text-slate-500')"
                        class="w-8 h-8 rounded-full flex items-center justify-center transition-all">
                    <i data-lucide="arrow-up" class="w-4 h-4"></i>
                </button>
            </div>
            
            <p :class="isDarkMode ? 'text-slate-500' : 'text-slate-400'" class="text-[10px] text-center pt-2">
                Gemini dapat menampilkan informasi yang kurang akurat. Periksa kembali dokumen resmi.
            </p>
        </div>

    </div>
</div>

<script>
    function geminiLightAssistant() {
        return {
            isOpen: window.innerWidth < 768 || {{ isset($isMobileDevice) && $isMobileDevice ? 'true' : 'false' }},
            inputPrompt: '',
            isLoading: false,
            isListening: false,
            messages: [],
            recognition: null,
            lastEntities: null,
            
            // Default to Google Gemini Light Mode (#F8F9FA Pristine Off-White)
            isDarkMode: false,

            // AI Service tidak pernah dipanggil langsung dari browser. Semua
            // lewat KDMP supaya kunci internalnya tetap di server.
            async panggilAi(url, payload) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                return res.json();
            },

            init() {
                if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    this.recognition = new SpeechRecognition();
                    this.recognition.lang = 'id-ID';
                    this.recognition.continuous = false;
                    this.recognition.interimResults = false;

                    this.recognition.onresult = (event) => {
                        const transcript = event.results[0][0].transcript;
                        this.inputPrompt = transcript;
                        this.isListening = false;
                        this.sendMessage();
                    };

                    this.recognition.onerror = () => { this.isListening = false; };
                    this.recognition.onend = () => { this.isListening = false; };
                }
            },

            toggleDarkMode() {
                this.isDarkMode = !this.isDarkMode;
            },

            toggleDrawer() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) this.scrollToBottom();
            },

            openDrawer(detail) {
                this.isOpen = true;
                if (detail && detail.voice) {
                    this.toggleVoiceInput();
                }
                this.scrollToBottom();
            },

            clearChat() {
                this.messages = [];
            },

            sendQuickPrompt(text) {
                this.inputPrompt = text;
                this.sendMessage();
            },

            toggleVoiceInput() {
                if (!this.recognition) {
                    alert('Browser tidak mendukung Web Speech Recognition.');
                    return;
                }
                if (this.isListening) {
                    this.recognition.stop();
                    this.isListening = false;
                } else {
                    this.isListening = true;
                    this.recognition.start();
                }
            },

            triggerCameraUpload() {
                document.getElementById('geminiCameraInput').click();
            },

            handleCameraUpload(e) {
                const file = e.target.files[0];
                if (file) {
                    this.isOpen = true;
                    this.messages.push({
                        sender: 'user',
                        text: `📷 [Foto Disposisi Diunggah: ${file.name}]`
                    });
                    this.inputPrompt = `Ekstrak dan analisis foto disposisi ${file.name}`;
                    this.sendMessage();
                }
            },

            async sendMessage() {
                if (!this.inputPrompt.trim() || this.isLoading) return;

                const userText = this.inputPrompt;
                this.messages.push({ sender: 'user', text: userText });
                this.inputPrompt = '';
                this.isLoading = true;
                this.scrollToBottom();

                try {
                    const data = await this.panggilAi('{{ route('ai.chat') }}', { prompt: userText });
                    this.isLoading = false;

                    if (data.status === 'success' && data.data) {
                        const botReply = data.data.message || data.data.response_text;
                        const approvalNeeded = data.data.action_required || false;
                        const jobId = data.data.job_id || null;

                        if (data.data.intent && data.data.intent.entities) {
                            this.lastEntities = data.data.intent.entities;
                        }

                        this.messages.push({
                            sender: 'bot',
                            formattedText: this.formatMarkdown(botReply),
                            approvalRequired: approvalNeeded,
                            jobId: jobId,
                            actionSummary: data.data.intent ? `Modul Intent: ${data.data.intent.intent || data.data.intent}` : 'Membutuhkan verifikasi pimpinan'
                        });
                    } else {
                        this.messages.push({
                            sender: 'bot',
                            formattedText: 'Mohon maaf, terjadi kendala saat memproses permintaan.'
                        });
                    }
                } catch (err) {
                    this.isLoading = false;
                    this.messages.push({
                        sender: 'bot',
                        formattedText: 'Gagal terhubung ke AI Service. Pastikan aplikasi <code>ai-kdmp</code> berjalan dan <code>AI_SERVICE_URL</code> sudah benar.'
                    });
                }

                this.scrollToBottom();
            },

            async handleApproval(jobId, decision) {
                try {
                    // 1. Update status in ai-kdmp microservice (lewat KDMP)
                    const json = await this.panggilAi('{{ route('ai.approve') }}', {
                        job_id: jobId,
                        decision: String(decision).toLowerCase(),
                    });

                    // 2. If approved, store record directly into KDMP travel_orders & travel_personnels database!
                    if (String(decision).toLowerCase() === 'approve') {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        
                        const dbRes = await fetch('{{ route('sppd.ai-store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                job_id: jobId,
                                personel: this.lastEntities?.personel || 'DAMIANUS, SH., M.Si',
                                tujuan: this.lastEntities?.tujuan || 'Bengkayang',
                                tanggal: this.lastEntities?.tanggal || '27 Juli 2025',
                                maksud: this.lastEntities?.maksud || 'Perjalanan Dinas'
                            })
                        });

                        const dbJson = await dbRes.json();
                        
                        if (dbJson.status === 'success') {
                            alert(`✅ ${dbJson.message}`);
                            // Auto reload or redirect to Daftar SPD page to show newly inserted record!
                            window.location.href = dbJson.redirect_url || window.location.href;
                            return;
                        }
                    }

                    alert(json.message || 'Status persetujuan draf SPD berhasil diperbarui.');
                } catch (e) {
                    alert('Gagal memproses persetujuan: ' + e.message);
                }
            },

            formatMarkdown(text) {
                return text ? text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>') : '';
            },

            scrollToBottom() {
                setTimeout(() => {
                    const container = document.getElementById('geminiChatBody');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 100);
            }
        }
    }
</script>
