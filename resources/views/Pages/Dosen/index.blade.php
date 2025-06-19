@extends('Layout.layout')

@section('pages', 'Dosen');

@section('title', config('app.name') . ' | Dosen');

@section('content')

    <div>
        @include('Component.button', [
            'class' => 'fixed-plugin-button',
            'label' => 'Tambah Data',
        ])
    </div>

    @include('Pages.Dosen.form')

@endsection
