@php
    $class = $class ?? '';
    $label = $label ?? 'Button';
    $id = $id ?? 'btn';
@endphp

<button class='btn {{ $class }}' id='{{ $id }}'>
    {{ $label }}
</button>