<x-guest-layout>
    <div class="w-full max-w-md mx-auto mt-10 mb-8">
        {{-- Logos --}}
        <div class="flex items-center justify-center gap-5 mb-3">
            <img src="{{ asset('images/logo_vredeburg.avif') }}" alt="Vredeburg" class="h-24 md:h-26">
        </div>

        {{-- Card --}}
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-xl ring-1 ring-black/5 p-7 md:p-8">
            <h1 class="text-center text-2xl font-semibold text-ink">Masuk ke Dashboard</h1>
            <p class="mt-2 text-center text-sm text-ink/60">
                Silakan login ke akun admin Anda.
            </p>

            {{-- Session Status --}}
            <x-auth-session-status class="mt-4" :status="session('status')" />

            {{-- Standard POST form --}}
            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
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
                        autocomplete="username"
                        class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                               text-ink focus:border-pine focus:ring-2 focus:ring-pine/40"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-ink" />
                    <x-text-input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                               text-ink focus:border-pine focus:ring-2 focus:ring-pine/40"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-sm underline text-pine hover:text-ink">
                            {{ __('Lupa kata sandi Anda?') }}
                        </a>
                    @endif

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-aqua px-6 py-2.5
                                   text-white font-medium shadow-sm hover:opacity-90 transition">
                        {{ __('Masuk') }}
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink/70">
            Belum punya akun?
            <a href="{{ route('register') }}" class="underline text-pine hover:text-ink">
                Daftar
            </a>
        </p>
    </div>
</x-guest-layout>