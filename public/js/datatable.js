export function dataTable(options = {}) {

    const renderTableBody = options.renderTableBody || function () { };

    let state = {
        url: null,
        data: [],
        searching: false,
        search: null,
        itemDisplay: null ?? 10,
        page: 1,
        currentPage: null,
        totalPage: null,
        filter: {}
    };

    let debounceTimer;

    function renderPagination() {
        const currentPage = state.currentPage ?? 1;
        const totalPage = state.totalPage ?? 1;
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

    $(document).on('keyup', '#search', function (e) {
        e.preventDefault();
        state.page = 1;
        state.search = $('#search').val();
        if (e.key === 'Enter') {
            // Langsung cari kalau tekan Enter
            reload(state.url);
        } else {
            // Debounce kalau bukan Enter
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                reload(state.url);
            }, 800);
        }
    });

    $(document).on('change', '#option', function () {
        state.itemDisplay = $(this).val();
        state.page = 1;
        reload(state.url);
    });

    $(document).on('click', '.page-btn', function () {
        const page = parseInt($(this).data('page'));
        $('.page-btn').addClass('disabled');
        if (!isNaN(page)) {
            state.currentPage = page;
            getData(page); // fungsi yang ambil data

            // update active class
            $('.page-btn').removeClass('active');
            $(this).addClass('active');
        }
    });

    async function getData(url = null) {
        if (state.searching == true) {
            return;
        }
        state.url = url;
        state.searching = true;
        state.data = [];
        $('#tableBody').empty(); // Dari data table blade
        $('#loading-spinner').show();

        return new Promise((resolve, reject) => {
            $.ajax({
                type: 'GET',
                url: state.url,
                data: {
                    page: state.page,
                    search: state.search,
                    itemDisplay: state.itemDisplay,
                    filter: state.filter,
                },
                success: function (response) {
                    state.data = response.data;
                    state.currentPage = response.currentPage;
                    state.totalPage = response.totalPage;
                    renderPagination();
                    renderTableBody(state.data);
                    resolve(state.data);
                },
                error: function (xhr) {
                    reject(xhr.responseJSON?.message || 'Error loading data');
                },
                complete: function () {
                    $('#loading-spinner').hide();
                    state.searching = false;
                }
            });
        });
    }

    function reload() {
        getData(state.url);
    }

    function filter(newFilters) {
        state.filter = { ...state.filter, ...newFilters };
        reload();
    }

    return {
        getData,
        filter,
        reload,
    };
}