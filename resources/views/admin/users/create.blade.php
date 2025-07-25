@extends('layouts.app')

@section('title', 'Create User')

@section('styles')
    <style>
        div.form-grid {
            display: grid;
            grid-template-columns: auto auto auto;
        }

        .field {
            margin: 10px 20px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#accordion').accordion({
                icons: null
            });

            $('.dropdown').selectmenu();
        });
    </script>
@endsection

@section('content')
    <h2 class="ui header">Create New User</h2>
    <form class="ui form" method="POST" action="{{ route('admin.users.store') }}" novalidate>
        <div class="ui container" style="margin-top: 2em;" id="accordion">
            <h3>New user</h3>
            <div>
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label>Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}" required>
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="field">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>

                    <div class="field">
                        <label>Role</label>
                        <select name="role" required class="dropdown">
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="business" {{ old('role') == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-select dropdown">
                            @foreach (\App\Enums\UserStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status') == $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <button class="ui ui-button ui-corner-all" type="submit">Create User</button>
        <a href="{{ route('admin.users') }}"><button class="ui ui-button ui-corner-all" type="button">Cancel</button></a>
    </form>
@endsection
