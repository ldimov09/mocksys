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
    <div class="ui segment">
        <h2 class="ui header">Create New Company</h2>

        <form class="ui form" method="POST" action="{{ route('admin.companies.store') }}" novalidate>
            <div id="accordion">
                <h3>Company Information</h3>
                <div>
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label>Manager Name</label>
                            <input type="text" name="manager_name" value="{{ old('manager_name') }}" required>
                        </div>

                        <div class="field">
                            <label>Company Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="field">
                            <label>Company Number</label>
                            <input type="text" name="number" value="{{ old('number') }}" required>
                        </div>

                        <div class="field">
                            <label>Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" required>
                        </div>

                        <div class="field">
                            <label>Legal form</label>
                            <select name="legal_form" class="ui dropdown" required>
                                <option value="">-- Select Legal form --</option>
                                <option value="ad" {{ old('legal_form') == 'ad' ? 'selected' : '' }}>АД</option>
                                <option value="ead" {{ old('legal_form') == 'ead' ? 'selected' : '' }}>ЕАД</option>
                                <option value="eood" {{ old('legal_form') == 'eood' ? 'selected' : '' }}>ЕООД </option>
                                <option value="et" {{ old('legal_form') == 'et' ? 'selected' : '' }}>ЕТ</option>
                                <option value="ood" {{ old('legal_form') == 'ood' ? 'selected' : '' }}>ООД</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Associated Business Account</label>
                            <select name="account_id" class="ui dropdown" required>
                                <option value="">-- Select Business User --</option>
                                @foreach ($businessUsers as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('account_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            <button type="submit" class="ui ui-button ui-corner-all">Create Company</button>
            <a href="{{ route('admin.companies.index') }}"><button class="ui ui-button ui-corner-all"
                    type="button">Cancel</button></a>
        </form>
    </div>
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
            }); // optional, if you're using Semantic UI's dropdown
        });
    </script>
@endsection
