@props(['id' => null, 'maxWidth' => null])

<x-modal-layout :id="$id" :maxWidth="$maxWidth">
  <div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-soft']) }}>
    {{ $slot }}
  </div>
</x-modal-layout>