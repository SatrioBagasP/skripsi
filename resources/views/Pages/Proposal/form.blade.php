@php
    $edit = $edit ?? false;
@endphp
@extends('Layout.layout')

@section('pages', 'Tambah Data Proposal')

@section('title', config('app.name') . ' | Mahasiswa')

@section('content')

    <div class="card px-4 py-2">
        <div class="row">
            <div class="col-md-6">
                <label>Nama</label>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Nama Proposal" name="name" id="name">
                    <div class="invalid-feedback" id="nameError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <label>File Proposal</label>
                @if (isset($data))
                    <small>
                        (<a href="{{ $data['file_url'] }}" target="_blank"
                            class="text-primary text-decoration-underline">view file</a>)
                    </small>
                @endif
                <div class="mb-2">
                    <input type="file" class="form-control" name="file" id="file" accept="application/pdf">
                    <small>pdf 2mb</small>
                    <div class="invalid-feedback" id="fileError"></div>
                </div>

            </div>
            <div class="col-md-6">
                <label>Deskripsi</label>
                <div class="mb-3">
                    <textarea class="form-control" name="desc" id="desc" cols="30" rows="10"></textarea>
                    <div class="invalid-feedback" id="descError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <label>Organisasi</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'user_id',
                        'id' => 'user_id',
                        'data' => $organisasiOption,
                    ])
                </div>
                <label>Ketua Pelaksana</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'ketua_id',
                        'id' => 'ketua_id',
                        'data' => $ketuaOption,
                    ])
                </div>
                <label>Dosen Penanggung Jawab</label>
                <div class="mb-3">
                    @include('Component.select', [
                        'name' => 'dosen_id',
                        'id' => 'dosen_id',
                        'data' => $dosenOption,
                    ])
                </div>
                <label>Mahasiswa</label>
                <div class="">
                    @include('Component.select', [
                        'name' => 'mahasiswa_id',
                        'id' => 'mahasiswa_id',
                        'data' => $mahasiswaOption,
                        'multiple' => true,
                    ])
                </div>
                <small class='mb-2'>Ketua pelaksana tidak perlu dipilih, karena akan terpilih otomatis oleh sistem</small>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_harian" name="is_harian">
                    <label class="form-check-label">Harian?</label>
                </div>
            </div>
            <div class="col-md-6">
                <div id="not-harian">
                    <div class="row d-flex">
                        <div class="col-md-6">
                            <label>Start Date</label>
                            <input class="form-control flatpickr" type="text" id="start_date" name="start_date">
                            <div class="invalid-feedback" id="start_dateError"></div>
                        </div>
                        <div class="col-md-6">
                            <label>End Date</label>
                            <input class="form-control flatpickr" type="text" id="end_date" name="end_date">
                            <div class="invalid-feedback" id="end_dateError"></div>
                        </div>
                    </div>
                </div>
                <div id="yes-harian" style="display: none;">
                    <label>Start - End Date</label>
                    <input class="form-control flatpickr-range" type="text" id="range_date" name="range_date">
                    <div class="invalid-feedback" id="range_dataError"></div>
                </div>
            </div>
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
    <script>
        (function() {
            let edit = @json($edit ?? false);
            let dataSet = {};
            if (edit) {
                dataSet = @json($data ?? null);

                Object.entries(dataSet).forEach(function([key, value]) {
                    if (key != 'file_url') {
                        $(`#${key}`).val(value);
                    }
                });
                $('#is_harian').prop('checked', dataSet.is_harian == 1);
                if (dataSet.is_harian == 1) {
                    $('#yes-harian').show();
                    $('#not-harian').hide();
                } else {
                    $('#yes-harian').hide();
                    $('#not-harian').show();
                }

            } else {
                dataSet = {
                    id: null,
                    name: null,
                    dosen_id: null,
                    desc: null,
                    file: null,
                    user_id: null,
                    mahasiswa_id: [],
                    is_harian: false,
                    start_date: null,
                    end_date: null,
                    range_date: null,
                }
            }

            $(document).ready(function() {
                $('.flatpickr-range').flatpickr({
                    mode: "range",
                    minDate: "today",
                    dateFormat: "Y-m-d",
                });
                $('.flatpickr').flatpickr({
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    time_24hr: true,
                });

                $('input[id], select[id], textarea[id], checkbox[id], file[id]').on('input change', function() {
                    const key = $(this).attr('id');
                    const type = $(this).attr('type');

                    if (type === 'checkbox') {
                        dataSet[key] = $(this).prop('checked');
                    } else if (type === 'file') {
                        dataSet[key] = this.files[0];
                    } else {
                        dataSet[key] = $(this).val();
                    }
                });

                // $('#ketua_id').change(function(e) {
                //     e.preventDefault();
                //     dataSet.dosen_id = null;
                //     let ketuaId = $(this).val();
                //     if (!hasJurusan) {
                //         $.ajax({
                //             type: "GET",
                //             url: "{{ route('proposal.getDosen') }}",
                //             data: {
                //                 ketuaId: ketuaId,
                //             },
                //             success: function(response) {
                //                 var options = '';
                //                 if (response.data.length > 0) {
                //                     options +=
                //                         '<option value="" disabled selected>---Pilih Dosen Penanggung Jawab---</option>';
                //                     $.each(response.data, function(index, value) {
                //                         options +=
                //                             `<option value="${value.value}" ${dataSet.dosen_id == value.value ? 'selected' : ''}>${value.label}</option>`;
                //                     });
                //                     console.log(options);
                //                 } else {
                //                     options +=
                //                         '<option value="" disabled selected>Tidak Ada Data Dosen Hubungi Admin Aplikasi!</option>';
                //                 }

                //                 $('#dosen_id').html(options);

                //             }
                //         });
                //     }
                // });

                if (edit) {
                    $('.select2').trigger('change');
                }


                $('#is_harian').change(function(e) {
                    e.preventDefault();

                    let isHarian = $(this).prop('checked')
                    if (isHarian) {
                        $('#yes-harian').show();
                        $('#not-harian').hide();
                    } else {
                        $('#yes-harian').hide();
                        $('#not-harian').show();
                    }
                });

                $('#btn-submit').click(function(e) {
                    e.preventDefault();
                    const button = $(this);
                    const formData = new FormData();
                    for (const key in dataSet) {
                        if (dataSet[key] !== null && key !== 'mahasiswa_id') {
                            formData.append(key, dataSet[key]);
                        }
                    }

                    if (Array.isArray(dataSet.mahasiswa_id)) {
                        dataSet.mahasiswa_id.forEach((id) => {
                            formData.append('mahasiswa_id[]', id);
                        });
                    }
                    // button.attr('disabled', true);
                    $.ajax({
                        type: "POST",
                        url: edit ? '{{ route('proposal.update') }}' :
                            '{{ route('proposal.store') }}',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            localStorage.setItem('flash_message', response
                                .message);
                            localStorage.setItem('flash_type',
                                'success');
                            window.location.href = '{{ route('proposal.index') }}';

                        },
                        error: function(xhr, status, error) {
                            var err = xhr.responseJSON.errors;
                            $('.invalid-feedback').text('').hide();
                            $('.form-control').removeClass('is-invalid');
                            $.each(err, function(key, value) {
                                $('#' + key + 'Error').text(value).show();
                                $('#' + key).addClass('is-invalid');
                            });
                            flasher.error(xhr.responseJSON.message);
                            button.attr('disabled', false);
                        }
                    });
                });

            });
        })()
    </script>
@endpush
