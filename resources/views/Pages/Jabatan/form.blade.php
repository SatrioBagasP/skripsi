@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jabatan')

@section('contentSidebarForm')
    <label>Jabatan</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Jabatan" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
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
