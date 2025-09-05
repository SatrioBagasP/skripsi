@extends('Layout.layout')

@section('pages', 'Dosen')

@section('title', config('app.name') . ' | Dosen')

@section('content')

    <div class="card">
        <div class="px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data Dosen</h6>
                </div>

                <div>
                    @include('Component.button', [
                        'class' => 'fixed-plugin-button mt-2',
                        'id' => 'sidebarform-btn',
                        'label' => 'Tambah Data',
                    ])
                </div>

            </div>
            @include('Component.datatable', [
                'title' => 'Data Jurusan',
                'head' => ['NIP', 'Nama', 'Jurusan', 'Status', 'Aksi'],
            ])
        </div>
    </div>
    @include('Pages.Dosen.form', [
        'optionJurusan' => $optionJurusan,
        'jabatanOption' => $jabatanOption,
        'akademikOption' => $akademikOption,
    ])

@endsection

@push('js')
    <script type="module">
        import {
            dataTable
        } from '/js/datatable.js';
        (function() {

            function renderTableBody(data) {
                let i = 1;
                data.forEach((item, index) => {
                    $('#tableBody').append(`
                        <tr class="data-item" data-index="${index}">
                            <td class='align-middle text-center text-sm'>${i}</td>
                            <td> ${item.nip} </td>
                            <td> ${item.name} </td>
                            <td> ${item.jurusan} </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.status === 1  ? '<span class="badge badge-sm bg-gradient-success">Online</span>' : '<span class="badge badge-sm bg-gradient-secondary">Offline</span>'}
                            </td>
                            <td class='align-middle text-center text-sm'>
                                <a href="#" class='edit'><i class="fa fa-pencil me-1"></i></a>
                            </td>
                        </tr>
                    `);
                    i++;
                });
            }

            const table = dataTable({
                renderTableBody
            });
            table.renderData("{{ route('master.dosen.getData') }}");

            let dataSet = {}
            $(document).ready(function() {

                $('#sidebarform-btn').click(function(e) {
                    e.preventDefault();
                    resetDataSet();
                });


                $('input[id], select[id], textarea[id], checkbox[id]').on('input change', function() {
                    const key = $(this).attr('id');
                    const isCheckbox = $(this).attr('type') === 'checkbox';
                    dataSet[key] = isCheckbox ? $(this).prop('checked') : $(this).val();
                });

                $(document).on('click', '.edit', function() {
                    resetDataSet();
                    $('#btn-tambah').hide();
                    $('#btn-edit').show();
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);
                    dataSet.id = data.id;
                    console.log(data);
                    $('#sidebar-form').addClass('show');
                    $('#name').val(data.name).trigger('change');
                    $('#no_hp').val(data.no_hp).trigger('change');
                    $('#nip').val(data.nip).trigger('change');
                    $('#alamat').val(data.alamat).trigger('change');
                    $('#jurusan_id').val(data.jurusan_id).trigger('change');
                    $('#jabatan_id').val(data.jabatan_id).trigger('change');
                    $('#akademik_id').val(data.akademik_id).trigger('change');
                    $('#status').prop('checked', data.status == 1).trigger('change');
                });

                $('#btn-tambah').click(function(e) {
                    e.preventDefault();
                    submitForm($(this), 'store')
                });

                $('#btn-edit').click(function(e) {
                    e.preventDefault();
                    submitForm($(this), 'update')
                });

                function resetDataSet() {
                    dataSet = {};
                    $('.select2').val('').trigger('change');
                    $('#status').prop('checked', false);
                    $('.invalid-feedback').text('').hide();
                    $('.form-control').removeClass('is-invalid');
                }

                function submitForm(button, type) {
                    button.attr('disabled', true);
                    let route = ''
                    if (type == 'store') {
                        route = '{{ route('master.dosen.store') }}'
                    } else {
                        route = '{{ route('master.dosen.update') }}'
                    }
                    $.ajax({
                        type: "POST",
                        url: route,
                        data: dataSet,
                        success: function(response) {
                            flasher.success(response.message);
                            button.attr('disabled', false);
                            $('#sidebar-form').removeClass('show');
                            table.reload();
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
                }
            });
        })()
    </script>
@endpush
