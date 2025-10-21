@props(['value'])
<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink/80']) }}>
  {{ $value ?? $slot }}
</label>