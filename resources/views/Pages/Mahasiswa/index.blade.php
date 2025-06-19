@extends('Layout.layout')

@section('pages', 'Mahasiswa');

@section('title', config('app.name') . ' | Mahasiswa');

@section('content')

    <div>
        @include('Component.button', [
            'class' => 'fixed-plugin-button',
            'label' => 'Tambah Data',
        ])
    </div>

    @include('Pages.Mahasiswa.form')

@endsection
