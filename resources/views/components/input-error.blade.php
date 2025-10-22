@props(['messages' => []])

@if ($messages)
  <ul {{ $attributes->merge(['class' => 'mt-1 text-sm text-red-600']) }}>
    @foreach ((array) $messages as $message)
      <li>{{ $message }}</li>
    @endforeach
  </ul>
@endif