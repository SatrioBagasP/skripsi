@extends('Layout.layout')

@section('pages', 'Validasi Proposal')

@section('title', config('app.name') . ' | Validasi Proposal')

@section('content')

    <div class="card px-4 py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-3">Detail Data Proposal {{ $data['no_proposal'] }} </h5>
            <span class="badge bg-info">{{ $data['status'] }}</span>
        </div>

        <div class="row">
            <div class="col-md-4 mb-2">
                Nama Proposal
            </div>
            <div class="col-md-8 mb-2">
                <input type="text" class="form-control" placeholder="Nama Proposal" name="name" id="name"
                    value="{{ $data['name'] }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                Organisasi
            </div>
            <div class="col-md-8 mb-2">
                <input type="text" class="form-control" placeholder="Nama Proposal" name="organisasi" id="organisasi"
                    value="{{ $data['organisasi'] }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                Dosen Penangung Jawab
            </div>
            <div class="col-md-8 mb-2">
                <input type="text" class="form-control" placeholder="Nama Proposal" name="dosen" id="dosen"
                    value="{{ $data['dosen'] }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                Deskripsi
            </div>
            <div class="col-md-8 mb-2">
                <textarea class="form-control" name="desc" id="desc" cols="30" rows="10" readonly>{{ $data['desc'] }}</textarea>
            </div>
            <div class="col-md-4 mb-2">
                File
            </div>
            <div class="col-md-8 mb-2">
                (<a href="{{ $data['file_url'] }}" target="_blank" class="text-primary text-decoration-underline">view
                    file</a>)
            </div>
            <div class="col-md-4 mb-2">
                List Mahasiswa
            </div>
            <div class="col-md-8 mb-2">
                <ul class="row list">
                    @foreach ($data['mahasiswa'] as $mhs)
                        <li class="col-2 mb-2">{{ $mhs->name }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-4 mb-2">
                Tanggal
            </div>
            <div class="col-md-4 mb-2">
                <input class="form-control flatpickr" type="text" id="start_date" name="start_date"
                    value="{{ $data['start_date'] }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                <input class="form-control flatpickr" type="text" id="start_date" name="start_date"
                    value="{{ $data['end_date'] }}" readonly>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="d-flex justify-content-end gap-2">
                    @include('Component.button', [
                        'class' => 'btn-danger mt-2',
                        'id' => 'btn-submit',
                        'label' => 'Tolak Proposal',
                    ])
                    @include('Component.button', [
                        'class' => 'btn-primary mt-2',
                        'id' => 'btn-submit',
                        'label' => 'Setujui Proposal',
                    ])
                </div>
            </div>
        </div>
    </div>

@endsection
