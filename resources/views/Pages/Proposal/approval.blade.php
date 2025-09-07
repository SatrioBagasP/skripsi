@extends('Layout.layout')

@section('pages', 'Approval Proposal')

@section('title', config('app.name') . ' | Approval Proposal')

@section('content')

    <div class="card">
        <div class="px-4 py-2">
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
                            <td class='text-sm'> ${item.no_proposal} </td>
                            <td class='text-sm'> ${item.name} </td>
                            <td class='align-middle text-center text-sm'> ${item.ketua} <br> <small >(${item.npm_ketua})<small> </td>
                            <td class='align-middle text-center text-sm'> ${item.organisasi} </td>
                            ${item.admin == true ? `<td class='text-sm'>${item.dosen}</td>` : ''}
                            ${item.admin == true ? `<td class='text-sm'>${item.jurusan}</td>` : ''}
                            <td class='align-middle text-center text-sm'> ${item.status} </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.detail == true ? `<button class='btn btn-info detail w-full mb-1'> <i class="fa fa-eye me-1"></i>Detail</button><br>` : ''}
                            </td>
                        </tr>
                    `);
                    i++;
                });
            }
            const table = dataTable({
                renderTableBody
            });
            table.renderData("{{ route('approval-proposal.getData') }}");

            $(document).ready(function() {
                const validasiUrl = @json(route('approval-proposal.edit', ['id' => ':id']));

                const message = localStorage.getItem('flash_message');
                const type = localStorage.getItem('flash_type');

                if (message && type && typeof flasher !== 'undefined') {
                    flasher[type](message);
                    localStorage.removeItem('flash_message');
                    localStorage.removeItem('flash_type');
                }

                $(document).on('click', '.detail', function() {
                    $(this).attr('disabled', true);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);

                    if (data.detail == true) {
                        let editUrl = validasiUrl.replace(':id', data.id);
                        window.location.href = editUrl;
                    }
                });
            });
        })()
    </script>
@endpush
