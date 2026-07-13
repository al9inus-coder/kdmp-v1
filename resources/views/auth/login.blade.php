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
    <div class="w-full max-w-[380px] px-8 py-12 bg-white/80 backdrop-blur-xl rounded-[32px] shadow-2xl border border-white/50">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold tracking-tighter uppercase mb-2 text-slate-900">KDMP</h1>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.3em]">Kelola Digital Manajemen Pengadaan</p>
        </div>

        <!-- Form Login -->
        <form action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email -->
            <div class="mb-5">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" 
                    class="w-full px-4 py-3 rounded-2xl bg-white/50 border border-slate-200 focus:outline-none focus:border-blue-500 transition" required autofocus>
                @error('email')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-8">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Password</label>
                <input type="password" name="password" 
                    class="w-full px-4 py-3 rounded-2xl bg-white/50 border border-slate-200 focus:outline-none focus:border-blue-500 transition" required>
                @error('password')
                    <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-4 bg-slate-900 text-white rounded-2xl font-semibold hover:bg-black transition-all shadow-lg active:scale-95">
                Masuk Sistem
            </button>
        </form>

        <!-- Footer -->
        <p class="mt-8 text-center text-[9px] text-slate-400 uppercase tracking-widest font-medium">
            © {{ date('Y') }} Sistem Pengadaan
        </p>

    </div>

</body>
</html>
