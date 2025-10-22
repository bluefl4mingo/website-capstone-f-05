@props(['align' => 'right', 'width' => '48'])
@php
$alignmentClasses = match($align) {
  'left' => 'origin-top-left left-0',
  'top' => 'origin-top',
  default => 'origin-top-right right-0',
};
$widthClass = match($width) {
  '48' => 'w-48',
  default => $width,
};
@endphp

<div class="relative">
  <div {{ $attributes->merge(['class' => '']) }}>
    {{ $trigger }}
  </div>

  <div class="absolute z-50 mt-2 {{ $widthClass }} rounded-2xl shadow-soft {{ $alignmentClasses }}" style="display: none;" x-data x-show="open" x-transition>
    <div class="rounded-2xl ring-1 ring-mist/30 bg-white p-1">
      {{ $content }}
    </div>
  </div>
</div>