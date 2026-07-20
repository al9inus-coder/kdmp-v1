<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | KDMP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        body { font-family: 'Inter', sans-serif; }

        @keyframes wave-animation {
            0% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.05) rotate(0.5deg); }
            100% { transform: scale(1) rotate(0deg); }
        }

        .animate-flag {
            animation: wave-animation 12s ease-in-out infinite;
        }

        .glass-card {
            position: relative;
            background: linear-gradient(160deg, rgba(255,255,255,0.28) 0%, rgba(255,255,255,0.12) 45%, rgba(255,255,255,0.06) 100%);
            -webkit-backdrop-filter: blur(24px) saturate(160%) brightness(1.08);
            backdrop-filter: blur(24px) saturate(160%) brightness(1.08);
            border-radius: 32px;
            box-shadow:
                0 25px 50px -12px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.55),
                inset 0 -1px 0 rgba(255,255,255,0.08),
                inset 1px 0 0 rgba(255,255,255,0.18),
                inset -1px 0 0 rgba(255,255,255,0.10);
        }

        /* gradient border ring */
        .glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(160deg, rgba(255,255,255,0.7), rgba(255,255,255,0.12) 40%, rgba(255,255,255,0.05) 60%, rgba(255,255,255,0.35));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        /* diagonal light streak reflection */
        .glass-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,0.18) 42%, rgba(255,255,255,0.05) 50%, transparent 60%);
            pointer-events: none;
        }

        .glass-input {
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.30);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            color: #fff;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.10);
        }
        .glass-input:focus {
            background: rgba(255,255,255,0.20);
            border-color: rgba(147,197,253,0.8);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden p-4">

    {{-- Background Flag --}}
    <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
        <img src="{{ asset('/images/bendera.jpg') }}"
             class="w-full h-full object-cover opacity-55 animate-flag"
             alt="Indonesia Flag">
    </div>
    <div class="fixed inset-0 bg-slate-950/65 -z-10 pointer-events-none"></div>

    <!-- Login Card -->
    <div class="glass-card w-full max-w-[380px] px-8 py-12">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold tracking-tighter uppercase mb-2 text-white drop-shadow-md">KDMP</h1>
            <p class="text-[9px] font-bold text-white/70 uppercase tracking-[0.3em]">Kelola Digital Manajemen Pengadaan</p>
        </div>

        <!-- Form Login -->
        <form action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email -->
            <div class="mb-5">
                <label class="block text-[10px] font-bold text-white/70 uppercase tracking-widest mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="glass-input w-full px-4 py-3 rounded-2xl focus:outline-none transition" required autofocus>
                @error('email')
                    <p class="text-red-300 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-8">
                <label class="block text-[10px] font-bold text-white/70 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password"
                    class="glass-input w-full px-4 py-3 rounded-2xl focus:outline-none transition" required>
                @error('password')
                    <p class="text-red-300 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="relative w-full py-4 bg-slate-900/90 text-white rounded-2xl font-semibold hover:bg-black transition-all shadow-lg active:scale-95">
                Masuk Sistem
            </button>
        </form>

        <!-- Footer -->
        <p class="mt-8 text-center text-[9px] text-white/50 uppercase tracking-widest font-medium">
            © {{ date('Y') }} Sistem Pengadaan
        </p>

    </div>

</body>
</html>
