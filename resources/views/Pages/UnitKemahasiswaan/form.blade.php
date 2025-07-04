@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Unit Kemahasiswaan')

@section('contentSidebarForm')
    <label>Unit Kemahasiswaan</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
    </div>
    <label>No Hp</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nomor Handphone Penanggung Jawab" name="no_hp" id="no_hp">
        <div class="invalid-feedback" id="no_hpError"></div>
    </div>
    <label>Jurusan</label>
    <div class="mb-3">
        @include('Component.select', [
            'name' => 'jurusan_id',
            'id' => 'jurusan_id',
            'data' => $optionJurusan,
        ])
    </div>
    <label>Image</label>
    <div class="mb-3">
        <input type="file" class="form-control" placeholder="Nama Unit Kemahasiswaan" name="image" id="image" accept="image/*">
        <div class="invalid-feedback" id="imageError"></div>
    </div>
    <img id="imagePreview" src="" alt="Preview Image" class="d-none" style="max-width: 150px; border-radius: 5px; border: 1px solid #ddd; padding: 5px;">
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
@endsection
