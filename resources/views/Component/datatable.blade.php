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
<div class="table-responsive">
    <table class="table align-items-center mb-2">
        <thead id='tableHead'>
            @if ($selection)
                <th
                    class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>
                    <input type="checkbox">
                </th>
            @endif
            <th class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>No
            </th>
            @foreach ($head as $item)
                <th
                    class='text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle text-center'>
                    {{ $item }}</th>
            @endforeach
        </thead>
        <tbody id='tableBody'>

        </tbody>
    </table>
</div>

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
