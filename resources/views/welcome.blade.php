@extends('layouts.app')

@section('content')
<div class="ui container" style="margin-top: 3em;">
    <h1 class="ui header">Welcome to the MockSys Bank system</h1>
    <div class="ui segment">
        @guest
            <a href="{{ route('login') }}" class="ui ui-button ui-corner-all">
                Login
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="ui ui-button ui-corner-all">
                Dashboard
            </a>
        @endguest
    </div>
</div>
@endsection