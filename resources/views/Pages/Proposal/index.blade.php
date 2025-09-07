@extends('Layout.layout')

@section('pages', 'Pengajuan Proposal')

@section('title', config('app.name') . ' | Pengajuan Proposal')

@section('content')

    <div class="card">
        <div class="px-4 py-2">
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
    <script type="module">
        import {
            dataTable
        } from '/js/datatable.js';
        (function() {
            function renderTableBody(data) {
                let i = 1;
                data.forEach((item, index) => {

                    const statusClass = {
                        'Draft': 'bg-secondary',
                        'Rejected': 'bg-danger',
                        'Approved': 'bg-success'
                    } [item.status] || 'bg-warning';
                    $('#tableBody').append(`
                        <tr class="data-item" data-index="${index}">
                            <td class='align-middle text-center text-sm'>${i}</td>
                            <td class='text-sm'> ${item.no_proposal} </td>
                            <td class='text-sm'> ${item.name} </td>
                            <td class='align-middle text-center text-sm'> ${item.ketua} <br> <small >(${item.npm_ketua})<small> </td>
                            <td class='align-middle text-center text-sm'> ${item.dosen} </td>
                            ${item.admin == true ? `<td>${item.organisasi}</td>` : ''}
                            ${item.admin == true ? `<td>${item.jurusan}</td>` : ''}
                            <td class='align-middle text-center text-sm'>
                                <span class="badge ${statusClass}">${item.status}</span>
                            </td>
                            <td class='align-middle text-center text-sm'>
                                ${item.pengajuan == true ? `<button class='btn btn-secondary mb-1 pengajuan' style="width:100px"><svg width="12px" height="20px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><title>spaceship</title><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-1720.000000, -592.000000)" fill="#FFFFFF"fill-rule="nonzero"><g transform="translate(1716.000000, 291.000000)"><g transform="translate(4.000000, 301.000000)"><path class="color-background" d="M39.3,0.706666667 C38.9660984,0.370464027 38.5048767,0.192278529 38.0316667,0.216666667 C14.6516667,1.43666667 6.015,22.2633333 5.93166667,22.4733333 C5.68236407,23.0926189 5.82664679,23.8009159 6.29833333,24.2733333 L15.7266667,33.7016667 C16.2013871,34.1756798 16.9140329,34.3188658 17.535,34.065 C17.7433333,33.98 38.4583333,25.2466667 39.7816667,1.97666667 C39.8087196,1.50414529 39.6335979,1.04240574 39.3,0.706666667 Z M25.69,19.0233333 C24.7367525,19.9768687 23.3029475,20.2622391 22.0572426,19.7463614 C20.8115377,19.2304837 19.9992882,18.0149658 19.9992882,16.6666667 C19.9992882,15.3183676 20.8115377,14.1028496 22.0572426,13.5869719 C23.3029475,13.0710943 24.7367525,13.3564646 25.69,14.31 C26.9912731,15.6116662 26.9912731,17.7216672 25.69,19.0233333 L25.69,19.0233333 Z"></path><path class="color-background opacity-6" d="M1.855,31.4066667 C3.05106558,30.2024182 4.79973884,29.7296005 6.43969145,30.1670277 C8.07964407,30.6044549 9.36054508,31.8853559 9.7979723,33.5253085 C10.2353995,35.1652612 9.76258177,36.9139344 8.55833333,38.11 C6.70666667,39.9616667 0,40 0,40 C0,40 0,33.2566667 1.855,31.4066667 Z"></path><path class="color-background opacity-6" d="M17.2616667,3.90166667 C12.4943643,3.07192755 7.62174065,4.61673894 4.20333333,8.04166667 C3.31200265,8.94126033 2.53706177,9.94913142 1.89666667,11.0416667 C1.5109569,11.6966059 1.61721591,12.5295394 2.155,13.0666667 L5.47,16.3833333 C8.55036617,11.4946947 12.5559074,7.25476565 17.2616667,3.90166667 L17.2616667,3.90166667 Z"></path> <path class="color-background opacity-6" d="M36.0983333,22.7383333 C36.9280725,27.5056357 35.3832611,32.3782594 31.9583333,35.7966667 C31.0587397,36.6879974 30.0508686,37.4629382 28.9583333,38.1033333 C28.3033941,38.4890431 27.4704606,38.3827841 26.9333333,37.845 L23.6166667,34.53 C28.5053053,31.4496338 32.7452344,27.4440926 36.0983333,22.7383333 L36.0983333,22.7383333 Z"> </path></g></g></g></g></svg> Ajukan </button><br>` : ''}
                                ${item.edit == true ? `<button class='btn btn-info edit mb-1' style="width:100px"> <i class="fa fa-pencil me-1"></i>Edit</button><br>` : ''}
                                ${item.delete == true ? `<button class='btn btn-danger delete mb-1' style="width:100px"><i class="fa fa-trash me-1"></i>Delete</button>` : ''}
                            </td>
                        </tr>
                    `);
                    i++;
                });
            }

            const table = dataTable({
                renderTableBody
            });
            table.renderData("{{ route('proposal.getData') }}");


            $(document).ready(function() {

                const message = localStorage.getItem('flash_message');
                const type = localStorage.getItem('flash_type');

                if (message && type && typeof flasher !== 'undefined') {
                    flasher[type](message);
                    localStorage.removeItem('flash_message');
                    localStorage.removeItem('flash_type');
                }

                const editUrlTemplate = @json(route('proposal.edit', ['id' => ':id']));
                const tambahProposalUrl = "{{ route('proposal.create') }}";

                $('#btn-tambah-proposal').click(function(e) {
                    e.preventDefault();
                    window.location.href = tambahProposalUrl;
                });

                $(document).on('click', '.edit', function() {
                    $(this).attr('disabled', true);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);
                    if (data.edit == true) {
                        let editUrl = editUrlTemplate.replace(':id', data.id);
                        window.location.href = editUrl;
                    }
                });

                $(document).on('click', '.delete', function() {
                    const button = $(this);
                    button.attr('disabled', true);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);
                    if (data.delete == true) {

                        Swal.fire({
                            icon: 'question',
                            title: 'Apakah Anda Yakin?',
                            text: 'Data ini bersifat permanen dan tidak bisa dipulihkan setelah dihapus.',
                            showConfirmButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Hapus Sekarang!',
                            confirmButtonColor: '#F1416C',
                            cancelButtonText: 'Batal',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "POST",
                                    url: "{{ route('proposal.delete') }}",
                                    data: {
                                        id: data.id,
                                    },
                                    success: function(response) {
                                        flasher.success(response.message);
                                        table.reload();
                                    },
                                    error: function(xhr, status, error) {
                                        flasher.error(xhr.responseJSON.message);
                                        button.attr('disabled', false);
                                    }
                                });
                            } else {
                                button.attr('disabled', false);
                            }

                        })
                    }
                });

                $(document).on('click', '.pengajuan', function() {
                    const button = $(this);
                    button.attr('disabled', true);
                    const item = $(this).closest('.data-item');
                    const index = parseInt(item.data('index'));
                    let data = table.getDataByIndex(index);
                    if (data.pengajuan == true) {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('proposal.pengajuan') }}",
                            data: {
                                id: data.id,
                            },
                            success: function(response) {
                                flasher.success(response.message);
                                table.reload()
                            },
                            error: function(xhr, status, error) {
                                flasher.error(xhr.responseJSON.message);
                                button.attr('disabled', false);
                            }
                        });
                    }
                })
            });
        })()
    </script>
@endpush
