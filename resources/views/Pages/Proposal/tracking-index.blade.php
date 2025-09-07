@extends('Layout.layout')

@section('pages', 'Tracking Proposal')

@section('title', config('app.name') . ' | Tracking Proposal')

@push('css')
    <style>
        /* sederhana tapi mirip tracking paket */
        .tracking {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tracking:before {
            content: '';
            position: absolute;
            left: 40px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e9ecef;
        }

        .tracking li {
            position: relative;
            padding: 20px 20px 20px 80px;
            border-radius: 8px;
            margin-bottom: 18px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .tracking .dot {
            position: absolute;
            left: 28px;
            top: 28px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #fff;
        }

        .tracking .dot.done {
            background: #0d6efd;
        }

        .tracking .dot.pending {
            background: #6c757d;
        }

        .tracking .title {
            font-weight: 600;
            margin-bottom: 6px;
        }

        .tracking .time {
            font-size: 13px;
            color: #6c757d;
        }

        .tracking .desc {
            margin-top: 8px;
            color: #495057;
        }

        .current-step {
            border-left: 4px solid #0d6efd;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.06);
        }

        .spinner-inline {
            display: inline-block;
            margin-left: 8px;
        }
    </style>
@endpush

@section('content')

    <div class="card">
        <div class="px-4 py-5">

            <h2 class="mb-4">Tracking Pengajuan Proposal</h2>

            <div class="input-group mb-3">
                <input type="text" id="search-keyword" class="form-control" placeholder="Masukkan kode proposal">
                <button id="btn-search" class="btn btn-primary mb-0" type="button">Cari</button>
            </div>

            <div id="tracking-result" class="mt-4">

            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        (function() {
            function doSearch() {
                let keyword = $('#search-keyword').val().trim();
                if (!keyword) {
                    $('#tracking-result').html(
                        '<div class="">Masukkan keyword terlebih dahulu</div>');
                    return;
                }

                $.ajax({
                    url: "{{ route('tracking.search') }}",
                    method: "POST",
                    data: {
                        keyword: keyword
                    },
                    dataType: "html", // penting: kita minta HTML
                    beforeSend: function() {
                        $('#btn-search').prop('disabled', true).text('Mencari...');
                        $('#tracking-result').html(
                            '<div class="">Mencari... <span class="spinner-inline">⏳</span></div>'
                        );
                    },
                    success: function(html) {
                        // server mengembalikan HTML (rendered Blade) — langsung masukkan ke container
                        $('#tracking-result').html(html);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            $('#tracking-result').html(
                                '<div class="">Keyword tidak valid / kosong.</div>');
                        } else {
                            $('#tracking-result').html(
                                '<div class="">Terjadi kesalahan pada server.</div>');
                        }
                    },
                    complete: function() {
                        $('#btn-search').prop('disabled', false).text('Cari');
                    }
                });
            }

            $('#btn-search').on('click', doSearch);

            $('#search-keyword').on('keypress', function(e) {
                if (e.which === 13) {
                    doSearch();
                }
            });
        })()
    </script>
@endpush
