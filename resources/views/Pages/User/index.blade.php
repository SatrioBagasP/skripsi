@extends('Layout.layout')

@section('pages', 'User')

@section('title', config('app.name') . ' | User')

@section('content')

    <div class="card">
        <div class="table-responsive px-4 py-2">
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
                'head' => ['Username', 'Email', 'User Type', 'Role', 'Status', 'Aksi'],
            ])
        </div>
    </div>

    @include('Pages.User.form', [
        'userAbleOption' => $userAbleOption,
        'roleOption' => $roleOption,
    ])

@endsection

@push('js')
    <script>
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
                            <td> ${item.role} </td>
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

            function getData(page = 1) {
                if (searching) {
                    return;
                }
                searching = true;
                data = [];
                $('#tableBody').empty(); // Dari data table blade
                $('#loading-spinner').show();
                $.ajax({
                    type: "GET",
                    url: "{{ route('master.user.getData') }}",
                    data: {
                        page: page,
                        search: paginateControll.search,
                        itemDisplay: paginateControll.itemDisplay
                    },
                    success: function(response) {
                        response.data.forEach(item => {
                            data.push({
                                id: item.id,
                                name: item.name,
                                email: item.email,
                                role: item.role,
                                user_type: item.user_type,
                                user_id: item.user_id,
                                role_id: item.role_id,
                                status: item.status,
                            });
                        });
                        paginateControll.currentPage = response.currentPage;
                        paginateControll.totalPage = response.totalPage;
                        renderPagination(); // function dari datatable
                        renderTableBody(data)
                    },
                    error: function(xhr, status, error) {
                        flasher.error(xhr.responseJSON.message);
                    },
                    complete: function() {
                        $('#loading-spinner').hide();
                        searching = false;
                    }
                });
            }

            getData();
            // END BUNDLE

            let dataSet = {
                id: null,
                name: null,
                email: null,
                user_id: null,
                role_id: null,
                password: null,
                status: false,
            }
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

                    dataSet = {
                        id: data[index].id,
                    }

                    $('#sidebar-form').addClass('show');
                    $('#name').val(data[index].name).trigger('change');
                    $('#email').val(data[index].email).trigger('change');
                    $('#user_id').val(data[index].user_id).trigger('change');
                    $('#role_id').val(data[index].role_id).trigger('change');
                    $('#status').prop('checked', data[index].status == 1).trigger('change');
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
                    dataSet = {
                        id: null,
                        name: null,
                        email: null,
                        user_id: null,
                        role_id: null,
                        password: null,
                        status: false,
                    };
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
                            getData();
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
