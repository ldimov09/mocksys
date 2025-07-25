@extends('layouts.app')

@section('styles')
    <style>
        button:hover {
            opacity: 0.7;
        }
    </style>
@endsection

@section('scripts')
    <script defer>
        $(document).ready(function() {
            $.fn.dataTable.moment('HH:mm:ss DD.MM.YYYY UTC');

            $('#transactions-table').DataTable({
                pagingType: "simple_numbers",
                language: {
                    search: "Filter transactions:",
                    lengthMenu: "Show _MENU_ transactions per page"
                }
            });

            $('#accordion').accordion({
                icons: null,
                heightStyle: "content"
            });
        });
    </script>
@endsection

@section('content')
    <h2 class="ui header">Transactions</h2> 
    <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
        <i class="ui-icon ui-icon-caret-1-w"></i> Back to dashboard</a>
    <div class="ui container" style="margin-top: 2em;" id='accordion'>
        <h3>All transactions</h3>
        <table class="ui celled table dataTable" id="transactions-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Signature</th>
                    <th>Error</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>
                            {{ optional($transaction->sender)->name ?? 'N/A' }} 
                            ({{ optional($transaction->sender)->account_number ?? 'N/A' }})
                        </td>
                        <td>
                            {{ optional($transaction->receiver)->name ?? 'N/A' }} 
                            ({{ optional($transaction->receiver)->account_number ?? 'N/A' }})
                        </td>
                        <td>Ʉ{{ number_format($transaction->amount, 2) }}</td>
                        <td>
                            @php
                                $colors = [
                                    'approved' => 'green',
                                    'pending' => 'blue',
                                    'declined' => 'red',
                                    'refunded' => 'orange',
                                ];
                                $color = $colors[$transaction->status] ?? 'grey';
                            @endphp
                            <div class="ui label" style="color: {{ $color }}">{{ ucfirst($transaction->status) }}</div>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
                        <td style="font-family: monospace; font-size: 0.9em;">
                            {{ Str::limit($transaction->signature, 20) }}
                        </td>
                        <td>{{ $transaction->error ?? '-' }}</td>
                        <td>{{ $transaction->created_at->format('H:i:s d.m.Y') }} UTC</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
