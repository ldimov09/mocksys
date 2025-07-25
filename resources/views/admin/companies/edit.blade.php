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
    <h2 class="ui header">Update company</h2>
    <form class="ui form" method="POST" action="{{ route('admin.companies.update', $company->id) }}" novalidate>
        @csrf
        @method('PUT')
        <div id="accordion">
            <h3>Edit Company</h3>
            <div>
                <div class="form-grid">
                    <div class="field">
                        <label>Manager Name</label>
                        <input type="text" name="manager_name" value="{{ old('manager_name', $company->manager_name) }}"
                            required>
                    </div>

                    <div class="field">
                        <label>Company Name</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required>
                    </div>

                    <div class="field">
                        <label>Company Number (with self-check digit)</label>
                        <input type="text" value="{{ $company->number }}" disabled>
                        <input type="hidden" name="number" value="{{ $company->number }}">
                    </div>

                    <div class="field">
                        <label>Address</label>
                        <input type="text" name="address" value="{{ old('address', $company->address) }}" required>
                    </div>

                    <div class="field">
                        <label>Legal form</label>
                        <select name="legal_form" class="ui dropdown" disabled>
                            <option value="">-- Select Legal form --</option>
                            <option value="ad" {{ $company->legal_form == 'ad' ? 'selected' : '' }}>АД</option>
                            <option value="ead" {{ $company->legal_form == 'ead' ? 'selected' : '' }}>ЕАД</option>
                            <option value="eood" {{ $company->legal_form == 'eood' ? 'selected' : '' }}>ЕООД</option>
                            <option value="et" {{ $company->legal_form == 'et' ? 'selected' : '' }}>ЕТ</option>
                            <option value="ood" {{ $company->legal_form == 'ood' ? 'selected' : '' }}>ООД</option>
                        </select>
                        <input type="hidden" name="legal_form" value="{{ $company->legal_form }}">
                    </div>

                    <div class="field">
                        <label>Associated Business Account</label>
                        <select name="account_id" class="ui dropdown" required>
                            <option value="">-- Select Business User --</option>
                            @foreach ($businessUsers as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('account_id', $company->account_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->account_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="ui ui-button ui-corner-all">Update Company</button>
        <a href="{{ route('admin.companies.index') }}"><button class="ui ui-button ui-corner-all"
                type="button">Cancel</button></a>
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
            }); // optional, if you're using Semantic UI's dropdown
        });
    </script>
@endsection
