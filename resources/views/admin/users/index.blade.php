@extends('layouts.app')

@section('title', 'Manage Users')

@section('styles')
    <style>
        .delete-btn {
            font-size: 18px;
            padding: 5px !important;
            color: maroon;
            border: 1px solid maroon;
        }

        .edit-btn {
            font-size: 18px;
            padding: 5px !important;
            color: darkgoldenrod;
            border: 1px solid darkgoldenrod;
        }

        .keys-btn {
            font-size: 18px;
            padding: 5px !important;
            color: darkcyan;
            border: 1px solid darkcyan;
        }

        button:hover {
            opacity: 0.7;
        }
    </style>
@endsection


@section('scripts')

    <script>
        $(document).ready(function() {
            $('input[type="checkbox"]').checkboxradio();


            $('#users-table').DataTable({
                // Optional: customize pagination type, language, etc
                "pagingType": "simple_numbers",
                "language": {
                    "search": "Filter users:",
                    "lengthMenu": "Show _MENU_ users per page"
                }
            });

            $('#accordion').accordion({
                icons: null
            });

            let currentUserId = null;

            $(".key-btn").on("click", function() {
                currentUserId = $(this).data("user-id");
                userRole = $(this).data("user-role");

                if(userRole == "business"){
                    $("#fiscalKey").css("display", "block");
                } else {
                    $("#fiscalKey").css("display", "none");
                }

                // Fill in modal values
                $("#modal-user-name").text($(this).data("user-name"));
                $("#transaction-key").val($(this).data("transaction-key"));
                $("#fiscal-key").val($(this).data("fiscal-key"));

                $("#toggle-transaction-key").text(
                    $(this).data("transaction-enabled") ? "Disable" : "Enable"
                );

                $('.transaction-key-disabled-text').css('display', $(this).data("transaction-enabled") ?
                    "none" : "inline")
                $('.fiscal-key-disabled-text').css('display', $(this).data("fiscal-enabled") ? "none" :
                    "inline")

                $("#toggle-fiscal-key").text(
                    $(this).data("fiscal-enabled") ? "Disable" : "Enable"
                );

                $("#lock-keys-toggle").prop("checked", $(this).data("locked"));
                $('input[type="checkbox"]').checkboxradio({
                    checked: $(this).data("locked")
                });

                $("#key-management-dialog").dialog({
                    modal: true,
                    resizable: false,
                    draggable: false,
                    width: 500,
                });
            });

            function showSuccess(message) {
                $("#success-msgbox").text(message).show();
                $("#error-msgbox").hide();
            }

            function showError(message) {
                $("#error-msgbox").text(message).show();
                $("#success-msgbox").hide();
            }

            $("#generate-transaction-key").click(function() {
                $.post(`/keys/${currentUserId}/transaction/reset`, {
                    _token: '{{ csrf_token() }}'
                }).done(function(data) {
                    $("#transaction-key").val(data.transaction_key);
                    showSuccess("Transaction key regenerated successfully.");
                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.error ||
                        "Something went wrong while generating transaction key.");
                });
            });

            $("#generate-fiscal-key").click(function() {
                $.post(`/keys/${currentUserId}/fiscal/reset`, {
                    _token: '{{ csrf_token() }}'
                }).done(function(data) {
                    $("#fiscal-key").val(data.fiscal_key);
                    showSuccess("Fiscal key regenerated successfully.");
                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.error ||
                        "Something went wrong while generating fiscal key.");
                });
            });

            $("#toggle-transaction-key").click(function() {
                $.post(`/keys/${currentUserId}/transaction/toggle`, {
                    _token: '{{ csrf_token() }}'
                }).done(function(data) {
                    $("#toggle-transaction-key").text(data.enabled ? "Disable" : "Enable");
                    $('.transaction-key-disabled-text').css('display', data.enabled ? "none" :
                        "inline");
                    showSuccess("Transaction key toggled successfully.");
                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.error || "Failed to toggle transaction key.");
                });
            });

            $("#toggle-fiscal-key").click(function() {
                $.post(`/keys/${currentUserId}/fiscal/toggle`, {
                    _token: '{{ csrf_token() }}'
                }).done(function(data) {
                    $("#toggle-fiscal-key").text(data.enabled ? "Disable" : "Enable");
                    $('.fiscal-key-disabled-text').css('display', data.enabled ? "none" : "inline");
                    showSuccess("Fiscal key toggled successfully.");
                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.error || "Failed to toggle fiscal key.");
                });
            });

            $("#lock-keys-toggle").click(function() {
                $.post(`/admin/keys/${currentUserId}/lock`, {
                    _token: '{{ csrf_token() }}',
                    locked: this.checked ? 1 : 0
                }).done(function(data) {
                    $("#lock-keys-toggle").prop('checked', !!Number(data.locked));
                    showSuccess("Key lock status updated.");
                }).fail(function(xhr) {
                    showError(xhr.responseJSON?.error || "Failed to update lock status.");
                });
            });

            let formToSubmit = null;

            $(".delete-user-form").on("submit", function(e) {
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

@section('content')
    <h2 class="ui header">Users</h2>
    <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
        <i class="ui-icon ui-icon-caret-1-w"></i> Back to dashboard</a>
    <a href="{{ route('admin.users.create') }}" class="ui ui-button ui-corner-all"><i class="ui-icon ui-icon-circle-plus"></i>
        Create New
        User</a>

    <div class="ui container" style="margin-top: 2em;" id="accordion">
        <h3>All users</h3>
        <table class="dataTable" style="margin-top: 1em;" id="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Account Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr @if ($user->role === 'admin') style="background-color: #f9f9f9;" @endif>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->account_number }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>Ʉ{{ number_format($user->balance, 2) }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td style="color: {{ $user->status->color() }}">{{ $user->status->label() }}</td>
                        <td style="">
                            <a href="{{ route('admin.users.edit', $user->id) }}" title="Edit"
                                style="text-decoration: none;">
                                <button class="ui ui-button ui-corner-all" title="Edit">
                                    <span class="ui-icon ui-icon-pencil"></span>
                                </button>
                            </a>
                            <button class="ui ui-button ui-corner-all key-btn" data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-role="{{ $user->role }}"
                                data-transaction-key="{{ $user->transaction_key }}"
                                data-fiscal-key="{{ $user->fiscal_key }}"
                                data-transaction-enabled="{{ $user->transaction_key_enabled }}"
                                data-fiscal-enabled="{{ $user->fiscal_key_enabled }}"
                                data-locked="{{ $user->keys_locked_by_admin }}" title="Manage Keys">
                                <span class="ui-icon ui-icon-key"></span>
                            </button>
                            @if ($user->role !== 'admin')
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    style="display:inline;" class="delete-user-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui ui-button ui-corner-all" title="Delete">
                                        <span class="ui-icon ui-icon-trash"></span>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="key-management-dialog" title="Manage Business Keys" style="display: none;">
        <div class="ui form">
            <p><strong>User:</strong> <span id="modal-user-name"></span></p>
            <div class="field">
                <label>Transaction Key</label>
                <small style="color: red" class="transaction-key-disabled-text">Disabled</small>
                <input type="text" id="transaction-key" readonly>
                <div class="ui">
                    <button class="ui ui-button ui-corner-all" id="generate-transaction-key">Generate</button>
                    <button class="ui ui-button ui-corner-all" id="toggle-transaction-key">Disable</button>
                </div>
            </div>
            <div class="field" id="fiscalKey">
                <label>Fiscal Key</label>
                <small style="color: red" class="fiscal-key-disabled-text">Disabled</small>
                <input type="text" id="fiscal-key" readonly>
                <div class="ui buttons">
                    <button class="ui ui-button ui-corner-all" id="generate-fiscal-key">Generate</button>
                    <button class="ui ui-button ui-corner-all" id="toggle-fiscal-key">Disable</button>
                </div>
            </div>

            <div class="field">
                <label for="lock-keys-toggle">Lock keys from user control</label>
                <input type="checkbox" style="display: inline" id="lock-keys-toggle">
            </div>

            <small style="color:red;" id="error-msgbox"></small>
            <small style="color:green;" id="success-msgbox"></small>
        </div>
    </div>

    <div id="confirm-dialog" title="Please confirm" style="display: none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:2px 7px 50px 0;"></span>
            Are you sure you want to delete this user?</p>
    </div>
@endsection
