@extends('layouts.app')

@section('styles')
    <style>
        div.form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .field {
            margin: 10px 20px;
        }
    </style>
@endsection

@section('content')
    <h2 class="ui header">Create New Device</h2>

    <a href="{{ route('admin.devices.index', ['user_id' => $userId]) }}" class="ui ui-button ui-corner-all" style="margin-bottom: 1em;">
        <i class="ui-icon ui-icon-caret-1-w"></i> Back to Devices
    </a>

    <form class="ui form" method="POST" action="{{ route('admin.devices.store') }}" novalidate>
        @csrf
        <input type="hidden" name="user_id" value="{{ $userId }}">

        <div id="accordion">
            <h3>Device Information</h3>
            <div>
                <div class="form-grid">
                    <div class="field">
                        <label>Device Name</label>
                        <input type="text" name="device_name" value="{{ old('device_name') }}">
                    </div>

                    <div class="field">
                        <label>Device Address</label>
                        <input type="text" name="device_address" value="{{ old('device_address') }}">
                    </div>

                    <div class="field">
                        <label>Description</label>
                        <input type="text" name="description" value="{{ old('description') }}">
                    </div>

                    <div class="field">
                        <label>Number</label>
                        <input type="text" name="number" maxlength="50" value="{{ old('number') }}">
                    </div>

                    <div class="field">
                        <label>Status</label>
                        <select name="status" class="ui dropdown">
                            <option value="enabled" {{ old('status') === 'enabled' ? 'selected' : '' }}>Enabled</option>
                            <option value="disabled" {{ old('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 1em;">
            <button type="submit" class="ui ui-button ui-corner-all">
                <i class="ui-icon ui-icon-check"></i> Save Device
            </button>
            <a href="{{ route('admin.devices.index', ['user_id' => $userId]) }}">
                <button class="ui ui-button ui-corner-all" type="button">
                    <i class="ui-icon ui-icon-close"></i> Cancel
                </button>
            </a>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#accordion').accordion({
                icons: null,
                heightStyle: "content",
            });

            $('.ui.dropdown').selectmenu({
                width: 500,
            });
        });
    </script>
@endsection
