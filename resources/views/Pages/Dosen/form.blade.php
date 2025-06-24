@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Unit Kemahasiswaan')

@section('contentSidebarForm')
    <form action="">
        <label>Dosen</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="name" id="name">
            <div class="invalid-feedback" id="nameError"></div>
        </div>
        <label>No Hp</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="No Handphone" name="no_hp" id="no_hp">
            <div class="invalid-feedback" id="no_hpError"></div>
        </div>
        <label>NIP</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="NIP" name="nip" id="nip">
            <div class="invalid-feedback" id="nipError"></div>
        </div>
        <label>Alamat</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Alamat" name="alamat" id="alamat">
            <div class="invalid-feedback" id="alamatError"></div>
        </div>
        <label>Jurusan</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'jurusan_id',
                'id' => 'jurusan_id',
                'data' => $optionJurusan,
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
            @include('Component.button', [
                'class' => 'bg-gradient-info mt-4 mb-0',
                'label' => 'Simpan',
                'id' => 'btn-edit',
            ])
        </div>

    </form>
@endsection
