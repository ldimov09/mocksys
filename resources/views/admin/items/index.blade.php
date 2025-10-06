@extends('layouts.app')

@section('content')
    <div class="ui segment">
        <h2 class="ui header">Items</h2>
        <a href="{{ route('admin.dashboard') }}" class="ui ui-button ui-corner-all">
            <i class="ui-icon ui-icon-caret-1-w"></i> Back to Dashboard</a>

        <form action="{{ route('admin.items.index') }}" method="GET" style="display:inline-block; margin-left:1em;">
            <label for="user_id">Business User:</label>
            <select name="user_id" id="user_id" onchange="this.form.submit()">
                <option value="">-- Select --</option>
                @foreach ($businessUsers as $user)
                    <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->account_number ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedUserId)
            <a href="{{ route('admin.items.create', ['user_id' => $selectedUserId]) }}" 
               class="ui ui-button ui-corner-all">
                <i class="ui-icon ui-icon-circle-plus"></i> Create New Item
            </a>

            <div class="ui container" style="margin-top: 2em;" id="accordion">
                <h3>Items for selected business</h3>
                <table id="items-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Price</th>
                            <th>Number</th>
                            <th>Unit</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->short_name }}</td>
                                <td>{{ $item->price }}</td>
                                <td>{{ $item->number }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.items.edit', $item->id) }}" title="Edit">
                                        <button class="ui ui-button ui-corner-all">
                                            <span class="ui-icon ui-icon-pencil"></span>
                                        </button>
                                    </a>
                                    <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST"
                                          style="display:inline;" class="delete-item-form">
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
        @endif
    </div>

    <div id="confirm-dialog" title="Please confirm" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:2px 7px 50px 0;"></span>
            Are you sure you want to delete this item?</p>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#items-table').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                responsive: true
            });

            $('#accordion').accordion({ icons: null });

            let formToSubmit = null;
            $(".delete-item-form").on("submit", function(e) {
                e.preventDefault();
                formToSubmit = this;

                $("#confirm-dialog").dialog({
                    resizable: false,
                    draggable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    buttons: {
                        "Yes, delete": function() {
                            $(this).dialog("close");
                            formToSubmit.submit();
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
