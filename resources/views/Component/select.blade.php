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
@endphp
<select class="input-group select2 form-control" name="{{ $name }}" id="{{ $id }}">
    <option value="" selected disabled>--Pilih Data--</option>
    @foreach ($data as $item)
        <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
    @endforeach
</select>
<div class="invalid-feedback" id="{{ $name }}Error"></div>
