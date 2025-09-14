@php
    $edit = $edit ?? false;
@endphp
@extends('Layout.layout')

@section('pages', 'Tambah Data Proposal')

@section('title', config('app.name') . ' | Mahasiswa')

@push('css')
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
            <div class="col-md-6">
                <label>Nama</label>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Nama Proposal" name="name" id="name">
                    <div class="invalid-feedback" id="nameError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <label>Organisasi</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'unit_id',
                        'id' => 'unit_id',
                        'placeholder' => '-- Pilih Organisasi --',
                        'data' => $organisasiOption,
                    ])
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-2">
                    <div class="position-relative file-upload">
                        <div class="d-flex align-items-center mb-0">
                            <h6 class=" fw-semibold">
                                <label>File Proposal</label>
                                @if (isset($data))
                                    <small>
                                        <a href="{{ $data['file_url'] }}" target="_blank"
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
                <label>Ketua Pelaksana</label>
                <div class="mb-2">
                    @include('Component.select', [
                        'name' => 'ketua_ids',
                        'id' => 'ketua_ids',
                        'disabled' => true,
                        'data' => [],
                    ])
                </div>
            </div>
            <div class="col-md-6">
                <label>Dosen Penanggung Jawab</label>
                <div class="mb-3">
                    @include('Component.select', [
                        'name' => 'dosen_id',
                        'id' => 'dosen_id',
                        'disabled' => true,
                        'data' => [],
                    ])
                </div>
            </div>
            <div class="col-md-6">
                <label>Mahasiswa</label>
                <div class="">
                    @include('Component.select', [
                        'name' => 'mahasiswa_id',
                        'id' => 'mahasiswa_id',
                        'disabled' => true,
                        'data' => [],
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
                    <div class="invalid-feedback" id="range_dateError"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="need_ruangan" name="need_ruangan">
                    <label class="form-check-label">Butuh Ruangan?</label>
                </div>
                <small class="text-muted d-block mt-1">
                    Pilih jadwal terlebih dahulu untuk menampilkan ruangan.
                </small>
                <div class="mb-3" id="input-ruangan">
                    @include('Component.select', [
                        'name' => 'ruangan',
                        'id' => 'ruangan',
                        'disabled' => true,
                        'data' => [],
                        'multiple' => true,
                    ])
                    <div id="ruangan-loading" class="text-muted small mt-1" style="display:none;">
                        <span class="spinner-border spinner-border-sm"></span> Loading data...
                    </div>
                    <small class="text-muted d-block">
                        NB: Ruangan dengan Proposal yang masih berstatus <b>Draft</b> atau <b>Ditolak</b> tetap bisa
                        dipinjam organisasi lain.
                    </small>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('proposal.index') }}" class="btn fixed-plugin-button mt-2 btn-secondary">
                        Kembali
                    </a>
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
        $('.select2').select2();
        (function() {
            let firstRender = true;
            let edit = @json($edit ?? false);
            let dataSet = {
                selected_mahasiswa: [],
                ruangan: [],
            };
            if (edit) {
                dataSet = @json($data ?? null);
                Object.entries(dataSet).forEach(function([key, value]) {
                    if (key != 'file_url') {
                        $(`#${key}`).val(value);
                    }
                });
                $('#is_harian').prop('checked', dataSet.is_harian == 1);
                $('#need_ruangan').prop('checked', dataSet.need_ruangan == 1);
                if (dataSet.is_harian == 1) {
                    $('#yes-harian').show();
                    $('#not-harian').hide();
                } else {
                    $('#yes-harian').hide();
                    $('#not-harian').show();
                }

                if (dataSet.need_ruangan == 1) {
                    $('#ruangan').removeAttr('disabled');
                } else {
                    $('#ruangan').attr('disabled', true);
                }

            }
            $(document).ready(function() {
                $('.flatpickr-range').flatpickr({
                    mode: "range",
                    minDate: "today",
                    dateFormat: "Y-m-d",
                    onClose: function(selectedDates, dateStr, instance) {
                        $('#need_ruangan').trigger('change');
                    }
                });
                $('.flatpickr').flatpickr({
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    time_24hr: true,
                    onClose: function(selectedDates, dateStr, instance) {
                        $('#need_ruangan').trigger('change');
                    }
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
                $('#unit_id').change(function(e) {
                    e.preventDefault();
                    $('#ketua_id').attr('disabled', true);
                    $('#dosen_id').attr('disabled', true);
                    $('#mahasiswa_id').attr('disabled', true);
                    if ($(this).val() === '') {
                        return;
                    }
                    appendKetuaPelaksana($(this).val());
                });

                $('#unit_id').trigger('change');

                function appendKetuaPelaksana(organisasiId) {
                    $('#ketua_ids').empty();
                    $('#mahasiswa_id').empty();
                    $('#dosen_id').empty();
                    $.ajax({
                        type: "GET",
                        url: "{{ route('proposal.getOption') }}",
                        data: {
                            id: organisasiId,
                        },
                        success: function(response) {
                            var ketuaOptions = '';
                            var dosenOptions = '';
                            var mahasiswaOptions = '';
                            if (response.data_mahasiswa.length > 0) {
                                ketuaOptions +=
                                    '<option value="" selected disabled>--Pilih Data--</option>';
                                $.each(response.data_mahasiswa, function(index, value) {
                                    ketuaOptions +=
                                        `<option value="${value.value}"}>${value.label}</option>`;
                                });
                            } else {
                                ketuaOptions +=
                                    '<option disabled selected>Tidak Ada Data Mahasiswa Hubungi Admin Aplikasi!</option>';
                            }

                            if (response.data_dosen.length > 0) {
                                dosenOptions +=
                                    '<option value="" selected disabled>--Pilih Data--</option>';
                                $.each(response.data_dosen, function(index, value) {
                                    dosenOptions +=
                                        `<option value="${value.value}">${value.label}</option>`;
                                });
                            } else {
                                dosenOptions +=
                                    '<option disabled selected>Tidak Ada Data Dosen Hubungi Admin Aplikasi!</option>';
                            }

                            if (response.data_mahasiswa.length > 0) {
                                mahasiswaOptions +=
                                    '<option disabled>--Pilih Data--</option>';
                                $.each(response.data_mahasiswa, function(index, value) {
                                    let isSelected = dataSet.selected_mahasiswa.some(v =>
                                        v == value.value)
                                    mahasiswaOptions +=
                                        `<option value="${value.value}">${value.label}</option>`;
                                });
                            } else {
                                mahasiswaOptions +=
                                    '<option disabled >Tidak Ada Data Mahasiswa Hubungi Admin Aplikasi!</option>';
                            }

                            $('#ketua_ids').removeAttr('disabled');
                            $('#mahasiswa_id').removeAttr('disabled');
                            $('#dosen_id').removeAttr('disabled');
                            $('#ketua_ids').html(ketuaOptions);
                            $('#dosen_id').html(dosenOptions);
                            $('#mahasiswa_id').html(mahasiswaOptions);
                            $('#ketua_ids').val(dataSet.ketua_ids).trigger('change');
                            $('#dosen_id').val(dataSet.dosen_id).trigger('change');
                            $('#mahasiswa_id').val(dataSet.selected_mahasiswa).trigger('change');

                        },
                        error: function(xhr, status, error) {
                            var err = xhr.responseJSON.errors;
                            flasher.error(xhr.responseJSON.message);
                        }
                    });
                }

                function getRuanganOption() {
                    $('#ruangan').empty();
                    $('#ruangan-loading').show();
                    let isHarian = $('#is_harian').prop('checked')
                    $.ajax({
                        type: "GET",
                        url: "{{ route('proposal.getRuanganOption') }}",
                        data: {
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            range_date: $('#range_date').val(),
                            is_harian: isHarian,
                            id: dataSet.id,
                        },
                        success: function(response) {
                            var ruanganOptions = '';
                            if (response.data.length > 0) {
                                ruanganOptions +=
                                    '<option disabled>--Pilih Data--</option>';
                                $.each(response.data, function(index, value) {
                                    ruanganOptions +=
                                        `<option value="${value.value}"}>${value.label}</option>`;
                                });
                            } else {
                                ruanganOptions +=
                                    '<option disabled >Tidak Ada Data Ruangan Silahkan Pilih Jadwal Lain!</option>';
                            }
                            $('#ruangan').html(ruanganOptions);
                            $('#ruangan-loading').hide();
                            $('#ruangan').val(dataSet.ruangan).trigger('change');
                            firstRender = false;

                        },
                        error: function(xhr, status, error) {
                            $('#ruangan-loading').hide();
                            flasher.error(xhr.responseJSON.message);
                        }
                    });
                }

                $('#is_harian').change(function(e) {
                    e.preventDefault();

                    let isHarian = $(this).prop('checked')
                    if (isHarian) {
                        $('#yes-harian').show();
                        $('#start_date').val('').trigger('change');
                        $('#end_date').val('').trigger('change');
                        $('#not-harian').hide();
                    } else {
                        $('#yes-harian').hide();
                        $('#not-harian').show();
                        $('#range_date').val('').trigger('change');
                    }
                    $('#need_ruangan').trigger('change');
                });

                $('#need_ruangan').change(function() {
                    let needRuangan = $(this).prop('checked');
                    let startDate = $('#start_date').val();
                    let endDate = $('#end_date').val();
                    let rangeDate = $('#range_date').val();
                    if (!firstRender) {
                        $('#ruangan').val('').trigger('change');
                    }


                    if (needRuangan && ((startDate != '' && endDate != '') || rangeDate != '')) {
                        $('#ruangan').removeAttr('disabled');
                        getRuanganOption();
                    } else {
                        $('#ruangan').attr('disabled', true);
                    }
                });

                $('#need_ruangan').trigger('change');

                $('#btn-submit').click(function(e) {
                    e.preventDefault();
                    const button = $(this);
                    const formData = new FormData();
                    for (const key in dataSet) {
                        // mengecualikan mahasiswa_id dan ruangan, krn dia array, agar nanti respon yagn diterima di backend itu array
                        if (dataSet[key] !== '' && (key !== 'mahasiswa_id' || key !== 'ruangan')) {
                            formData.append(key, dataSet[key]);
                        }
                    }

                    if (Array.isArray(dataSet.mahasiswa_id)) {
                        dataSet.mahasiswa_id.forEach((id) => {
                            formData.append('mahasiswa_id[]', id);
                        });
                    }
                    if (Array.isArray(dataSet.ruangan)) {
                        dataSet.ruangan.forEach((id) => {
                            formData.append('ruangan[]', id);
                        });
                    }
                    setButtonLoading(button, true, 'Menyimpan...');
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
                            setButtonLoading(button, false);
                        }
                    });
                });

            });
        })()
    </script>
@endpush
