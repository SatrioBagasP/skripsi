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
@endphp
<select class="select2 form-control" name="{{ $name }}" id="{{ $id }}" @if ($multiple) multiple="{{ $multiple }} @endif">
    @if (!$multiple)
        <option value="" selected disabled>--Pilih Data--</option>
    @endif
    @foreach ($data as $item)
        <option value="{{ $item['value'] }}" {{ !empty($item['selected']) ? 'selected' : '' }}>{{ $item['label'] }}
        </option>
    @endforeach
</select>
<div class="invalid-feedback" id="{{ $name }}Error"></div>
