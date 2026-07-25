<div x-data="adaptiveAiAssistant()"
     @open-ai-drawer.window="openDrawer($event.detail)"
     @open-camera-ocr.window="triggerCameraUpload()"
     class="relative">

    <!-- DESKTOP FLOATING ACTION BUTTON (FAB) -->
    <div class="hidden md:block fixed bottom-6 right-6 z-50">
        <button @click="toggleDrawer()"
                class="w-14 h-14 rounded-full bg-[#1E1F22] hover:bg-[#2A2B2F] border border-slate-700/60 text-cyan-400 flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 ring-4 ring-cyan-500/10 group"
                title="KDMP AI Assistant">
            <svg class="w-7 h-7 text-cyan-400 group-hover:rotate-12 transition-transform duration-300" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="currentColor"/>
            </svg>
            <span class="absolute top-1 right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-cyan-400"></span>
            </span>
        </button>
    </div>

    <!-- ADAPTIVE MOBILE & DESKTOP AI CHAT CANVAS -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-98"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98"
         :class="canvasClasses"
         class="fixed inset-0 md:inset-auto md:bottom-6 md:right-6 z-50 w-full md:w-[490px] md:h-[710px] md:rounded-3xl shadow-2xl flex flex-col overflow-hidden font-sans border-0 md:border transition-colors duration-300"
         style="display: none;">

        <!-- 1. Minimalist Top Bar with Theme Switcher -->
        <div :class="headerClasses" class="h-14 px-4 flex items-center justify-between shrink-0 border-b transition-colors">
            <div class="flex items-center gap-2">
                <!-- Gemini Spark Logo -->
                <svg class="w-6 h-6 text-cyan-400" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="url(#geminiGradAdaptive)"/>
                    <defs>
                        <linearGradient id="geminiGradAdaptive" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#4285F4"/>
                            <stop offset="0.5" stop-color="#9B51E0"/>
                            <stop offset="1" stop-color="#E91E63"/>
                        </linearGradient>
                    </defs>
                </svg>

                <!-- Style Selector Dropdown Pill -->
                <div class="relative" x-data="{ openStyle: false }">
                    <button @click="openStyle = !openStyle" :class="pillBadgeClasses" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors">
                        <span x-text="currentStyleLabel"></span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                    </button>

                    <!-- Style Options Menu -->
                    <div x-show="openStyle" @click.away="openStyle = false" 
                         class="absolute left-0 mt-1.5 w-52 py-1 bg-slate-900 text-slate-200 rounded-2xl shadow-xl border border-slate-700 z-50 text-xs space-y-0.5">
                        <button @click="setStyle('gemini'); openStyle = false" class="w-full px-3 py-2 text-left hover:bg-slate-800 flex items-center justify-between font-medium">
                            <span>💎 1. Google Gemini</span>
                            <span x-show="themeStyle === 'gemini'" class="text-cyan-400">✓</span>
                        </button>
                        <button @click="setStyle('apple'); openStyle = false" class="w-full px-3 py-2 text-left hover:bg-slate-800 flex items-center justify-between font-medium">
                            <span>🍏 2. Apple Glass</span>
                            <span x-show="themeStyle === 'apple'" class="text-indigo-400">✓</span>
                        </button>
                        <button @click="setStyle('emerald'); openStyle = false" class="w-full px-3 py-2 text-left hover:bg-slate-800 flex items-center justify-between font-medium">
                            <span>🍃 3. Executive Emerald</span>
                            <span x-show="themeStyle === 'emerald'" class="text-emerald-400">✓</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <!-- Light / Dark Auto System Toggle Button -->
                <button @click="toggleDarkMode()" :class="iconBtnClasses" class="p-2 rounded-full transition-colors" title="Toggle Light/Dark Mode">
                    <i data-lucide="sun" x-show="isDarkMode" class="w-4 h-4 text-amber-400"></i>
                    <i data-lucide="moon" x-show="!isDarkMode" class="w-4 h-4 text-slate-600"></i>
                </button>

                <button @click="clearChat()" :class="iconBtnClasses" class="p-2 rounded-full transition-colors" title="Chat Baru">
                    <i data-lucide="square-pen" class="w-4.5 h-4.5"></i>
                </button>
                
                <button @click="isOpen = false" :class="iconBtnClasses" class="p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>
        </div>

        <!-- 2. Main Chat Scrollable Canvas -->
        <div class="flex-1 px-5 py-4 overflow-y-auto space-y-6 scrollbar-none" id="adaptiveChatBody">
            
            <!-- Welcome Greeting (When Chat is Empty) -->
            <div x-show="messages.length === 0" class="pt-6 pb-4 space-y-8">
                
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-[#4285F4] via-[#9B51E0] to-[#D9657B] bg-clip-text text-transparent leading-tight">
                        Halo, {{ auth()->user()->name ?? 'Operator' }}
                    </h1>
                    <h2 :class="subheadClasses" class="text-2xl font-semibold">
                        Ada yang bisa saya bantu?
                    </h2>
                </div>

                <!-- Quick Suggestion Pills -->
                <div class="space-y-3">
                    <p :class="mutedTextClasses" class="text-xs font-medium">Saran Perintah Cepat</p>
                    <div class="flex flex-col gap-2.5">
                        
                        <button @click="sendQuickPrompt('Buatkan Surat Tugas Survei Lapangan tanggal 29 Juli')" 
                                :class="suggestionCardClasses"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📝 Buatkan Draf Surat Tugas Survei Lapangan</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="sendQuickPrompt('Susun Berita Acara Serah Terima Barang dan Pekerjaan')" 
                                :class="suggestionCardClasses"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📜 Susun Berita Acara (BA) Serah Terima</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="sendQuickPrompt('Berapa tarif SBU Perjalanan Dinas 2026?')" 
                                :class="suggestionCardClasses"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📊 Cek Tarif Standar SBU Perjalanan Dinas 2026</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                        <button @click="triggerCameraUpload()" 
                                :class="suggestionCardClasses"
                                class="w-full text-left p-3.5 rounded-2xl border transition-all flex items-center justify-between group">
                            <span class="text-xs font-normal">📷 Upload Foto Lembar Disposisi (OCR Scan)</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all"></i>
                        </button>

                    </div>
                </div>
            </div>

            <!-- Dynamic Chat Conversation -->
            <template x-for="(msg, index) in messages" :key="index">
                <div class="space-y-4">
                    
                    <!-- USER BUBBLE -->
                    <div x-show="msg.sender === 'user'" class="flex justify-end">
                        <div :class="userBubbleClasses" class="px-4 py-3 rounded-2xl rounded-tr-xs text-xs leading-relaxed max-w-[85%] font-normal shadow-sm">
                            <span x-text="msg.text"></span>
                        </div>
                    </div>

                    <!-- BOT BUBBLE -->
                    <div x-show="msg.sender === 'bot'" class="flex items-start gap-3.5 py-1">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-cyan-400" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C12 7.5 7.5 12 2 12C7.5 12 12 16.5 12 22C12 16.5 16.5 12 22 12C16.5 12 12 7.5 12 2Z" fill="url(#geminiGradChatMsg)"/>
                                <defs>
                                    <linearGradient id="geminiGradChatMsg" x1="2" y1="2" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#4285F4"/>
                                        <stop offset="0.5" stop-color="#9B51E0"/>
                                        <stop offset="1" stop-color="#E91E63"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div :class="botTextClasses" class="text-xs leading-relaxed font-normal space-y-2" x-html="msg.formattedText"></div>

                            <!-- APPROVAL GATE CARD -->
                            <template x-if="msg.approvalRequired">
                                <div :class="approvalCardClasses" class="p-4 rounded-2xl border space-y-3 mt-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-amber-500 flex items-center gap-1.5">
                                            <i data-lucide="shield-alert" class="w-4 h-4"></i> Konfirmasi Tindakan
                                        </span>
                                        <span class="text-[10px] opacity-70 font-mono" x-text="msg.jobId"></span>
                                    </div>
                                    <p class="text-xs leading-normal opacity-90" x-text="msg.actionSummary"></p>
                                    
                                    <div class="flex items-center gap-2 pt-1">
                                        <button @click="handleApproval(msg.jobId, 'APPROVE')" 
                                                class="flex-1 py-2 rounded-xl bg-[#4285F4] hover:bg-blue-600 text-white font-medium text-xs transition-colors shadow">
                                            Setujui & Proses
                                        </button>
                                        <button @click="handleApproval(msg.jobId, 'REJECT')" 
                                                :class="rejectBtnClasses"
                                                class="px-4 py-2 rounded-xl font-medium text-xs border transition-colors">
                                            Tolak
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
                <div :class="mutedTextClasses" class="text-xs font-normal animate-pulse">
                    Sedang memproses...
                </div>
            </div>

        </div>

        <!-- Hidden Camera Input -->
        <input type="file" id="adaptiveCameraInput" accept="image/*" capture="environment" @change="handleCameraUpload($event)" class="hidden">

        <!-- 3. Floating Input Bar Pill -->
        <div :class="footerClasses" class="p-4 shrink-0 border-t transition-colors">
            
            <div :class="inputPillClasses" class="px-3.5 py-2.5 rounded-full border flex items-center gap-2 shadow-xl transition-all">
                
                <button @click="triggerCameraUpload()" 
                        class="p-1.5 opacity-70 hover:opacity-100 hover:text-purple-400 transition-colors"
                        title="Foto Disposisi OCR">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                </button>

                <button @click="toggleVoiceInput()" 
                        :class="isListening ? 'text-rose-500 animate-pulse' : 'opacity-70 hover:opacity-100 hover:text-cyan-400'"
                        class="p-1.5 transition-colors"
                        title="Voice Command">
                    <i data-lucide="mic" class="w-5 h-5"></i>
                </button>

                <input type="text" 
                       x-model="inputPrompt" 
                       @keyup.enter="sendMessage()" 
                       placeholder="Tanya AI..." 
                       :class="inputTextClasses"
                       class="flex-1 bg-transparent border-0 px-1 text-xs focus:outline-none focus:ring-0">

                <button @click="sendMessage()" 
                        :class="sendBtnClasses"
                        class="w-8 h-8 rounded-full flex items-center justify-center transition-all">
                    <i data-lucide="arrow-up" class="w-4 h-4"></i>
                </button>
            </div>
            
            <p :class="mutedTextClasses" class="text-[10px] text-center pt-2">
                KDMP AI Assistant • Periksa kembali informasi dokumen resmi.
            </p>
        </div>

    </div>
</div>

<script>
    function adaptiveAiAssistant() {
        return {
            isOpen: window.innerWidth < 768 || {{ isset($isMobileDevice) && $isMobileDevice ? 'true' : 'false' }},
            inputPrompt: '',
            isLoading: false,
            isListening: false,
            messages: [],
            recognition: null,
            
            // Dynamic Theme State
            themeStyle: 'gemini', // 'gemini', 'apple', 'emerald'
            isDarkMode: window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches,

            init() {
                // Listen to OS dark/light theme changes automatically
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                    this.isDarkMode = event.matches;
                });

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

            setStyle(styleName) {
                this.themeStyle = styleName;
            },

            toggleDarkMode() {
                this.isDarkMode = !this.isDarkMode;
            },

            get currentStyleLabel() {
                if (this.themeStyle === 'gemini') return '💎 Gemini';
                if (this.themeStyle === 'apple') return '🍏 Apple Glass';
                if (this.themeStyle === 'emerald') return '🍃 Executive Emerald';
                return '💎 Gemini';
            },

            // DYNAMIC DRESSING COMPUTED CLASSES
            get canvasClasses() {
                if (this.isDarkMode) {
                    if (this.themeStyle === 'apple') return 'bg-slate-950/95 backdrop-blur-2xl text-slate-100 border-slate-800';
                    if (this.themeStyle === 'emerald') return 'bg-[#061816] text-emerald-50 border-emerald-900/60';
                    return 'bg-[#131314] text-[#E3E2E6] border-slate-800'; // Default Gemini Dark
                } else {
                    if (this.themeStyle === 'apple') return 'bg-white/90 backdrop-blur-2xl text-slate-900 border-slate-200 shadow-2xl';
                    if (this.themeStyle === 'emerald') return 'bg-[#F4FBF7] text-slate-900 border-emerald-200';
                    return 'bg-[#F8F9FA] text-[#1F1F1F] border-slate-200'; // Default Gemini Light
                }
            },

            get headerClasses() {
                if (this.isDarkMode) return 'bg-[#131314]/90 border-slate-800/60';
                return 'bg-[#F8F9FA]/90 border-slate-200/80';
            },

            get footerClasses() {
                if (this.isDarkMode) return 'bg-[#131314] border-slate-800/60';
                return 'bg-[#F8F9FA] border-slate-200/80';
            },

            get pillBadgeClasses() {
                if (this.isDarkMode) return 'bg-[#1E1F22] text-slate-200 border-slate-800';
                return 'bg-white text-slate-800 border-slate-200 shadow-sm';
            },

            get iconBtnClasses() {
                if (this.isDarkMode) return 'text-slate-400 hover:text-white hover:bg-[#1E1F22]';
                return 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/60';
            },

            get subheadClasses() {
                if (this.isDarkMode) return 'text-slate-400';
                return 'text-slate-500';
            },

            get mutedTextClasses() {
                if (this.isDarkMode) return 'text-slate-500';
                return 'text-slate-400';
            },

            get suggestionCardClasses() {
                if (this.isDarkMode) return 'bg-[#1E1F22] hover:bg-[#2A2B2F] border-slate-800 text-slate-200';
                return 'bg-white hover:bg-slate-100 border-slate-200/80 text-slate-800 shadow-sm';
            },

            get userBubbleClasses() {
                if (this.isDarkMode) return 'bg-[#2A2B2F] text-[#E3E2E6]';
                return 'bg-[#E9EEF6] text-[#1F1F1F]';
            },

            get botTextClasses() {
                if (this.isDarkMode) return 'text-[#E3E2E6]';
                return 'text-[#1F1F1F]';
            },

            get approvalCardClasses() {
                if (this.isDarkMode) return 'bg-[#1E1F22] border-slate-700/60 text-slate-200';
                return 'bg-white border-slate-200 text-slate-800 shadow-sm';
            },

            get rejectBtnClasses() {
                if (this.isDarkMode) return 'bg-[#2A2B2F] text-slate-300 border-slate-700 hover:bg-slate-700';
                return 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200';
            },

            get inputPillClasses() {
                if (this.isDarkMode) return 'bg-[#1E1F22] border-slate-700/50 text-white';
                return 'bg-[#F0F4F9] border-slate-300/80 text-slate-900';
            },

            get inputTextClasses() {
                if (this.isDarkMode) return 'text-white placeholder-slate-500';
                return 'text-slate-900 placeholder-slate-400';
            },

            get sendBtnClasses() {
                if (this.inputPrompt.trim()) {
                    return this.isDarkMode ? 'bg-white text-slate-900 shadow' : 'bg-[#1F1F1F] text-white shadow';
                }
                return this.isDarkMode ? 'bg-slate-700/60 text-slate-500' : 'bg-slate-300 text-slate-500';
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
                document.getElementById('adaptiveCameraInput').click();
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
                    const res = await fetch('http://127.0.0.1:8000/api/v1/ai/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-KDMP-SECRET-KEY': 'kdmp_secret_key_2026'
                        },
                        body: JSON.stringify({
                            user_id: '{{ auth()->user()->name ?? "Operator KDMP" }}',
                            prompt: userText
                        })
                    });

                    const data = await res.json();
                    this.isLoading = false;

                    if (data.status === 'success' && data.data) {
                        const botReply = data.data.message;
                        const approvalNeeded = data.data.action_required || false;
                        const jobId = data.data.job_id || null;

                        this.messages.push({
                            sender: 'bot',
                            formattedText: this.formatMarkdown(botReply),
                            approvalRequired: approvalNeeded,
                            jobId: jobId,
                            actionSummary: data.data.intent ? `Modul Intent: ${data.data.intent}` : 'Membutuhkan verifikasi pimpinan'
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
                        formattedText: 'Gagal terhubung ke AI Service microservice. Pastikan server `ai-kdmp` berjalan di port 8000.'
                    });
                }

                this.scrollToBottom();
            },

            async handleApproval(jobId, decision) {
                try {
                    const res = await fetch('http://127.0.0.1:8000/api/v1/ai/approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-KDMP-SECRET-KEY': 'kdmp_secret_key_2026'
                        },
                        body: JSON.stringify({
                            job_id: jobId,
                            action: decision,
                            user_id: '{{ auth()->user()->name ?? "Operator KDMP" }}'
                        })
                    });

                    const json = await res.json();
                    alert(json.message || 'Status persetujuan berhasil diperbarui.');
                } catch (e) {
                    alert('Gagal memproses persetujuan: ' + e.message);
                }
            },

            formatMarkdown(text) {
                return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
            },

            scrollToBottom() {
                setTimeout(() => {
                    const container = document.getElementById('adaptiveChatBody');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 100);
            }
        }
    }
</script>
