<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>MockSys Bank</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/start/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="icon" href="{{ asset('bank_icon.png') }}">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- datetime-moment plugin -->
    <script src="https://cdn.datatables.net/plug-ins/2.3.2/sorting/datetime-moment.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <style>
        body {
            background-color: #eeeeee;
            padding: 30px;
        }

        * {
            font-family: 'Courier', Courier, monospace !important;
        }

        .container {
            /*width: 400px;*/
            margin: 0 auto;
        }

        .ui-widget {
            font-size: 1em;
        }

        .form-box {
            padding: 20px;
            border: 2px solid #aaa;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        select {
            width: 100%;
            padding: 6px;
            margin-top: 5px;
            font-family: inherit;
        }

        button {
            margin-top: 15px;
            padding: 8px 12px;
            font-weight: bold;
            cursor: pointer;
        }

        td {
            padding: 5px 10px 5px 10px;
        }

        .hovered-row {
            background-color: #dedede !important;
            /* DataTables styles can be bossy */
        }

        .close {
            scale: 1.5;
        }

        .close:hover {
            opacity: 0.7;
        }

        input{
            border-radius: 6px;
            border: 1px solid gray;
        }
    </style>
    @yield('styles')


    @yield('scripts')

</head>

<body>

    <div class="container">
        @if (session('success') || session('error') || $errors->any() ?? false)
            <div class="ui container" style="margin-top: 1em;">
                @if (session('success'))
                    <div class="ui-widget" id="global-message" title="Click to close">
                        <div class="ui-state-highlight ui-corner-all"
                            style="padding: 0.7em; margin-bottom: 1em; position: relative;">
                            <span class="ui-icon ui-icon-check" style="float: left; margin-right: 0.3em;"></span>
                            <p style="margin-left: 2em;">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="ui-widget" id="global-message" title="Click to close">
                        <div class="ui-state-error ui-corner-all"
                            style="padding: 0.7em; margin-bottom: 1em; position: relative;">
                            <span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
                            <p style="margin-left: 2em;">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="ui-widget" id="global-message" title="Click to close">
                        <div class="ui-state-error ui-corner-all"
                            style="padding: 0.7em; margin-bottom: 1em; position: relative;">
                            <span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span>
                            <ul class="list" style="margin-left: 2em;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </div>
</body>
<script>
    const validationErrors = @json($errors->toArray());
</script>
<script>
    $(document).ready(function() {
        $('#global-message').on('click', function() {
            $(this).remove();
        });

        $(document).tooltip({
            track: true
        });
    });

    $(document).on('mouseenter', 'table.dataTable tbody td', function() {
        $(this).closest('tr').addClass('hovered-row');
    });

    $(document).on('mouseleave', 'table.dataTable tbody td', function() {
        $(this).closest('tr').removeClass('hovered-row');
    });
</script>
<script>
    $(function () {
        // Highlight invalid fields and show messages
        if (typeof validationErrors === 'object') {
            for (let fieldName in validationErrors) {
                let message = validationErrors[fieldName][0];

                let $field = $('[name="' + fieldName + '"]');

                if ($field.length) {
                    $field.css({
                        'border': '1px solid red',
                        'background-color': '#ffe7e7'
                    });

                    // Avoid duplicate messages
                    if ($field.next('.field-error').length === 0) {
                        $('<div class="field-error" style="color: red; font-size: 0.85em; margin-top: 3px;">' + message + '</div>')
                            .insertAfter($field);
                    }
                }
            }
        }
    });
</script>

</html>
