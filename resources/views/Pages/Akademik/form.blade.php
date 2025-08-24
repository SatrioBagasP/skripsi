@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Akademik')

@section('contentSidebarForm')
    <label>Akademik</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Akademik" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
    </div>
    <label>No HP</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nomor Hp layanan" name="no_hp" id="no_hp">
        <div class="invalid-feedback" id="no_hpError"></div>
    </div>
    <label>Ketua</label>
    @include('Component.select', [
        'name' => 'ketua_id',
        'id' => 'ketua_id',
        'data' => $dataDosen,
    ])
    <div class="form-check form-switch mt-3">
        <input class="form-check-input" type="checkbox" id="status" name="status">
        <label class="form-check-label">Status</label>
    </div>
    <div class="d-flex justify-content-end">
        @include('Component.button', [
            'class' => 'bg-gradient-info mt-4 mb-0 hidden',
            'label' => 'Tambah',
            'id' => 'btn-tambah',
        ])

        @include('Component.button', [
            'class' => 'bg-gradient-info mt-4 mb-0 hidden',
            'label' => 'Simpan',
            'id' => 'btn-edit',
        ])
    </div>
@endsection
