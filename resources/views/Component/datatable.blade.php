@php
    $head = $head ?? ['Dummy', 'Dummy'];
    $search = $search ?? true;
    $itemDisplay = $itemDisplay ?? true;
    $selection = $selection ?? false;
    $paginate = $paginate ?? true;
@endphp

<div class="d-flex justify-content-between">
    @if ($itemDisplay)
        <select name="" id="option" class="form-select w-10">
            <option value="10">10 Item</option>
            <option value="20">20 Item</option>
            <option value="50">50 Item</option>
            <option value="100">100 Item </option>
        </select>
    @else
        <span></span>
    @endif

    @if ($search)
        <input type="text" class="form-control w-20" placeholder="Search...." id="search">
    @endif
</div>
<table class="table align-items-center mb-2">
    <thead id='tableHead'>
        @if ($selection)
            <th class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>
                <input type="checkbox">
            </th>
        @endif
        <th class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>No</th>
        @foreach ($head as $item)
            <th class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>
                {{ $item }}</th>
        @endforeach
    </thead>
    <tbody>

    </tbody>
    <tbody id='tableBody'>

    </tbody>
</table>
<div id="loading-spinner" style="display: none; text-align: center;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

@if ($paginate)
    <div class="d-flex justify-content-end mb-4">
        <div id="paginationControls" class="pagination ">

        </div>
    </div>
@endif

<script>
    @push('paginate_js')
        let paginateControll = {
            search: null,
            itemDisplay: 10,
            currentPage: null,
            lastPage: null,
            loading: false,
        }

        let debounceTimer;

        function renderPagination() {
            const currentPage = paginateControll.currentPage ?? 1;
            const totalPage = paginateControll.totalPage ?? 1;
            const container = $('#paginationControls');
            container.empty();

            if (totalPage <= 1) return;

            // Tombol Prev
            container.append(
                `<a  href="javascript:;" class="page-btn page-link ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">
                    <i class="fa fa-angle-left"></i>
                    <span class="sr-only">Previous</span>
                </a>`
            );

            const maxVisible = 3; // berapa angka maksimal yang ingin ditampilkan langsung
            let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(totalPage, start + maxVisible - 1);

            // Jika terlalu dekat akhir
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }

            if (start > 1) {
                container.append(`<a  href="javascript:;" class="page-btn page-link" data-page="1">1</a>`);
                if (start > 2) {
                    container.append(`<span>...</span>`);
                }
            }

            for (let i = start; i <= end; i++) {
                container.append(
                    `<a  href="javascript:;" class="page-btn page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`
                );
            }

            if (end < totalPage) {
                if (end < totalPage - 1) {
                    container.append(`<span>...</span>`);
                }
                container.append(
                    `<a  href="javascript:;" class="page-btn page-link" data-page="${totalPage}">${totalPage}</a>`);
            }

            // Tombol Next
            container.append(
                `<a  href="javascript:;" class="page-btn page-link ${currentPage === totalPage ? 'disabled' : ''}" data-page="${currentPage + 1}">
                    <i class="fa fa-angle-right"></i>
                    <span class="sr-only">Next</span>
                </a>`
            );
        }

        $(document).on('keyup', '#search', function(e) {
            e.preventDefault();
            paginateControll.search = $('#search').val();
            if (e.key === 'Enter') {
                // Langsung cari kalau tekan Enter
                getData(1);
            } else {
                // Debounce kalau bukan Enter
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    getData(1);
                }, 800);
            }
        });

        $(document).on('change', '#option', function() {
            paginateControll.itemDisplay = $(this).val();
            getData(1);
        });

        $(document).on('click', '.page-btn', function() {
            const page = parseInt($(this).data('page'));
            $('.page-btn').addClass('disabled');
            if (!isNaN(page)) {
                paginateControll.currentPage = page;
                getData(page); // fungsi yang ambil data

                // update active class
                $('.page-btn').removeClass('active');
                $(this).addClass('active');
            }
        });
    @endpush
</script>
