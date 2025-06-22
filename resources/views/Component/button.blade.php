@php
    $class = $class ?? '';
    $label = $label ?? 'Button';
    $id = $id ?? 'btn';
@endphp

<button class='btn btn-primary {{ $class }}' id='{{ $id }}'>
    {{ $label }}
</button>