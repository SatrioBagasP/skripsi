@extends('Layout.layout')

@section('pages', 'Mahasiswa')

@section('title', config('app.name') . ' | Mahasiswa')

@section('content')

    <div class="card">
        <div class="table-responsive px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data Propsal</h6>
                </div>

                <div>
                    @include('Component.button', [
                        'class' => 'fixed-plugin-button mt-2',
                        'id' => 'btn-tambah-proposal',
                        'label' => 'Tambah Data',
                    ])
                </div>

            </div>
            @include('Component.datatable', [
                'head' => $head,
            ])
        </div>
    </div>

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
                            <td> ${item.no_proposal} </td>
                            <td> ${item.name} </td>
                            ${item.admin == true ? `<td>${item.organisasi}</td>` : ''}
                            ${item.admin == true ? `<td>${item.jurusan}</td>` : ''}
                            <td class='align-middle text-center text-sm'> ${item.status} </td>
                            <td class='align-middle text-center text-sm'> 
                                ${item.edit == true ? ` <a href="#" class='edit'><i class="fa fa-pencil me-1"></i></a>` : ''}
                                ${item.delete == true ? `<a href="#"><i class="fa fa-trash me-1"></i></a>` : ''}
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
                    url: "{{ route('proposal.getData') }}",
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
                                no_proposal: item.no_proposal,
                                organisasi: item.organisasi,
                                jurusan: item.jurusan,
                                status: item.status,
                                edit: item.edit,
                                delete: item.delete,
                                admin: item.admin,
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

            $(document).ready(function() {
                const tambahProposalUrl = "{{ route('proposal.create') }}";
                $('#btn-tambah-proposal').click(function(e) {
                    e.preventDefault();
                    window.location.href = tambahProposalUrl;
                });

                $(document).on('click', '.edit', function() {
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    console.log(data[index]);
                });
            });
        })()
    </script>
@endpush
