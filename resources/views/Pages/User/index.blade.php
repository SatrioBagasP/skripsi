@extends('Layout.layout')

@section('pages', 'User')

@section('title', config('app.name') . ' | User')

@section('content')

    <div>
        @include('Component.button', [
            'class' => 'fixed-plugin-button',
            'label' => 'Tambah Data',
        ])
    </div>

    @include('Pages.User.form')

@endsection
