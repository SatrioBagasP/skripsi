@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jurusan')

@section('contentSidebarForm')
    <form action="">
        <label>User</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'jurusan_id',
                'id' => 'jurusan_id',
            ])
        </div>
        <label>Name</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Username" name="name" id="name">
            <div class="invalid-feedback" id="nameError"></div>
        </div>
        <label>Email</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="kode" name="email" id="email">
            <div class="invalid-feedback" id="emailError"></div>
        </div>
        <label>Password</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="kode" name="password" id="password">
            <div class="invalid-feedback" id="passwordError"></div>
        </div>
        <label>Role</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'role_id',
                'id' => 'role_id',
            ])
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
