@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jurusan')

@section('contentSidebarForm')
    <label>Jurusan</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Jurusan" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
    </div>
    <label>Kode</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="kode" name="kode" id="kode">
        <div class="invalid-feedback" id="kodeError"></div>
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
