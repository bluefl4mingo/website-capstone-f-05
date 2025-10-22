<x-guest-layout>
    <div class="w-full max-w-md mx-auto mt-10 mb-8">
        {{-- Logos --}}
        <div class="flex items-center justify-center gap-5 mb-3">
            <img src="{{ asset('images/logo_vredeburg.avif') }}" alt="Vredeburg" class="h-24 md:h-28">
        </div>

        {{-- Card --}}
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-xl ring-1 ring-black/5 p-7 md:p-8">
            <h1 class="text-center text-2xl font-semibold text-ink">Lupa Kata Sandi</h1>
            <p class="mt-2 text-center text-sm text-ink/60">
                Masukkan email terdaftar Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
            </p>

            {{-- Session Status --}}
            <x-auth-session-status class="mt-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-ink" />
                    <x-text-input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                               text-ink focus:border-pine focus:ring-2 focus:ring-pine/40"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('login') }}"
                       class="text-sm underline text-pine hover:text-ink">
                        Kembali ke Masuk
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-aqua px-6 py-2.5
                                   text-white font-medium shadow-sm hover:opacity-90 transition">
                        Kirim Link Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>