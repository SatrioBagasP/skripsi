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
                        <li class="col-3 mb-2" style='margin-right: 10px' class='text-sm'> <span class='text-sm'>
                                {{ $mhs->npm }} </span> | <span class='text-sm'>{{ $mhs->name }}</span>
                            @if ($mhs->id == $data['ketua_id'])
                                <br>
                                <span class='text-sm'>
                                    (Ketua Pelaksana)
                                </span>
                            @endif
                        </li>
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
                @if ($data['approvalBtn'])
                    <div class="d-flex justify-content-end gap-2">
                        @include('Component.button', [
                            'class' => 'btn-danger mt-2',
                            'id' => 'btn-tolak',
                            'label' => 'Tolak Proposal',
                        ])
                        @include('Component.button', [
                            'class' => 'btn-primary mt-2',
                            'id' => 'btn-setujui',
                            'label' => 'Setujui Proposal',
                        ])
                    </div>
                @else
                    <div class="d-flex justify-content-end gap-2 mt-2 mb-2">
                        @if ($data['status'] == 'Accepted')
                            -- No Action Needed --
                        @elseif($data['status'] == '')
                            -- Waiting For Revision --
                        @else
                            -- Waiting For Approval --
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        (function() {
            const id = @json($data['id']);
            const approvalUrl = @json($data['approvalUrl']);
            $(document).ready(function() {
                $('#btn-setujui').click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Setujui Proposal?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Setujui!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: approvalUrl,
                                data: {
                                    id: id,
                                    approve: true,
                                },
                                success: function(response) {
                                    flasher.success(response.message)
                                },
                                error: function(xhr, status, error) {
                                    flasher.error(xhr.responseJSON.message)
                                }
                            });
                        }
                    });
                });

                $('#btn-tolak').click(function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Tolak Proposal?',
                        input: "text",
                        inputAttributes: {
                            autocapitalize: "off"
                        },
                        text: "Jika anda menolak proposal, silahkan masukkan alasan penolakan.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, tolak!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: approvalUrl,
                                data: {
                                    id: id,
                                    reason: result.value,
                                    approve: false,
                                },
                                success: function(response) {
                                    flasher.success(response.message)
                                },
                                error: function(xhr, status, error) {
                                    flasher.error(xhr.responseJSON.message)
                                }
                            });
                        }
                    });
                });
            });
        })()
    </script>
@endpush
