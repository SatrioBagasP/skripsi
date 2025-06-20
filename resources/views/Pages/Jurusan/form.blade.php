@extends('Layout.sidebarform')

@section('titleSidebarForm', 'Tambah Data')

@section('sub-titleSidebarForm', 'Jurusan')

@section('contentSidebarForm')
    {{-- <form> --}}
    <label>Jurusan</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Nama Jurusan" name="name" id="name">
        <div class="invalid-feedback" id="nameError"></div>
    </div>
    <label>Kode</label>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="kode" name="kode" id="kode">
        <div class="invalid-feedback" id="kodeError"></div>
    </div>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="status" name="status">
        <label class="form-check-label">Status</label>
    </div>
    <div class="d-flex justify-content-end">
        @include('Component.button', [
            'class' => 'bg-gradient-info mt-4 mb-0',
            'label' => 'Tambah',
            'id' => 'btn-tambah',
        ])
    </div>

    {{-- </form> --}}
@endsection

@push('js')
    <script>
        (function() {

            let dataSet = {
                name: null,
                kode: null,
                status: false,
            };

            $(document).ready(function() {

                $('input[id], select[id], textarea[id], checkbox[id]').on('input change', function() {
                    const key = $(this).attr('id');
                    const isCheckbox = $(this).attr('type') === 'checkbox';
                    dataSet[key] = isCheckbox ? $(this).prop('checked') : $(this).val();
                });

                $('#btn-tambah').click(function(e) {
                    e.preventDefault();
                    $(this).attr('disabled',true);
                    $.ajax({
                        type: "POST",
                        url: "{{ route('master.jurusan.store') }}",
                        data: dataSet,
                        success: function(response) {
                            flasher.success(response.message);
                            $('#btn-tambah').attr('disabled',false);
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
                            $('#btn-tambah').attr('disabled',false);
                        }
                    });
                });

            });
        })();
    </script>
@endpush
