    <div class="fixed-plugin" id='sidebar-form'>
        <div class="card shadow-lg ">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0" id='titleSidebarForm'>@yield('titleSidebarForm')</h5>
                    <p id='sub-titleSidebarForm'>@yield('sub-titleSidebarForm')</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
                <!-- End Toggle Button -->
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0">
                {{-- <form id='sidebarForm' enctype="multipart/form-data"> --}}
                @yield('contentSidebarForm')
                {{-- </form> --}}
            </div>
        </div>
    </div>
