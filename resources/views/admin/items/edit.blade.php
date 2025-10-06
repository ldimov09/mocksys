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
    <h2 class="ui header">Edit Item ID: {{ $item->id }}</h2>

    <a href="{{ route('admin.items.index', ['user_id' => $item->user_id]) }}" class="ui ui-button ui-corner-all"
        style="margin-bottom: 1em;">
        <i class="ui-icon ui-icon-caret-1-w"></i> Back to Items
    </a>

    <form class="ui form" method="POST" action="{{ route('admin.items.update', $item->id) }}" novalidate>
        @csrf
        @method('PUT')

        <div id="accordion">
            <h3>Edit Item</h3>
            <div>
                <div class="form-grid">
                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', $item->name) }}">
                    </div>

                    <div class="field">
                        <label>Short Name</label>
                        <input type="text" name="short_name" maxlength="16"
                            value="{{ old('short_name', $item->short_name) }}">
                    </div>

                    <div class="field">
                        <label>Price</label>
                        <input type="number" name="price" min="0" step="0.01"
                            value="{{ old('price', $item->price) }}">
                    </div>

                    <div class="field">
                        <label>Number (6 digits)</label>
                        <input type="text" name="number" maxlength="6" pattern="\d{6}"
                            value="{{ old('number', $item->number) }}">
                    </div>

                    <div class="field">
                        <label>Unit</label>
                        <select name="unit" class="ui dropdown">
                            <option value="">-- Select Unit --</option>
                            <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="L" {{ old('unit', $item->unit) == 'L' ? 'selected' : '' }}>L</option>
                            <option value="g" {{ old('unit', $item->unit) == 'g' ? 'selected' : '' }}>g</option>
                            <option value="mL" {{ old('unit', $item->unit) == 'mL' ? 'selected' : '' }}>mL</option>
                            <option value="PSU" {{ old('unit', $item->unit) == 'PSU' ? 'selected' : '' }}>PSU</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 1em;">
            <button type="submit" class="ui ui-button ui-corner-all">
                <i class="ui-icon ui-icon-disk"></i> Update Item
            </button>
            <a href="{{ route('admin.items.index', ['user_id' => $item->user_id]) }}">
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
