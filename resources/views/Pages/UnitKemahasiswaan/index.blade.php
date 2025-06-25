@extends('Layout.layout')

@section('pages', 'Unit Kemahasiswaan')

@section('title', config('app.name') . ' | Unit Kemahasiswaan')

@section('content')

    <div class="card">
        <div class="table-responsive px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data Unit Kemahasiswaan</h6>
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
                'head' => ['Nama', 'Jurusan', 'Gambar', 'Status', 'Aksi'],
            ])
        </div>
    </div>

    @include('Pages.UnitKemahasiswaan.form', [
        'optionJurusan' => $optionJurusan,
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
                            <td> ${item.jurusan} </td>
                            <td class='align-middle text-center text-sm'> <img class='rounded mx-auto d-block' src='${item.image}' alt='icon' width='62'> </td>
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
                if (searching) {
                    return;
                }
                searching = true;
                data = [];
                $('#tableBody').empty(); // Dari data table blade
                $('#loading-spinner').show();
                $.ajax({
                    type: "GET",
                    url: "{{ route('master.unit-kemahasiswaan.getData') }}",
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
                                image: item.image,
                                no_hp: item.no_hp,
                                jurusan: item.jurusan,
                                jurusan_id: item.jurusan_id,
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
                image: null,
                no_hp: null,
                jurusan_id: null,
                status: false,
            }
            $(document).ready(function() {

                $('#sidebarform-btn').click(function(e) {
                    e.preventDefault();
                    resetDataSet();
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

                $('#image').change(function(event) {
                    let file = event.target.files[0];
                    let preview = $('#imagePreview');

                    if (file) {
                        let reader = new FileReader();

                        reader.onload = function(e) {
                            preview.attr('src', e.target.result);
                            preview.removeClass('d-none');
                        };

                        reader.readAsDataURL(file);
                    }
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
                    if (data[index].image) {
                        $('#imagePreview').attr('src', data[index].image).removeClass('d-none');
                    } else {
                        $('#imagePreview').attr('src', '').addClass('d-none');
                    }
                    $('#name').val(data[index].name).trigger('change');
                    $('#no_hp').val(data[index].no_hp).trigger('change');
                    $('#npm').val(data[index].npm).trigger('change');
                    $('#jurusan_id').val(data[index].jurusan_id).trigger('change');
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
                        image: null,
                        no_hp: null,
                        jurusan_id: null,
                        status: false,
                    };
                    $('.select2').val('').trigger('change');
                    $('#image').val('').trigger('change');
                    $('#imagePreview').attr('src', '').addClass('d-none');
                    $('#status').prop('checked', dataSet.status);
                    $('.invalid-feedback').text('').hide();
                    $('.form-control').removeClass('is-invalid');
                }

                function submitForm(button, type) {
                    button.attr('disabled', true);
                    let route = ''
                    if (type == 'store') {
                        route = '{{ route('master.unit-kemahasiswaan.store') }}'
                    } else {
                        route = '{{ route('master.unit-kemahasiswaan.update') }}'
                    }

                    const formData = new FormData();
                    for (const key in dataSet) {
                        if (dataSet[key] !== null) {
                            formData.append(key, dataSet[key]);
                        }
                    }
                    $.ajax({
                        type: "POST",
                        url: route,
                        data: formData,
                        processData: false,
                        contentType: false,
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
