@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jurusan')

@section('contentSidebarForm')
    <form action="">
        <label>User</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'user_id',
                'id' => 'user_id',
                'data' => $userAbleOption,
            ])
        </div>
        <label>Name</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Username" name="name" id="name">
            <div class="invalid-feedback" id="nameError"></div>
        </div>
        <label>Email</label>
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Email" name="email" id="email">
            <div class="invalid-feedback" id="emailError"></div>
        </div>
        <label>Password</label>
        <div class="mb-3">
            <input type="password" class="form-control" placeholder="Your Secret.." name="password" id="password">
            <div class="invalid-feedback" id="passwordError"></div>
        </div>
        <label>Role</label>
        <div class="mb-3">
            @include('Component.select', [
                'name' => 'role_id',
                'id' => 'role_id',
                'data' => $roleOption,
            ])
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
