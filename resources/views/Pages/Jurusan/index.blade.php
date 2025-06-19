@extends('Layout.layout')

@section('pages', 'Jurusan');

@section('title', config('app.name') . ' | Jurusan');

@section('content')

    <div>
        @include('Component.button', [
            'class' => 'fixed-plugin-button',
            'label' => 'Tambah Data',
        ])
    </div>

    @include('Pages.Jurusan.form')

@endsection
