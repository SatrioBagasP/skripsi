@php
    $id = $id ?? 'select';
    $name = $name ?? 'select';
    $data = $data ?? [
        [
            'value' => '1',
            'label' => 'Dummy1',
        ],
        [
            'value' => '2',
            'label' => 'Dummy2',
        ],
    ];
    $multiple = $multiple ?? false;
    $placeholder = $placeholder ?? '--Pilih Data--';
@endphp
<select class="select2 form-control" name="{{ $name }}" id="{{ $id }}"
    @if ($multiple) multiple="{{ $multiple }} @endif">
    @foreach ($data as $item)
        <option value="{{ $item['value'] }}" {{ !empty($item['selected']) ? 'selected' : '' }}>{{ $item['label'] }}
        </option>
    @endforeach
</select>
<div class="invalid-feedback" id="{{ $name }}Error"></div>
