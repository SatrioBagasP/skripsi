@extends('Layout.layout')

@section('pages', 'User')

@section('title', config('app.name') . ' | User')

@section('content')

    <div class="card">
        <div class="px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data User</h6>
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
                'head' => ['Username', 'Email', 'User Type', 'Status', 'Aksi'],
            ])
        </div>
    </div>

    @include('Pages.User.form', [
        'userAbleOption' => $userAbleOption,
        'roleOption' => $roleOption,
    ])

@endsection

@push('js')
    <script type="module">
        import {
            dataTable
        } from '/js/datatable.js';
        (function() {
            // BUNDLE DATATABLE
            let searching = false;
            let data = [];
            @stack('paginate_js')

            function renderTableBody(data) {
                let i = 1;
                data.forEach((item, index) => {
                    $('#tableBody').append(`
                        <tr class="data-item" data-index="${index}">
                            <td class='align-middle text-center text-sm'>${i}</td>
                            <td> ${item.name} </td>
                            <td> ${item.email} </td>
                            <td> ${item.user_type} </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.status === 1  ? '<span class="badge badge-sm bg-gradient-success">Online</span>' : '<span class="badge badge-sm bg-gradient-secondary">Offline</span>'}
                            </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.status == 1 ? `<a href="#" class='edit'><i class="fa fa-pencil me-1"></i></a>` : ''}
                            </td>
                        </tr>
                    `);
                    i++;
                });
            }

            const table = dataTable({
                renderTableBody
            });
            table.renderData("{{ route('master.user.getData') }}");

            let dataSet = {};
            $(document).ready(function() {

                $('#sidebarform-btn').click(function(e) {
                    e.preventDefault();
                    resetDataSet();
                });


                $('input[id], select[id], textarea[id], checkbox[id]').on('input change', function() {
                    const key = $(this).attr('id');
                    const isCheckbox = $(this).attr('type') === 'checkbox';
                    if (key == 'role_id') {
                        dataSet.selected_role = $(this).val();
                    } else {
                        dataSet[key] = isCheckbox ? $(this).prop('checked') : $(this).val();
                    }
                });

                $(document).on('click', '.edit', function() {
                    resetDataSet();
                    $('#btn-tambah').hide();
                    $('#btn-edit').show();
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);
                    dataSet.id = data.id;
                    $('#sidebar-form').addClass('show');
                    $('#name').val(data.name).trigger('change');
                    $('#email').val(data.email).trigger('change');
                    $('#user_id').val(data.user_id).trigger('change');
                    $('#role_id').val(data.selected_role).trigger('change');
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
                    $('#password').val('').trigger('change');
                    $('#status').prop('checked', dataSet.status);
                    $('.invalid-feedback').text('').hide();
                    $('.form-control').removeClass('is-invalid');
                }

                function submitForm(button, type) {
                    button.attr('disabled', true);
                    let route = ''
                    if (type == 'store') {
                        route = '{{ route('master.user.store') }}'
                    } else {
                        route = '{{ route('master.user.update') }}'
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
