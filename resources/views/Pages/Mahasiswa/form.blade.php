@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Mahasiswa')

@section('contentSidebarForm')
    <form action="">
        <label>Mahasiswa</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="name" id="name">
            <div class="invalid-feedback" id="nameError"></div>
        </div>
        <label>NPM</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="NPM" name="npm" id="npm">
            <div class="invalid-feedback" id="npmError"></div>
        </div>
        <label>Jurusan</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'jurusan_id',
                'id' => 'jurusan_id',
            ])
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="status" name="status" checked="">
            <label class="form-check-label">Status</label>
        </div>
        <div class="d-flex justify-content-end">
            @include('Component.button', [
                'class' => 'bg-gradient-info mt-4 mb-0',
                'label' => 'Tambah',
                'id' => 'btn-tambah',
            ])
        </div>

    </form>
@endsection
