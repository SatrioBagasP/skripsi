<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title')
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link href="{{ asset('css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('css/soft-ui-dashboard.css') }}" rel="stylesheet" />
    <!-- Nepcha Analytics (nepcha.com) -->
    <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <!-- Bootstrap 5 -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <style>
        .file-input {
            opacity: 0;
            position: absolute;
            width: 1px;
            height: 1px;
        }

        .file-label {
            cursor: pointer;
            padding: 20px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .file-label:hover {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }

        .file-label i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #6c757d;
            transition: all 0.3s;
        }

        .file-label.has-file {
            border: 2px solid #0d6efd;
            background-color: #f0f7ff;
        }

        .file-label.has-file:hover {
            background-color: #e6f2ff;
        }

        .file-label.has-file i {
            color: #0d6efd;
        }

        .file-preview {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            display: none;
        }

        .file-name {
            font-size: 0.9rem;
            color: #495057;
            text-align: center;
            margin-top: 8px;
            word-break: break-word;
            display: none;
        }

        .file-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 12px;
        }

        .file-label:hover .file-overlay {
            opacity: 1;
        }

        .upload-status {
            display: none;
            margin-top: 10px;
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .document-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            margin-top: 10px;
            display: none;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background-color: #0d6efd;
            transition: width 0.4s ease;
        }
    </style>
    @stack('css')
</head>

<body class="g-sidenav-show  bg-gray-100">
    @include('Layout.sidebar')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        @include('Layout.navbar')
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="px-3" style="min-height: 80vh">
                @yield('content')
            </div>

            <footer class="footer pt-3">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                ©
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                made with <i class="fa fa-heart"></i> by
                                <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative
                                    Tim</a>
                                for a better web.
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com" class="nav-link text-muted"
                                        target="_blank">Creative Tim</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted"
                                        target="_blank">About Us</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/blog" class="nav-link text-muted"
                                        target="_blank">Blog</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted"
                                        target="_blank">License</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </main>
    <!--   Core JS Files   -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/core/popper.min.js') }}"></script>
    <script src="{{ asset('js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('js/plugins/chartjs.min.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@flasher/flasher@1.2.4/dist/flasher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.select2').select2({
                placeholder: '-- Pilih Data --'
            });
            $('#sidebarform-btn').click(function(e) {
                $('#sidebarForm').find('input, textarea, select').val('').trigger('change');
            });

            $(document).on('change', '.file-input', function(e) {
                e.preventDefault();
                $progress = $(this).closest('.file-upload').find('.progress');
                $label = $(this).closest('.file-upload').find('.file-label');
                $fileIcon = $label.find('.file-icon');
                $fileName = $label.find('.file-name');
                $filePreview = $label.find('.file-preview');
                $status = $(this).closest('.file-upload').find('.upload-status');

                handleFileUpload(this, $label, $filePreview, $fileIcon, $fileName, $status,
                    $progress);

            });

            // Generic file upload handler
            function handleFileUpload(input, $label, $preview, $icon, $fileNameElement, $status,
                $progress) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];

                    // Validate file size (max 5MB)
                    if (file.size > 2 * 1024 * 1024) {
                        flasher.error('Ukuran file terlalu besar. Maksimum 2MB diperbolehkan.');
                        $(input).val("");
                        return;
                    }

                    // Show progress bar
                    $progress.show();
                    const progressBar = $progress.find(".progress-bar");

                    // Simulate upload progress
                    let width = 0;
                    const interval = setInterval(() => {
                        if (width >= 100) {
                            clearInterval(interval);

                            // Update label appearance
                            $label.addClass("has-file");

                            // Show file name
                            $fileNameElement.text(file.name).show();

                            if (file.type.startsWith("image/")) {
                                // Preview image
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    if ($preview) {
                                        $preview.attr("src", e.target.result).show();
                                    }
                                };
                                reader.readAsDataURL(file);
                                $icon.hide();
                            } else {
                                if ($preview) $preview.hide();
                                $icon.attr("class",
                                        "bi bi-file-earmark-pdf-fill pdf-icon file-icon")
                                    .show();
                            }

                            // Show status
                            $status.show().html(
                                `<i class="bi bi-check-circle-fill me-1 text-success"></i><span>${file.name} - ${formatFileSize(file.size)}</span>`
                            );

                            $progress.hide();

                        } else {
                            width += 5;
                            progressBar.css("width", width + "%");
                        }
                    }, 50);
                }
            }

            // Format file size
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' bytes';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }


        });
    </script>
    <script>
        $('#sidebarform-btn').click(function(e) {
            e.preventDefault();
            $('#btn-edit').hide();
            $('#btn-tambah').show();
        });
    </script>
    @stack('js')
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('js/soft-ui-dashboard.min.js') }}"></script>
</body>

</html>
