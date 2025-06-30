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
                <label>Dosen Penanggung Jawab</label>
                <div class="mb-3">
                    @include('Component.select', [
                        'name' => 'dosen_id',
                        'id' => 'dosen_id',
                        'data' => $dosenOption,
                    ])
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
                <label>File Proposal</label>
                <div class="mb-2">
                    <input type="file" class="form-control" name="file" id="file" accept="application/pdf">
                    <small>pdf 2mb</small>
                    <div class="invalid-feedback" id="fileError"></div>
                </div>
                <label>Organisasi</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'user_id',
                        'id' => 'user_id',
                        'data' => $organisasiOption,
                    ])
                </div>
                <label>Mahasiswa</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'mahasiswa_id',
                        'id' => 'mahasiswa_id',
                        'data' => $mahasiswaOption,
                        'multiple' => true,
                    ])
                </div>
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
                        'label' => 'Ajukan Proposal',
                    ])
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script>
        (function() {

            let dataSet = {
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

                // const end = flatpickr("#end_date", {
                //     enableTime: true,
                //     dateFormat: "Y-m-d H:i",
                //     minDate: "today",
                //     time_24hr: true,
                // });

                // const start = flatpickr("#start_date", {
                //     enableTime: true,
                //     dateFormat: "Y-m-d H:i",
                //     minDate: "today",
                //     time_24hr: true,
                //     onChange: (selectedDates) => {
                //         if (selectedDates.length > 0) {
                //             // Atur minimal tanggal jam di end picker
                //             end.set("minDate", selectedDates[0]);
                //             // (Opsional) Jika current end < new start, bersihkan nilai end
                //             if (end.selectedDates.length > 0 && end.selectedDates[0] <
                //                 selectedDates[0]) {
                //                 end.clear();
                //             }
                //         }
                //     }
                // });


                $(document).on('click', '.edit', function() {
                    resetDataSet();
                    $('#btn-tambah').hide();
                    $('#btn-edit').show();
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    $('#sidebar-form').addClass('show');
                    $('#name').val(data[index].name).trigger('change');
                    $('#npm').val(data[index].npm).trigger('change');
                    $('#jurusan_id').val(data[index].jurusan_id).trigger('change');
                    $('#status').prop('checked', data[index].status == 1).trigger('change');
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


                $('#is_harian').change(function(e) {
                    e.preventDefault();
                    $('#start_date').val('');
                    $('#end_date').val('');
                    $('#range_date').val('');
                    dataSet.start_date = null
                    dataSet.end_date = null
                    dataSet.range_date = null

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

                    $.ajax({
                        type: "POST",
                        url: "{{ route('proposal.store') }}",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {

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
                            // button.attr('disabled', false);
                        }
                    });
                });

                // $('#btn-edit').click(function(e) {
                //     e.preventDefault();
                //     submitForm($(this), 'update')
                // });
            });
        })()
    </script>
@endpush
