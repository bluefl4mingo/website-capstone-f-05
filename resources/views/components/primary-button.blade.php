@props(['type' => 'button'])
<button {{ $attributes->merge(['type' => $type, 'class' => 'btn-primary']) }}>
  {{ $slot }}
</button>