@extends('Layout.layout')

@section('pages', 'Validasi Proposal')

@section('title', config('app.name') . ' | Validasi Proposal')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css" />
    <style>
        .fancy-box {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')

    <div class="card px-4 py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-3">Detail Laporan Kegiatan {{ $data['name'] }} </h5>
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
            {{-- <div class="col-md-4 mb-2">
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
            </div> --}}
            <div class="col-md-4 mb-2">
                File
            </div>
            <div class="col-md-8 mb-2">
                @if ($data['file'] != null)
                    <a href="{{ $data['file'] }}" target="_blank" class="text-primary text-decoration-underline"><i
                            class="bi bi-file-earmark"></i></i>File</a>
                @else
                    == TIDAK FILE LAPORAN KEGIATAN YANG DI UPLOAD ==
                @endif

            </div>
            <div class="col-md-4 mb-2">
                File Bukti Kehadiran
            </div>
            <div class="col-md-8 mb-2">
                @if ($data['file_bukti_kehadiran'] != null)
                    <a href="{{ $data['file_bukti_kehadiran'] }}" target="_blank"
                        class="text-primary text-decoration-underline"><i class="bi bi-file-earmark"></i></i>File</a>
                @else
                    == TIDAK FILE BUKTI KEHADIRAN YANG DI UPLOAD ==
                @endif

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
                File Bukti Dukung
            </div>
            <div class="col-md-8 mb-2">

                @forelse ($data['bukti_dukung'] as $item)
                    <a data-fancybox="gallery" data-src="{{ $item['file'] }}" class='fancy-box'>
                        <img src="{{ $item['file'] }}" width="200" height="150" alt="" />
                    </a>
                @empty
                    == TIDAK ADA BUKTI DUKUNG YANG DI UPLOAD ==
                @endforelse


                {{-- <a data-fancybox="gallery" data-src="https://lipsum.app/id/3/1600x1200">
                    <img src="https://lipsum.app/id/3/200x150" width="200" height="150" alt="" />
                </a>

                <a data-fancybox="gallery" data-src="https://lipsum.app/id/4/1600x1200">
                    <img src="https://lipsum.app/id/4/200x150" width="200" height="150" alt="" />
                </a> --}}
            </div>
            {{-- <a href="{{ $data['file_bukti_kehadiran'] }}" target="_blank"
                    class="text-primary text-decoration-underline"><i class="bi bi-file-earmark"></i></i>File</a> --}}
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                @if ($data['approvalBtn'])
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('approval-laporan-kegiatan.index') }}" class="btn fixed-plugin-button mt-2 btn-secondary">
                            Kembali
                        </a>
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
                        @elseif($data['status'] == 'Rejected')
                            -- Waiting For Revision --
                        @else
                            -- Waiting For Approval --
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>

    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        (function() {
            Fancybox.bind("[data-fancybox]", {
                // Your custom options
            });
            const id = @json($data['id']);
            const approvalUrl = @json($data['approvalUrl']);
            $(document).ready(function() {

                function setLoadingState(isLoading) {
                    const buttons = $('#btn-setujui, #btn-tolak');

                    if (isLoading) {
                        buttons.attr('disabled', true).each(function() {
                            const btn = $(this);
                            if (!btn.data('original-text')) {
                                btn.data('original-text', btn.html());
                            }
                            btn.html(
                                `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                 Loading...`
                            );
                        });
                    } else {
                        buttons.removeAttr('disabled').each(function() {
                            const btn = $(this);
                            if (btn.data('original-text')) {
                                btn.html(btn.data('original-text'));
                                btn.removeData('original-text');
                            }
                        });
                    }
                }

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
                            setLoadingState(true);
                            $.ajax({
                                type: "POST",
                                url: approvalUrl,
                                contentType: "application/json",
                                data: JSON.stringify({
                                    id: id,
                                    approve: true,
                                }),
                                success: function(response) {
                                    localStorage.setItem('flash_message', response
                                        .message);
                                    localStorage.setItem('flash_type',
                                        'success');
                                    window.location.href =
                                        '{{ route('approval-laporan-kegiatan.index') }}';
                                },
                                error: function(xhr, status, error) {
                                    flasher.error(xhr.responseJSON.message)
                                    setLoadingState(false);
                                }
                            });
                        }
                    });
                });

                $('#btn-tolak').click(function(e) {
                    e.preventDefault();
                    const button = $(this);
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
                            setLoadingState(true);
                            $.ajax({
                                type: "POST",
                                url: approvalUrl,
                                contentType: "application/json",
                                data: JSON.stringify({
                                    id: id,
                                    approve: false,
                                    reason: result.value,
                                }),
                                success: function(response) {
                                    localStorage.setItem('flash_message', response
                                        .message);
                                    localStorage.setItem('flash_type',
                                        'success');

                                    window.location.href =
                                        '{{ route('approval-proposal.index') }}';
                                },
                                error: function(xhr, status, error) {
                                    flasher.error(xhr.responseJSON.message)
                                    setLoadingState(false);
                                }
                            });
                        }
                    });
                });
            });
        })()
    </script>
@endpush
