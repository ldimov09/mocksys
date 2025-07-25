@extends('layouts.app')

@section('styles')
    <style>
        button:hover {
            opacity: 0.7;
        }
    </style>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            $.fn.dataTable.moment('HH:mm:ss DD.MM.YYYY UTC');

            $('#logs-table').DataTable({
                // Optional: customize pagination type, language, etc
                "pagingType": "simple_numbers",
                "language": {
                    "search": "Filter logs:",
                    "lengthMenu": "Show _MENU_ logs per page"
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
    <h2 class="ui header">System Logs</h2>
    <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
        <i class="ui-icon ui-icon-caret-1-w"></i> Back to dashboard</a>
    <div class="ui container" style="margin-top: 2em;" id="accordion">
        <h3>All logs</h3>
        <table class="ui celled table dataTable" id="logs-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Related user</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->user ? $log->user->name . ' (' . $log->user->account_number . ')' : 'Guest/Unknown' }}
                        </td>
                        <td>{{ $log->related_user ? $log->related_user->name . ' (' . $log->related_user->account_number . ')' : 'None' }}
                        </td>
                        <td>
                            @php
                                $labels = [
                                    'users' => 'blue',
                                    'error' => 'red',
                                    'authentication' => 'grey',
                                ];
                                $labelColor = $labels[$log->type] ?? 'black';
                            @endphp
                            <div style="color: {{ $labelColor }};" class="ui label">
                                {{ str_replace('_', ' ', ucfirst($log->type)) }}
                            </div>
                        </td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address ?? 'N/A' }}</td>
                        <td>{{ $log->created_at->format('H:i:s d.m.Y') }} UTC</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
