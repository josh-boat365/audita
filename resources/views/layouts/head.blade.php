<head>

    <meta charset="utf-8" />

    <title>Audita - BPSL | Internal & Audit Control</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A name that suggests performance evaluation and improvement." name="description" />
    <meta content="BPSL" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('bpsl_imgs/Audita-short-favicon.png') }}">

    <!-- bootstrap-datepicker css -->
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

    <!-- Plugins css -->
    <link href="{{ asset('assets/libs/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" type="text/css" />

    <!-- App js -->
    <script src="{{ asset('/assets/js/plugin.js') }}"></script>

    <!-- Plugins css -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{--  Custom Styles  --}}
    <style>
        .required {
            color: orangered !important;
            font-weight: 700;
        }
    </style>




</head>
