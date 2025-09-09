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
        <div class="row">
            <div class="col-md-12">
                <label>Nama Proposal</label>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Nama Proposal" value="{{ $data['name'] }}">
                    <div class="invalid-feedback" id="nameError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <label>File Laporan Kegiatan</label>
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
                </div>
            </div>
            <div class="col-md-6">
                <label>File Bukti Kehadiran</label>
                @if (isset($data) && $data['file_bukti_kehadiran'] != null)
                    <small>
                        (<a href="{{ $data['file_bukti_kehadiran'] }}" target="_blank"
                            class="text-primary text-decoration-underline">view file</a>)
                    </small>
                @endif
                <div class="mb-2">
                    <input type="file" class="form-control" name="file_bukti_kehadiran" id="file_bukti_kehadiran"
                        accept="image/jpeg, image/png, image/jpg">
                    <span class="rules">
                        <small>
                            Hanya menerima file JPG, PNG, JPEG <br>
                            Maksimal ukuran: <strong>2 MB</strong>
                        </small>
                    </span>
                    <div class="invalid-feedback" id="file_bukti_kehadiranError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <label>File Bukti Dukung</label>

                <div class="bukti-item input-group mb-2">
                    <input type="file" class="form-control file_bukti_dukung mb-0" data-index="0"
                        accept="image/jpeg, image/png, image/jpg">
                    <button type="button" class="btn btn-outline-primary addBukti mb-0" id="addBukti">
                        + Tambah Bukti Dukung
                    </button>
                </div>
                <div id="buktiDukungWrapper"> </div>
                <span class="rules">
                    <small>
                        Hanya menerima file JPG, PNG, JPEG <br>
                        Maksimal ukuran: <strong>2 MB</strong>
                    </small>
                </span>

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

        </div>
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
                    <div class="mb-2 bukti-item input-group">
                        <input type="file" class="form-control file_bukti_dukung"
                            data-index="${buktiIndex}"
                            accept="image/jpeg, image/png, image/jpg">
                        <button type="button" class="btn btn-outline-danger removeBukti mb-0">-</button>
                    </div>
                `;
                $('#buktiDukungWrapper').append(newItem);
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
                    dataSet.file_bukti_dukung[index] = this.files[0]; // replace sesuai index
                });


                $('#addBukti').click(function(e) {
                    e.preventDefault();
                    createBuktiDukung();
                });

                $(document).on('click', '.removeBukti', function() {
                    let parent = $(this).closest('.bukti-item');
                    let index = parent.find('.file_bukti_dukung').data('index');
                    dataSet.file_bukti_dukung.splice(index, 1);
                    parent.remove();
                });

                $('#btn-submit').click(function(e) {
                    e.preventDefault();
                    e.preventDefault();
                    const button = $(this);
                    button.attr('disabled', true);

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
            // let firstRender = true;
            // let edit = @json($edit ?? false);
            // let dataSet = {
            //     selected_mahasiswa: [],
            // };
            // if (edit) {
            //     dataSet = @json($data ?? null);
            //     Object.entries(dataSet).forEach(function([key, value]) {
            //         if (key != 'file_url') {
            //             $(`#${key}`).val(value);
            //         }
            //     });
            //     $('#is_harian').prop('checked', dataSet.is_harian == 1);
            //     if (dataSet.is_harian == 1) {
            //         $('#yes-harian').show();
            //         $('#not-harian').hide();
            //     } else {
            //         $('#yes-harian').hide();
            //         $('#not-harian').show();
            //     }



            // }
            // $(document).ready(function() {
            //     $('.flatpickr-range').flatpickr({
            //         mode: "range",
            //         minDate: "today",
            //         dateFormat: "Y-m-d",
            //     });
            //     $('.flatpickr').flatpickr({
            //         enableTime: true,
            //         dateFormat: "Y-m-d H:i",
            //         minDate: "today",
            //         time_24hr: true,
            //     });

            //     $('input[id], select[id], textarea[id], checkbox[id], file[id]').on('input change', function() {
            //         const key = $(this).attr('id');
            //         const type = $(this).attr('type');

            //         if (type === 'checkbox') {
            //             dataSet[key] = $(this).prop('checked');
            //         } else if (type === 'file') {
            //             dataSet[key] = this.files[0];
            //         } else {
            //             dataSet[key] = $(this).val();
            //         }
            //     });
            //     $('#unit_id').change(function(e) {
            //         e.preventDefault();
            //         $('#ketua_id').attr('disabled', true);
            //         $('#dosen_id').attr('disabled', true);
            //         $('#mahasiswa_id').attr('disabled', true);
            //         if ($(this).val() === '') {
            //             return;
            //         }
            //         appendKetuaPelaksana($(this).val());
            //     });

            //     $('#unit_id').trigger('change');


            //     function appendKetuaPelaksana(organisasiId) {
            //         $('#ketua_ids').empty();
            //         $('#mahasiswa_id').empty();
            //         $('#dosen_id').empty();
            //         $.ajax({
            //             type: "GET",
            //             url: "{{ route('proposal.getOption') }}",
            //             data: {
            //                 id: organisasiId,
            //             },
            //             success: function(response) {
            //                 var ketuaOptions = '';
            //                 var dosenOptions = '';
            //                 var mahasiswaOptions = '';
            //                 if (response.data_mahasiswa.length > 0) {
            //                     ketuaOptions +=
            //                         '<option value="" selected disabled>--Pilih Data--</option>';
            //                     $.each(response.data_mahasiswa, function(index, value) {
            //                         ketuaOptions +=
            //                             `<option value="${value.value}"}>${value.label}</option>`;
            //                     });
            //                 } else {
            //                     ketuaOptions +=
            //                         '<option value="" disabled selected>Tidak Ada Data Mahasiswa Hubungi Admin Aplikasi!</option>';
            //                 }

            //                 if (response.data_dosen.length > 0) {
            //                     dosenOptions +=
            //                         '<option value="" selected disabled>--Pilih Data--</option>';
            //                     $.each(response.data_dosen, function(index, value) {
            //                         dosenOptions +=
            //                             `<option value="${value.value}">${value.label}</option>`;
            //                     });
            //                 } else {
            //                     dosenOptions +=
            //                         '<option value="" disabled selected>Tidak Ada Data Mahasiswa Hubungi Admin Aplikasi!</option>';
            //                 }

            //                 if (response.data_mahasiswa.length > 0) {
            //                     mahasiswaOptions +=
            //                         '<option value="" disabled>--Pilih Data--</option>';
            //                     $.each(response.data_mahasiswa, function(index, value) {
            //                         let isSelected = dataSet.selected_mahasiswa.some(v =>
            //                             v == value.value)
            //                         mahasiswaOptions +=
            //                             `<option value="${value.value}">${value.label}</option>`;
            //                     });
            //                 } else {
            //                     mahasiswaOptions +=
            //                         '<option value="" disabled selected>Tidak Ada Data Mahasiswa Hubungi Admin Aplikasi!</option>';
            //                 }

            //                 $('#ketua_ids').removeAttr('disabled');
            //                 $('#mahasiswa_id').removeAttr('disabled');
            //                 $('#dosen_id').removeAttr('disabled');
            //                 $('#ketua_ids').html(ketuaOptions);
            //                 $('#dosen_id').html(dosenOptions);
            //                 $('#mahasiswa_id').html(mahasiswaOptions);
            //                 $('#ketua_ids').val(dataSet.ketua_ids).trigger('change');
            //                 $('#dosen_id').val(dataSet.dosen_id).trigger('change');
            //                 $('#mahasiswa_id').val(dataSet.selected_mahasiswa).trigger('change');

            //             },
            //             error: function(xhr, status, error) {
            //                 var err = xhr.responseJSON.errors;
            //                 flasher.error(xhr.responseJSON.message);
            //             }
            //         });
            //     }

            //     $('#is_harian').change(function(e) {
            //         e.preventDefault();

            //         let isHarian = $(this).prop('checked')
            //         if (isHarian) {
            //             $('#yes-harian').show();
            //             $('#not-harian').hide();
            //         } else {
            //             $('#yes-harian').hide();
            //             $('#not-harian').show();
            //         }
            //     });

            //     $('#btn-submit').click(function(e) {
            //         e.preventDefault();
            //         const button = $(this);
            //         const formData = new FormData();
            //         for (const key in dataSet) {
            //             if (dataSet[key] !== null && key !== 'mahasiswa_id') {
            //                 formData.append(key, dataSet[key]);
            //             }
            //         }

            //         if (Array.isArray(dataSet.mahasiswa_id)) {
            //             dataSet.mahasiswa_id.forEach((id) => {
            //                 formData.append('mahasiswa_id[]', id);
            //             });
            //         }
            //         button.attr('disabled', true);
            //         $.ajax({
            //             type: "POST",
            //             url: edit ? '{{ route('proposal.update') }}' :
            //                 '{{ route('proposal.store') }}',
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             success: function(response) {
            //                 localStorage.setItem('flash_message', response
            //                     .message);
            //                 localStorage.setItem('flash_type',
            //                     'success');
            //                 window.location.href = '{{ route('proposal.index') }}';

            //             },
            //             error: function(xhr, status, error) {
            //                 var err = xhr.responseJSON.errors;
            //                 $('.invalid-feedback').text('').hide();
            //                 $('.form-control').removeClass('is-invalid');
            //                 $.each(err, function(key, value) {
            //                     $('#' + key + 'Error').text(value).show();
            //                     $('#' + key).addClass('is-invalid');
            //                 });
            //                 flasher.error(xhr.responseJSON.message);
            //                 button.attr('disabled', false);
            //             }
            //         });
            //     });

            // });
        })()
    </script>
@endpush
