@php
    $edit = $edit ?? false;
@endphp
@extends('Layout.layout')

@section('pages', 'Tambah Data Proposal')

@section('title', config('app.name') . ' | Mahasiswa')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css" />
    <style>
        .alasan-box {
            border: 1px solid #e3342f;
            /* warna merah */
            background-color: #fdecea;
            /* merah muda lembut */
            color: #611a15;
            /* teks merah tua */
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alasan-box h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #b91c1c;
        }

        .alasan-box p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
@endpush

@section('content')

    <div class="card px-4 py-2">
        @if (isset($data['alasan_tolak']) && $data['alasan_tolak'])
            <div class="alasan-box">
                <h4>Proposal Ditolak</h4>
                <p>{{ $data['alasan_tolak'] }}</p>
            </div>
        @endif
        <div class="row" id='formWrapper'>
            <div class="col-md-12">
                <label>Nama Proposal</label>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Nama Proposal" value="{{ $data['name'] }}">
                    <div class="invalid-feedback" id="nameError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative file-upload">
                    <div class="d-flex align-items-center mb-0">
                        <h6 class=" fw-semibold">
                            <label>File Laporan Kegiatan</label>
                            @if (isset($data) && $data['file'] != null)
                                <small>
                                    <a href="{{ $data['file'] }}" target="_blank"
                                        class="text-primary text-decoration-underline"><i
                                            class="bi bi-file-earmark"></i></i>File</a>
                                </small>
                            @endif
                        </h6>
                    </div>

                    <input type="file" id="file" class="file-input" accept=".pdf">
                    <label for="file" class="file-label">
                        <i class="bi bi-file-earmark-pdf file-icon"></i>
                        <span class="fw-medium">Upload File</span>
                        <small class="text-muted mt-2">Format: PDF (Maks 2MB)</small>

                        <img class="file-preview" alt="Pratinjau dokumen">
                        <div class="file-name"></div>

                        <div class="file-overlay">
                            <i class="bi bi-cloud-arrow-up mb-2" style="font-size: 2rem;"></i>
                            <span>Klik untuk upload file</span>
                        </div>
                    </label>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="upload-status">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        <span>File berhasil diupload</span>
                    </div>
                </div>
                {{-- <label>File Laporan Kegiatan</label>
                @if (isset($data) && $data['file'] != null)
                    <small>
                        (<a href="{{ $data['file'] }}" target="_blank" class="text-primary text-decoration-underline">view
                            file</a>)
                    </small>
                @endif
                <div class="mb-2">
                    <input type="file" class="form-control" name="file" id="file" accept="application/pdf">
                    <span class="rules">
                        <small>
                            Hanya menerima file PDF <br>
                            Maksimal ukuran: <strong>2 MB</strong>
                        </small>
                    </span>
                    <div class="invalid-feedback" id="fileError"></div>
                </div> --}}
            </div>
            <div class="col-md-6">
                <div class="position-relative file-upload">
                    <div class="d-flex align-items-center mb-0">
                        <h6 class=" fw-semibold">
                            <label>File Bukti Kehadiran</label>
                            @if (isset($data) && $data['file_bukti_kehadiran'] != null)
                                <small>
                                    <a href="{{ $data['file_bukti_kehadiran'] }}" target="_blank"
                                        class="text-primary text-decoration-underline"><i
                                            class="bi bi-file-earmark"></i></i>File</a>
                                </small>
                            @endif
                        </h6>
                    </div>

                    <input type="file" id="file_bukti_kehadiran" class="file-input" accept=".pdf">
                    <label for="file_bukti_kehadiran" class="file-label">
                        <i class="bi bi-file-earmark-pdf file-icon"></i>
                        <span class="fw-medium">Upload File</span>
                        <small class="text-muted mt-2">Format: PDF (Maks 2MB)</small>

                        <img class="file-preview" alt="Pratinjau dokumen">
                        <div class="file-name"></div>

                        <div class="file-overlay">
                            <i class="bi bi-cloud-arrow-up mb-2" style="font-size: 2rem;"></i>
                            <span>Klik untuk upload file</span>
                        </div>
                    </label>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="upload-status">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        <span>File berhasil diupload</span>
                    </div>
                </div>
                {{-- <div class="position-relative file-upload">
                    <div class="d-flex align-items-center mb-0">
                        <h6 class=" fw-semibold">
                            <label>File Laporan Kegiatan</label>
                            @if (isset($data) && $data['file_bukti_kehadiran'] != null)
                                <small>
                                    <a href="{{ $data['file_bukti_kehadiran'] }}" target="_blank"
                                        class="text-primary text-decoration-underline"><i
                                            class="bi bi-file-earmark"></i></i>File</a>
                                </small>
                            @endif
                        </h6>
                    </div>

                    <input type="file" id="fileBuktiKehadiran" class="file-input" accept=".png, .jpg, .jpeg">
                    <label for="fileBuktiKehadiran" class="file-label">
                        <i class="bi bi-file-earmark-arrow-up file-icon"></i>
                        <span class="fw-medium">Upload File</span>
                        <small class="text-muted mt-2">Format: PNG, JPG, JPEG (Maks 2MB)</small>

                        <img class="file-preview" alt="Pratinjau dokumen">
                        <div class="file-name"></div>

                        <div class="file-overlay">
                            <i class="bi bi-cloud-arrow-up mb-2" style="font-size: 2rem;"></i>
                            <span>Klik untuk upload file</span>
                        </div>
                    </label>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="upload-status">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        <span>File berhasil diupload</span>
                    </div>
                </div> --}}
            </div>
            <div class="col-md-6">
                <div class="position-relative file-upload">
                    <div class="d-flex align-items-center justify-content-between mb-2 mt-2">
                        <h6 class="fw-semibold">
                            <label>File Laporan Kegiatan</label>
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm addBukti mb-0" id="addBukti">
                            + Tambah Bukti Dukung
                        </button>
                    </div>

                    <input type="file" id="fileBuktiDukung" class="file-input file_bukti_dukung" data-index='0' accept=".png, .jpg, .jpeg">
                    <label for="fileBuktiDukung" class="file-label">
                        <i class="bi bi-file-earmark-arrow-up file-icon"></i>
                        <span class="fw-medium">Upload File</span>
                        <small class="text-muted mt-2">Format: PNG, JPG, JPEG (Maks 2MB)</small>

                        <img class="file-preview" alt="Pratinjau dokumen">
                        <div class="file-name"></div>

                        <div class="file-overlay">
                            <i class="bi bi-cloud-arrow-up mb-2" style="font-size: 2rem;"></i>
                            <span>Klik untuk upload file</span>
                        </div>
                    </label>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="upload-status">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                        <span>File berhasil diupload</span>
                    </div>
                </div>
                {{-- <label>File Bukti Dukung</label>
                <button type="button" class="btn btn-outline-primary addBukti mb-0" id="addBukti">
                    + Tambah Bukti Dukung
                </button>

                <div class="bukti-item input-group mb-2">
                    <input type="file" class="form-control file_bukti_dukung mb-0" data-index="0"
                        accept="image/jpeg, image/png, image/jpg">
                    <button type="button" class="btn btn-outline-primary addBukti mb-0" id="addBukti">
                        + Tambah Bukti Dukung
                    </button>
                </div> --}}
            </div>
        </div>
        @if (isset($data) && $data['bukti_dukung'] != [])
            <div class="row">
                <h5 class="card-title">List File Bukti Dukung</h5>
                @foreach ($data['bukti_dukung'] as $index => $file)
                    <div class="mb-5 col-md-4 col-sm-6 d-flex flex-column align-items-center data-item"
                        data-index="{{ $index }}">
                        <a data-fancybox="gallery" data-src="{{ $file['file'] }}" data-caption=''>
                            <img src="{{ $file['file'] }}" width="200" height="150" alt="" />
                        </a>
                        <br>
                        <button type='button' class="btn btn-danger delete-image">Delete</button>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    @include('Component.button', [
                        'class' => 'fixed-plugin-button mt-2',
                        'id' => 'btn-submit',
                        'label' => $edit == true ? 'Rubah Data' : 'Tambah Data',
                    ])
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        $('.select2').select2({
            placeholder: '-- Pilih Data --'
        });
        (function() {
            Fancybox.bind("[data-fancybox]", {
                // Your custom options
            });
            let edit = @json($edit ?? false);
            let data = {};
            if (edit) {
                data = @json($data ?? null);
            }

            dataSet = {
                file_bukti_dukung: [],
            }

            let buktiIndex = 1;

            function createBuktiDukung() {
                let newItem = `
                    <div class='col-md-6'>
                        <div class="position-relative file-upload">
                            <div class="d-flex align-items-center justify-content-between mb-2 mt-2">
                                <h6 class="fw-semibold">

                                </h6>
                                <button type="button" class="btn btn-outline-danger removeBukti btn-sm mb-0" id="addBukti">
                                    - Hapus bukti dukung
                                </button>
                            </div>

                            <input type="file" id="fileBuktiKehadiran${buktiIndex}" data-index="${buktiIndex}" class="file-input file_bukti_dukung" accept=".png, .jpg, .jpeg">
                            <label for="fileBuktiKehadiran${buktiIndex}" class="file-label">
                                <i class="bi bi-file-earmark-arrow-up file-icon"></i>
                                <span class="fw-medium">Upload File</span>
                                <small class="text-muted mt-2">Format: PNG, JPG, JPEG (Maks 2MB)</small>

                                <img class="file-preview" alt="Pratinjau dokumen">
                                <div class="file-name"></div>

                                <div class="file-overlay">
                                    <i class="bi bi-cloud-arrow-up mb-2" style="font-size: 2rem;"></i>
                                    <span>Klik untuk upload file</span>
                                </div>
                            </label>

                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="upload-status">
                                <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                <span>File berhasil diupload</span>
                            </div>
                        </div>
                    </div>
                `;
                $('#formWrapper').append(newItem);
                buktiIndex++;
            }

            $(document).ready(function() {

                const message = localStorage.getItem('flash_message');
                const type = localStorage.getItem('flash_type');

                if (message && type && typeof flasher !== 'undefined') {
                    flasher[type](message);
                    localStorage.removeItem('flash_message');
                    localStorage.removeItem('flash_type');
                }

                $("#saveButton").on("click", function() {
                    let button = $(this);
                    button.html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...'
                    ).prop("disabled", true);

                    setTimeout(function() {
                        alert("Dokumen berhasil disimpan!");
                        button.html('<i class="bi bi-check-circle me-2"></i>Simpan Dokumen')
                            .prop(
                                "disabled", false);

                        // let successAlert = $(`
                    //     <div class="alert alert-success alert-dismissible fade show">
                    //         <strong>Berhasil!</strong> Dokumen Anda telah berhasil disimpan dan akan segera diproses.
                    //         <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    //     </div>
                    // `);
                        // $(".container").prepend(successAlert);
                    }, 2000);
                });


                $('#file').change(function(e) {
                    e.preventDefault();
                    dataSet.file = this.files[0];
                });

                $('#file_bukti_kehadiran').change(function(e) {
                    e.preventDefault();
                    dataSet.file_bukti_kehadiran = this.files[0];
                });

                $(document).on('change', '.file_bukti_dukung', function(e) {
                    e.preventDefault();
                    let index = $(this).data('index');
                    dataSet.file_bukti_dukung[index] = this.files[0];
                });

                $('#addBukti').click(function(e) {
                    e.preventDefault();
                    createBuktiDukung();
                });

                $(document).on('click', '.removeBukti', function() {
                    let parent = $(this).closest('.d-flex').closest('.file-upload');
                    let index = parent.find('.file_bukti_dukung').data('index');
                    dataSet.file_bukti_dukung.splice(index, 1);
                    parent.closest('.col-md-6').remove();
                });

                $('#btn-submit').click(function(e) {
                    e.preventDefault();
                    e.preventDefault();
                    const button = $(this);
                    // button.attr('disabled', true);

                    let formData = new FormData();

                    for (const key in dataSet) {
                        if (key !== 'file_bukti_dukung') {
                            formData.append(key, dataSet[key]);
                        }
                    }
                    dataSet.file_bukti_dukung.forEach((file, index) => {
                        formData.append(`file_bukti_dukung[${index}]`, file);
                    });
                    formData.append('id', data.id);
                    $.ajax({
                        type: "POST",
                        url: "{{ route('laporan-kegiatan.update') }}",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            localStorage.setItem('flash_message', response
                                .message);
                            localStorage.setItem('flash_type',
                                'success');
                            window.location.href = '{{ route('laporan-kegiatan.index') }}';
                        },
                        error: function(xhr, status, error) {
                            var err = xhr.responseJSON.errors;
                            $('.invalid-feedback').text('').hide();
                            $('.form-control').removeClass('is-invalid');
                            flasher.error(xhr.responseJSON.message);
                            $.each(err, function(key, value) {
                                const baseKey = key.split('.')[0];
                                $('#' + baseKey + 'Error').text(value[0]).show();
                                $('#' + baseKey).addClass('is-invalid');
                            });
                            button.attr('disabled', false);
                        }
                    });

                });

                $(document).on('click', '.delete-image', function(e) {
                    e.preventDefault();
                    const button = $(this);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    button.attr('disabled', true);
                    $.ajax({
                        type: "POST",
                        url: '{{ route('laporan-kegiatan.delete-image') }}',
                        data: {
                            id: data.bukti_dukung[index].id,
                            _method: 'delete',
                        },
                        success: function(response) {
                            localStorage.setItem('flash_message', response.message);
                            localStorage.setItem('flash_type', 'success');
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            flasher.error(xhr.responseJSON.message);
                            button.attr('disabled', false);
                        }
                    });
                });

            });
        })()
    </script>
@endpush
