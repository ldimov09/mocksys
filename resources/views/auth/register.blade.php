@extends('layouts.app')

@section('content')
    <h1> This page is no longer active! Please return back! </h1>
@endsection


{{-- 

<div class="form-box ui-widget-content">
        <h2>Register for MockSys Bank</h2>

        @if ($errors->any())
            <div class="error-msg">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" >

            <label for="account_number">Account Number</label>
            <input type="text" name="account_number" id="account_number" >

            <label for="email">Email</label>
            <input type="email" name="email" id="email" >

            <label for="password">Password</label>
            <input type="password" name="password" id="password" >

            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" >

            <label for="role">Role</label>
            <select name="role" id="role" >
                <option value="user">User</option>
                <option value="business">Business</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit">Register</button>
        </form>

        <p style="margin-top: 15px;">Already have an account? <a href="{{ route('login') }}">Login here</a>.</p>
    </div>

--}}