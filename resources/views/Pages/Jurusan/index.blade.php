@extends('Layout.layout')

@section('pages', 'Jurusan')

@section('title', config('app.name') . ' | Jurusan')

@section('content')

    {{-- TABLE --}}
    <div class="card">
        <div class="table-responsive px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data Jurusan</h6>
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
                'head' => ['Nama Jurusan', 'Kode Jurusan', 'Status', 'Aksi'],
            ])
        </div>
    </div>

    @include('Pages.Jurusan.form')

@endsection

@push('js')
    <script>
        (function() {

            let searching = false;

            @stack('paginate_js')

            let data = {
                jurusan: [],
            };

            function renderTableBody(data) {
                let i = 1;
                data.forEach((item, index) => {
                    $('#tableBody').append(`
                        <tr class="data-item" data-index="${index}">
                            <td class='align-middle text-center text-sm'>${i}</td>
                            <td> ${item.name} </td>
                            <td> ${item.kode} </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.status === 1  ? '<span class="badge badge-sm bg-gradient-success">Online</span>' : '<span class="badge badge-sm bg-gradient-secondary">Offline</span>'}    
                            </td>
                            <td class='align-middle text-center text-sm'> 
                                <a href="#" class='edit'><i class="fa fa-pencil me-1"></i></a>
                                <a href="#"><i class="fa fa-trash me-1"></i></a>
                            </td>
                        </tr>
                    `);
                    i++;
                });
            }

            function getData(page = 1) {
                if(searching){
                    return;
                }
                searching = true;
                data.jurusan = [];
                $('#tableBody').empty(); // Dari data table blade
                $('#loading-spinner').show();
                $.ajax({
                    type: "GET",
                    url: "{{ route('master.jurusan.getData') }}",
                    data: {
                        page: page,
                        search: paginateControll.search,
                        itemDisplay: paginateControll.itemDisplay
                    },
                    success: function(response) {
                        response.data.forEach(item => {
                            data.jurusan.push({
                                id: item.id,
                                name: item.name,
                                kode: item.kode,
                                status: item.status,
                            });
                        });
                        paginateControll.currentPage = response.currentPage;
                        paginateControll.totalPage = response.totalPage;
                        renderPagination(); // function dari datatable
                        renderTableBody(data.jurusan)
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

            let dataSet = {
                id: null,
                name: null,
                kode: null,
                status: false,
            };

            $(document).ready(function() {

                $('#sidebarform-btn').click(function(e) {
                    e.preventDefault();
                    $('#btn-edit').hide();
                    $('#btn-tambah').show();
                    resetDataSet();
                });


                $('input[id], select[id], textarea[id], checkbox[id]').on('input change', function() {
                    const key = $(this).attr('id');
                    const isCheckbox = $(this).attr('type') === 'checkbox';
                    dataSet[key] = isCheckbox ? $(this).prop('checked') : $(this).val();
                });

                $('#btn-tambah').click(function(e) {
                    e.preventDefault();
                    submitForm($(this),'store')
                });

                $('#btn-edit').click(function(e) {
                    e.preventDefault();
                    submitForm($(this),'update')
                });

                $(document).on('click', '.edit', function() {
                    resetDataSet();
                    $('#btn-tambah').hide();
                    $('#btn-edit').show();
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));

                    dataSet = {
                        id: data.jurusan[index].id,
                    }
                    $('#sidebar-form').addClass('show');
                    $('#name').val(data.jurusan[index].name).trigger('change');
                    $('#kode').val(data.jurusan[index].kode).trigger('change');
                    $('#status').prop('checked', data.jurusan[index].status == 1).trigger('change');
                });

                function resetDataSet() {
                    dataSet = {
                        id: null,
                        name: null,
                        kode: null,
                        status: false,
                    };
                    $('#status').prop('checked', dataSet.status);
                    $('.invalid-feedback').text('').hide();
                    $('.form-control').removeClass('is-invalid');
                }

                function submitForm(button, type) {
                    button.attr('disabled', true);
                    let route = ''
                    if (type == 'store') {
                        route = '{{ route('master.jurusan.store') }}'
                    } else {
                        route = '{{ route('master.jurusan.update') }}'
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
        })();
    </script>
@endpush
