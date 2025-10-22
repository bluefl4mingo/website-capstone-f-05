@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-pine text-sm font-medium text-ink'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-ink/70 hover:text-ink hover:border-aqua';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>