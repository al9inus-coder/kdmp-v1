<div x-data="aiAssistantWidget()"
     @open-ai-drawer.window="openDrawer($event.detail)"
     @open-camera-ocr.window="triggerCameraUpload()"
     class="relative">

    <!-- DESKTOP FLOATING ACTION BUTTON (FAB) -->
    <div class="hidden md:block fixed bottom-6 right-6 z-50">
        <button @click="toggleDrawer()"
                class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 via-indigo-600 to-cyan-500 text-white flex items-center justify-center shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 ring-4 ring-emerald-500/20 group">
            <i data-lucide="bot" class="w-7 h-7 group-hover:rotate-12 transition-transform"></i>
            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-slate-900"></span>
            </span>
        </button>
    </div>

    <!-- MOBILE & DESKTOP SLIDE-OVER AI CHAT DRAWER -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full"
         x-transition:enter-end="translate-y-0 md:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 md:translate-x-0"
         x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full"
         class="fixed inset-0 md:inset-auto md:bottom-6 md:right-6 z-50 w-full md:w-[420px] md:h-[620px] bg-slate-900/95 backdrop-blur-xl border border-slate-800 md:rounded-3xl shadow-2xl flex flex-col overflow-hidden"
         style="display: none;">

        <!-- Drawer Header -->
        <div class="p-4 bg-slate-800/80 border-b border-slate-700/60 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-cyan-500 flex items-center justify-center text-white font-bold shadow-md">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        KDMP AI Assistant
                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 uppercase font-semibold">Online</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Asisten Administrasi & Generator Dokumen</p>
                </div>
            </div>

            <button @click="isOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-700/60 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Chat Conversation Messages -->
        <div class="flex-1 p-4 overflow-y-auto space-y-4 text-xs" id="aiChatMessages">
            
            <!-- Welcome Bot Message -->
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                    <i data-lucide="bot" class="w-4 h-4"></i>
                </div>
                <div class="bg-slate-800/80 p-3.5 rounded-2xl rounded-tl-none border border-slate-700/60 text-slate-200 leading-relaxed max-w-[85%] space-y-2">
                    <p>Halo! Saya **KDMP AI Assistant**. Ada yang bisa saya bantu hari ini?</p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <button @click="sendQuickPrompt('Buatkan Surat Tugas Survei TPA Magmagan')" class="px-2.5 py-1 rounded-lg bg-slate-700/60 hover:bg-emerald-600/30 text-emerald-300 text-[11px] border border-emerald-500/20 text-left transition-colors">
                            📝 Buat Surat Tugas Survei
                        </button>
                        <button @click="sendQuickPrompt('Ringkas regulasi perjalanan dinas sbu 2026')" class="px-2.5 py-1 rounded-lg bg-slate-700/60 hover:bg-cyan-600/30 text-cyan-300 text-[11px] border border-cyan-500/20 text-left transition-colors">
                            🔍 Cek Tarif SBU Perjalanan Dinas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Chat Bubble Loop -->
            <template x-for="(msg, index) in messages" :key="index">
                <div class="space-y-3">
                    
                    <!-- USER MESSAGE -->
                    <div x-show="msg.sender === 'user'" class="flex justify-end">
                        <div class="bg-emerald-600 text-white p-3.5 rounded-2xl rounded-tr-none leading-relaxed max-w-[85%] shadow">
                            <span x-text="msg.text"></span>
                        </div>
                    </div>

                    <!-- BOT MESSAGE -->
                    <div x-show="msg.sender === 'bot'" class="flex gap-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                        <div class="bg-slate-800/80 p-3.5 rounded-2xl rounded-tl-none border border-slate-700/60 text-slate-200 leading-relaxed max-w-[85%] space-y-3">
                            
                            <div x-html="msg.formattedText"></div>

                            <!-- APPROVAL GATE CARD (If action required) -->
                            <template x-if="msg.approvalRequired">
                                <div class="bg-slate-900/90 p-3 rounded-xl border border-amber-500/30 space-y-2">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-amber-400 flex items-center gap-1">
                                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Perlu Persetujuan
                                        </span>
                                        <span class="text-[10px] text-slate-400" x-text="msg.jobId"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-300" x-text="msg.actionSummary"></p>
                                    
                                    <div class="flex items-center gap-2 pt-1">
                                        <button @click="handleApproval(msg.jobId, 'APPROVE')" class="flex-1 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-[11px] transition-colors">
                                            ✅ Setujui
                                        </button>
                                        <button @click="handleApproval(msg.jobId, 'REJECT')" class="px-3 py-1.5 rounded-lg bg-rose-600/30 hover:bg-rose-600/50 text-rose-300 font-semibold text-[11px] border border-rose-500/30 transition-colors">
                                            ❌ Tolak
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex gap-3" style="display: none;">
                <div class="w-7 h-7 rounded-lg bg-emerald-600/30 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30 animate-spin">
                    <i data-lucide="loader" class="w-4 h-4"></i>
                </div>
                <div class="bg-slate-800/80 px-4 py-3 rounded-2xl text-slate-400 text-xs italic">
                    AI sedang berpikir & menyusun draf...
                </div>
            </div>

        </div>

        <!-- Hidden Camera Input File for OCR -->
        <input type="file" id="cameraFileInput" accept="image/*" capture="environment" @change="handleCameraUpload($event)" class="hidden">

        <!-- Input Bar Footer -->
        <div class="p-3 bg-slate-900 border-t border-slate-800 space-y-2 shrink-0">
            
            <div class="flex items-center gap-2">
                
                <!-- Voice Microphone Toggle -->
                <button @click="toggleVoiceInput()" 
                        :class="isListening ? 'bg-rose-600 text-white animate-pulse' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                        class="p-2.5 rounded-xl border border-slate-700 transition-colors"
                        title="Perintah Suara (Voice Command)">
                    <i data-lucide="mic" class="w-4 h-4"></i>
                </button>

                <!-- Camera OCR Trigger -->
                <button @click="triggerCameraUpload()" 
                        class="p-2.5 rounded-xl bg-slate-800 text-cyan-400 hover:bg-slate-700 border border-slate-700 transition-colors"
                        title="Foto Disposisi OCR">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </button>

                <!-- Text Input -->
                <input type="text" 
                       x-model="inputPrompt" 
                       @keyup.enter="sendMessage()" 
                       placeholder="Ketik perintah atau tanyakan sesuatu..." 
                       class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500">

                <!-- Send Button -->
                <button @click="sendMessage()" 
                        class="p-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition-colors shadow">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </div>
            
            <div class="flex justify-between items-center text-[10px] text-slate-500 px-1">
                <span>Direct AI Service Connected</span>
                <span class="font-mono text-emerald-400">Secret Auth Key Active</span>
            </div>
        </div>

    </div>
</div>

<script>
    function aiAssistantWidget() {
        return {
            isOpen: false,
            inputPrompt: '',
            isLoading: false,
            isListening: false,
            messages: [],
            recognition: null,

            init() {
                // Initialize Web Speech Recognition if supported
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

                    this.recognition.onerror = () => {
                        this.isListening = false;
                    };

                    this.recognition.onend = () => {
                        this.isListening = false;
                    };
                }
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

            sendQuickPrompt(text) {
                this.inputPrompt = text;
                this.sendMessage();
            },

            toggleVoiceInput() {
                if (!this.recognition) {
                    alert('Browser Anda tidak mendukung fitur Web Speech Recognition.');
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
                document.getElementById('cameraFileInput').click();
            },

            handleCameraUpload(e) {
                const file = e.target.files[0];
                if (file) {
                    this.isOpen = true;
                    this.messages.push({
                        sender: 'user',
                        text: `📷 [Mengunggah Foto Disposisi/Dokumen: ${file.name}]`
                    });
                    this.inputPrompt = `Proses OCR disposisi dan ekstrak instruksi dari foto ${file.name}`;
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
                            actionSummary: data.data.intent ? `Modul Intent: ${data.data.intent}` : 'Permintaan membutuhkan persetujuan'
                        });
                    } else {
                        this.messages.push({
                            sender: 'bot',
                            formattedText: 'Mohon maaf, terjadi kendala saat merespons permintaan Anda.'
                        });
                    }
                } catch (err) {
                    this.isLoading = false;
                    this.messages.push({
                        sender: 'bot',
                        formattedText: 'Gagal terhubung ke AI Service microservice. Pastikan server `ai-kdmp` aktif di port 8000.'
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
                    const container = document.getElementById('aiChatMessages');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 100);
            }
        }
    }
</script>
