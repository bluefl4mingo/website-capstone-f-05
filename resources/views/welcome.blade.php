<x-guest-layout>
  <section class="bg-light rounded-[35px] shadow-lg mx-2 md:mx-8 lg:mx-50 xl:mx-30 2xl:mx-30 p-6 md:p-10 lg:p-50">
    {{-- Header row inside the white container --}}
    <div class="flex items-center justify-between mb-10 mx-[-15px] mt-[-15px]">
      <div class="flex items-center gap-1">
        <img src="{{ asset('images/logo_vredeburg.avif') }}" alt="Logo Museum" class="h-16">
        <img src="{{ asset('images/logo_dteti.png') }}" alt="Logo Partner" class="h-14">
      </div>
    </div>

    {{-- Hero grid --}}
    <div class="grid md:grid-cols-2 gap-10 items-center px-6 mb-10">
      {{-- Left: text content --}}
      <div>
        <h1 class="text-4xl font-semibold text-ink mb-5">Website Admin Vredeburg</h1>
        <p class="text-ink/70 leading-relaxed mb-6">
          Vestibulum molestie nisl nec nunc viverra efficitur. 
          Efficiunt ornare aliquam. Proin at est lectus, risus quis sagittis porta.
        </p>

        <ul class="space-y-2 text-ink/80">
          <li class="flex items-center gap-3">
            <span class="size-2 rounded-full bg-pink-400"></span>
            Fusce luctus blandit nisi.
          </li>
          <li class="flex items-center gap-3">
            <span class="size-2 rounded-full bg-pink-400"></span>
            Ut imperdiet dui at tincidunt mattis.
          </li>
        </ul>

        <div class="mt-8 flex gap-4">
            {{-- Masuk button --}}
            <a href="{{ route('login') }}" 
                class="bg-mint text-ink font-medium px-8 py-3 rounded-full shadow hover:opacity-90 transition">
                Masuk
            </a>

            {{-- Daftar button --}}
            <a href="{{ route('register') }}" 
                class="bg-pine text-white font-medium px-8 py-3 rounded-full shadow hover:opacity-90 transition">
                Daftar
            </a>
            </div>
        </div>

      {{-- Right: hero image --}}
      <div class="relative flex justify-center mt-[-20px]">
        <img src="{{ asset('images/hero.png') }}" alt="Dashboard Hero" 
             class="w-full max-w-2xl md:max-w-xl h-auto">
      </div>
    </div>
  </section>
</x-guest-layout>