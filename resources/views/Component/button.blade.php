@php
    $class = $class ?? '';
    $label = $label ?? 'Button';
    $id = $id ?? 'btn';
    $dataButton = $dataButton ?? [];
@endphp

<button type="button" class='btn btn-primary {{ $class }}' id='{{ $id }}'
    @foreach ($dataButton as $item)
        data-{{ $item['value'] }}="{{ $item['label'] }}" @endforeach>
    {{ $label }}
</button>
