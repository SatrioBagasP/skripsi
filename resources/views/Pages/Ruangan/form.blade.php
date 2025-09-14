@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Ruangan')

@section('contentSidebarForm')
    <label>Ruangan</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Ruangan" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
    </div>
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
