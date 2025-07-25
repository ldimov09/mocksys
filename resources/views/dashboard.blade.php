@extends('layouts.app')

@section('styles')
    <style>
        form {
            width: 400px;
        }

        .accordion {
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#user-logs-table').DataTable({
                pageLength: 10,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [1, 2, 3]
                }]
            });



            $(".key-btn").on("click", function() {
                currentUserId = $(this).data("user-id");

                // Fill in modal values
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

            $('.accordion').accordion({
                icons: null,
            });
        });
    </script>
@endsection

@section('content')
    <div class="ui container" style="margin-top: 2em;">
        <h2 class="ui header">Welcome, {{ $user->name }}!</h2>

        <div class="accordion">
            <h3>Main info</h3>
            <div>
                <h3>Your balance: <strong>Ʉ{{ number_format($user->balance, 2) }}</strong></h3>
                <h5>Your current status: <strong
                        style="color: {{ $user->status->color() }}">{{ $user->status->label() }}</strong></h5>
                <h5>Your current role: <strong>{{ ucfirst($user->role) }}</strong></h5>
            </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>

        <div class="accordion">
            <h3>Make a Transfer</h3>
            <div>
                <form class="ui" method="POST" action="{{ route('transfer') }}" novalidate>
                    @csrf
                    <div class="field">
                        <label>Receiver Account Number</label>
                        <input type="text" name="receiver_account" value="{{ old('receiver_account') }}" required>
                    </div>

                    <div class="field">
                        <label>Amount (in Ʉ)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required>
                    </div>

                    <div class="field">
                        <label>Your PIN (Password)</label>
                        <input type="password" name="pin" required>
                    </div>

                    <button class="ui ui-button ui-corner-all ui-corner-all" type="submit">Send Transfer</button>
                </form>
            </div>
        </div>

        <div class="accordion">
            <h3 class="ui header">Transaction & Login History</h3>

            <table id="user-logs-table" class="ui celled table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Other Party</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->logs()->get()->merge($user->transactions()->get()) as $entry)
                        <tr>
                            <td>{{ $entry->created_at->format('Y-m-d H:i:s') }} UTC</td>
                            <td>
                                @php
                                    $labels = [
                                        'card_payment' => 'green',
                                        'transfer' => 'blue',
                                        'transfer_fail' => 'red',
                                        'authentication' => 'grey',
                                    ];
                                    $labelColor = $labels[$entry->type] ?? 'black';
                                @endphp
                                <div style="color: {{ $labelColor }};" class="ui label">
                                    {{ str_replace('_', ' ', ucfirst($entry->type)) }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $labelsStatus = [
                                        'pending' => 'gray',
                                        'approved' => 'green',
                                        'declined' => 'red',
                                    ];
                                    $labelColorStatus = $labelsStatus[$entry->status] ?? 'black';
                                @endphp
                                <div style="color: {{ $labelColorStatus }};" class="ui label">
                                    {{ str_replace('_', ' ', ucfirst($entry->status ?? '—')) }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $related_user_name = array_key_exists('related_user_id', $entry->toArray())
                                        ? $entry->related_user?->name ?? '—'
                                        : ($entry->sender?->id === $user->id
                                            ? $entry->receiver?->name . ' received' ?? '—'
                                            : $entry->sender?->name . ' sent' ?? '—');
                                    $related_user_account_number = array_key_exists(
                                        'related_user_id',
                                        $entry->toArray(),
                                    )
                                        ? $entry->related_user?->account_number ?? '—'
                                        : ($entry->sender?->id === $user->id
                                            ? $entry->receiver?->account_number ?? '—'
                                            : $entry->sender?->account_number ?? '—');
                                @endphp
                                {{ $related_user_name }}( {{ $related_user_account_number }} )
                            </td>
                            <td>{{ $entry->description ?? 'Transaction Ʉ' . number_format($entry->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($user->role === 'business')
            <div class="ui segment">
                <h3 class="ui header">Transaction & Fiscal keys</h3>
                @if (!$user->keys_locked_by_admin)
                    <button class="ui ui-button ui-corner-all key-btn" data-user-id="{{ $user->id }}"
                        data-user-name="{{ $user->name }}" data-transaction-key="{{ $user->transaction_key }}"
                        data-fiscal-key="{{ $user->fiscal_key }}"
                        data-transaction-enabled="{{ $user->transaction_key_enabled }}"
                        data-fiscal-enabled="{{ $user->fiscal_key_enabled }}" title="Manage Keys">
                        <span class="ui-icon ui-icon-key"></span> Manage keys
                    </button>
                @else
                    <small style="color: red">Your keys have been locked. You cannot change them or see them. If you believe
                        that that
                        is a mistake, please contact an administrator.</small>
                @endif
            </div>
        @endif


        @if (auth()->user()->role == 'admin')
            <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all"><i class="ui-icon ui-icon-unlocked"></i>To admin
                dashboard</a>
        @endif
        <a href="{{ route('logout') }}" class="ui ui-button ui-corner-all"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="ui-icon ui-icon-arrowthickstop-1-w"></i>Logout
        </a>

        <div id="key-management-dialog" title="Manage Business Keys" style="display: none;">
            <div class="ui form">
                <div class="field">
                    <label>Transaction Key</label>
                    <small style="color: red" class="transaction-key-disabled-text">Disabled</small>
                    <input type="text" id="transaction-key" readonly>
                    <div class="ui buttons">
                        <button class="ui button" id="generate-transaction-key">Generate</button>
                        <button class="ui button" id="toggle-transaction-key">Disable</button>
                    </div>
                </div>
                <div class="field">
                    <label>Fiscal Key</label>
                    <small style="color: red" class="fiscal-key-disabled-text">Disabled</small>
                    <input type="text" id="fiscal-key" readonly>
                    <div class="ui buttons">
                        <button class="ui button" id="generate-fiscal-key">Generate</button>
                        <button class="ui button" id="toggle-fiscal-key">Disable</button>
                    </div>
                </div>
            </div>

            <small style="color:red;" id="error-msgbox"></small>
            <small style="color:green;" id="success-msgbox"></small>
        </div>
    </div>
@endsection
