@extends('Layout.layout')

@section('pages', 'Approval Proposal')

@section('title', config('app.name') . ' | Approval Proposal')

@section('content')

    <div class="card">
        <div class="table-responsive px-4 py-2">
            <div class='d-flex justify-content-between align-items-center'>
                <div>
                    <h6>Data Pending Propsal</h6>
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
                            ${item.admin == true ? `<td>${item.dosen}</td>` : ''}
                            ${item.admin == true ? `<td>${item.jurusan}</td>` : ''}
                            <td class='align-middle text-center text-sm'> ${item.organisasi} </td>
                            <td class='align-middle text-center text-sm'> ${item.status} </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.detail == true ? `<button class='btn btn-info detail w-50 mb-1'> <i class="fa fa-eye me-1"></i>Detail</button><br>` : ''}
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
                    url: "{{ route('approval-proposal.getData') }}",
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
                                dosen: item.dosen,
                                jurusan: item.jurusan,
                                status: item.status,
                                detail: item.detail,
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
                const validasiUrl = @json(route('approval-proposal.edit', ['id' => ':id']));

                $(document).on('click', '.detail', function() {
                    $(this).attr('disabled', true);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    if (data[index].detail == true) {
                        let editUrl = validasiUrl.replace(':id', data[index].id);
                        window.location.href = editUrl;
                    }
                });
            });
        })()
    </script>
@endpush
