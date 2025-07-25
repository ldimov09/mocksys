@extends('layouts.app')

@section('title', 'Edit User')

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

@section('content')
    <div class="ui container" style="margin-top: 2em;">
        <h2 class="ui header">Edit User</h2>

        @if ($errors->any())
            <div class="ui negative message">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="ui form" method="POST" action="{{ route('admin.users.update', $user->id) }}" novalidate>
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="field">
                    <label>Password <small>(leave blank to keep current)</small></label>
                    <input type="password" name="password" autocomplete="new-password">
                </div>

                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="field">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }} {{ old('role', $user->role) == 'admin' ? 'disabled' : '' }}>User</option>
                        <option value="business" {{ old('role', $user->role) == 'business' ? 'selected' : '' }} {{ old('role', $user->role) == 'admin' ? 'disabled' : '' }}>Business
                        </option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        @foreach (\App\Enums\UserStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $user->status) == $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="ui ui-button ui-corner-all" type="submit">Update User</button>
            <a href="{{ route('admin.users') }}" class="ui"><button class="ui ui-button ui-corner-all"
                    type="button">Cancel</button></a>
        </form>
    </div>
@endsection
