@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jurusan')

@section('contentSidebarForm')
    <form action="">
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
