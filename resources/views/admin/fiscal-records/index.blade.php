@extends('layouts.app')

@section('styles')
    <style>
        button:hover {
            opacity: 0.7;
        }

        td.details-control {
            cursor: pointer;
            color: #2185d0;
        }

        .details-row {
            background-color: #f9f9f9;
        }

        .item {
            cursor: pointer !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Activate Semantic UI tabs
            $('#tabs').tabs();
            $.fn.dataTable.moment('HH:mm:ss DD.MM.YYYY UTC');

            // Init DataTables for both tables
            initTable('#fiscalized-table');
            initTable('#non-fiscalized-table');

            function initTable(selector) {
                const table = $(selector).DataTable({
                    pagingType: "simple_numbers",
                    language: {
                        search: "Filter records:",
                        lengthMenu: "Show _MENU_ records per page"
                    }
                });

                // Row details toggle
                $(selector + ' tbody').on('click', 'td.details-control', function() {
                    const tr = $(this).closest('tr');
                    const row = table.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        const data = $(this).data('items');
                        const total = $(this).data('total');
                        const signature = $(this).data('signature');
                        const html = renderItemsTable(data, total, signature);
                        row.child(html, 'details-row').show();
                        tr.addClass('shown');
                    }
                });
            }

            function renderItemsTable(itemsJson, total, signature) {
                let items = [];

                try {
                    items = typeof itemsJson === 'string' ? JSON.parse(itemsJson) : itemsJson;
                } catch (e) {
                    console.error("Invalid items JSON", e);
                }

                let html = `
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                items.forEach(item => {
                    const subtotal = (item.quantity * item.price).toFixed(2);
                    html += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td>$${Number(item.price).toFixed(2)}</td>
                            <td>$${subtotal}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">Total</th>
                                <th>$${parseFloat(total).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <div style="margin-top: 1em;">
                        <strong>Fiscal Signature:</strong>
                        <pre style="white-space: pre-wrap; font-family: monospace;">${signature}</pre>
                    </div>
                `;

                return html;
            }
        });
    </script>
@endsection

@section('content')
    <div class="ui container" style="margin-top: 2em;">
        <h2 class="ui header">Fiscal Records</h2>

        <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
            <i class="ui-icon ui-icon-caret-1-w"></i> Back to dashboard </a> <br /><br />
        <div id="tabs">
            <ul>
                <li><a class="item active" data-tab="fiscalized" href="#fiscalized-table-tab">Approved</a></li>
                <li><a class="item" data-tab="others" href="#non-fiscalized-table-tab">Other</a></li>
            </ul>

            <div class="ui bottom attached tab segment active" data-tab="fiscalized" id="fiscalized-table-tab">
                <table class="ui celled table" id="fiscalized-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Payment type</th>
                            <th>Company name</th>
                            <th>Transaction</th>
                            <th>Business</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $map = [
                                'ad' => 'PLC', // Public Limited Company (АД)
                                'ead' => 'Sole PLC', // Sole-owned Public Limited Company (ЕАД)
                                'eood' => 'Ltd (Sole)', // Sole Proprietor Ltd. (ЕООД)
                                'et' => 'Sole Trader', // Sole Trader (ЕТ)
                                'ood' => 'Ltd', // Private Limited Company (ООД)
                            ];
                        @endphp
                        @foreach ($fiscalRecords->where('status', 'fiscalized') as $record)
                            <tr>
                                <td class="details-control" data-items="{{ $record->items }}"
                                    data-total="{{ $record->total }}" data-signature="{{ $record->fiscal_signature }}">
                                    <i class="ui-icon ui-icon-caret-1-s"></i> More info
                                </td>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->transaction_id ? 'Card' : 'Cash' }}</td>
                                <td>{{ $record->company->name . ' ' . $map[$record->company->legal_form] }}</td>
                                <td>{!! $record->transaction_id ? '#' . $record->transaction_id : '<i>No transaction</i>' !!}</td>
                                <td>{{ $record->business->name ?? 'N/A' }}
                                    ({{ $record->business->account_number ?? 'N/A' }})
                                </td>
                                <td>
                                    <div class="ui label" style="color: green;">Fiscalized</div>
                                </td>
                                <td>{{ $record->created_at->format('H:i:s d.m.Y') }} UTC</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="ui bottom attached tab segment" data-tab="others" id="non-fiscalized-table-tab">
                <table class="ui celled table" id="non-fiscalized-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Transaction</th>
                            <th>Business</th>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fiscalRecords->where('status', '!=', 'fiscalized') as $record)
                            <tr>
                                <td class="details-control" data-items="{{ $record->items }}"
                                    data-total="{{ $record->total }}" data-signature="{{ $record->fiscal_signature }}">
                                    <i class="ui-icon ui-icon-caret-1-s"></i> More info
                                </td>
                                <td>{{ $record->id }}</td>
                                <td>#{{ $record->transaction_id }}</td>
                                <td>{{ $record->business->name ?? 'N/A' }}
                                    ({{ $record->business->account_number ?? 'N/A' }})
                                </td>
                                @php
                                    $statusColors = [
                                        'pending' => 'blue',
                                        'cancelled' => 'orange',
                                        'error' => 'red',
                                    ];
                                    $color = $statusColors[$record->status] ?? 'grey';
                                @endphp
                                <td>
                                    <div class="ui label" style="color:  {{ $color }};">
                                        {{ ucfirst($record->status) }}</div>
                                </td>
                                <td> {{ $record->error }}</td>
                                <td>{{ $record->created_at->format('H:i:s d.m.Y') }} UTC</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
