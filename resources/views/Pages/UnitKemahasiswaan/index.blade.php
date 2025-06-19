@extends('Layout.layout')

@section('pages', 'Unit Kemahasiswaan');

@section('title', config('app.name') . ' | Unit Kemahasiswaan');

@section('content')

    <div>
        @include('Component.button', [
            'class' => 'fixed-plugin-button',
            'label' => 'Tambah Data',
        ])
    </div>

    @include('Pages.UnitKemahasiswaan.form')

@endsection
