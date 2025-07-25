@extends('layouts.app')

@section('content')
    <div class="ui segment">
        <h2 class="ui header">Companies</h2>
        <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
            <i class="ui-icon ui-icon-caret-1-w"></i> Back to dashboard</a>
        <a href="{{ route('admin.companies.create') }}" class="ui ui-button ui-corner-all"><i
                class="ui-icon ui-icon-circle-plus"></i>
            Create New
            Company</a>


        <div class="ui container" style="margin-top: 2em;" id="accordion">
            <h3>All companies</h3>
            <table id="companies-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Account</th>
                        <th>Manager Name</th>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Address</th>
                        <th>Legal Form</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $company)
                        @php
                            $map = [
                                'ad' => 'PLC', // Public Limited Company (АД)
                                'ead' => 'Sole PLC', // Sole-owned Public Limited Company (ЕАД)
                                'eood' => 'Ltd (Sole)', // Sole Proprietor Ltd. (ЕООД)
                                'et' => 'Sole Trader', // Sole Trader (ЕТ)
                                'ood' => 'Ltd', // Private Limited Company (ООД)
                            ];
                        @endphp
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td>{{ $company->account->account_number }}</td>
                            <td>{{ $company->manager_name }}</td>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->number }}</td>
                            <td>{{ $company->address }}</td>
                            <td>{{ $map[$company->legal_form] }}</td>
                            <td>{{ $company->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.companies.edit', $company->id) }}" title="Edit"
                                    style="text-decoration: none;">
                                    <button class="ui ui-button ui-corner-all" title="Edit">
                                        <span class="ui-icon ui-icon-pencil"></span>
                                    </button>
                                </a>
                                <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST"
                                    style="display:inline;" class="delete-company-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui ui-button ui-corner-all" title="Delete">
                                        <span class="ui-icon ui-icon-trash"></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="confirm-dialog" title="Please confirm" style="display: none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:2px 7px 50px 0;"></span>
            Are you sure you want to delete this company?</p>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#companies-table').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                responsive: true
            });

            $('#accordion').accordion({
                icons: null
            });

            let formToSubmit = null;

            $(".delete-company-form").on("submit", function(e) {
                e.preventDefault(); // Stop form from submitting
                formToSubmit = this; // Save the form so we can submit it later

                $("#confirm-dialog").dialog({
                    resizable: false,
                    draggable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    buttons: {
                        "Yes, delete": function() {
                            $(this).dialog("close");
                            formToSubmit.submit(); // Now submit the form
                        },
                        Cancel: function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        });
    </script>
@endsection
