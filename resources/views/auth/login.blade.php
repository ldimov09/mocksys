@extends('layouts.app')

@section('content')
    <div class="form-box ui-widget-content">
        <h2>Login to MockSys Bank</h2>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <label for="account_number">Account Number</label>
            <input type="text" name="account_number" id="account_number" required value="{{ old('account_number', '') }}">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" >

            <label for="specialPassword">Special password (admin only)</label>
            <input type="password" name="specialPassword" id="specialPassword">

            <button type="submit" class="ui ui-button ui-corner-all">Login</button>
        </form>

        <p style="margin-top: 15px;">Don't have an account? <a style="cursor: default; color: gray; text-decoration: none;" href="#">Register here</a>.</p>
    </div>
@endsection
