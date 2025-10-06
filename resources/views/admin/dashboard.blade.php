@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="ui container" style="margin-top: 2em;">
        <h2 class="ui header">Welcome to the Admin Dashboard</h2>
        <div class="ui message">
            <p>Here you can manage all collections in the app.</p>
        </div>

        <div class="ui menu">
            <a href="{{ route('dashboard') }}" class="ui ui-button ui-corner-all"><i class="ui-icon ui-icon-home"></i>To
                regular
                dashboard</a>
            <a href="{{ route('admin.users') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-person"></i>Manage Users</a>
            <a href="{{ route('admin.logs') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-script"></i>View Logs</a>
            <a href="{{ route('admin.transactions.index') }}" class="ui ui-button ui-corner-all">
                <i class="ui-icon ui-icon-transferthick-e-w"></i>View Transactions</a>
            <a href="{{ route('admin.fiscal_records.index') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-document"></i>View Fiscal Records</a><br><br>
            <a href="{{ route('admin.companies.index') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-document"></i>View Companies</a>
            <a href="{{ route('admin.items.index') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-cart"></i>View Items</a>
            <a href="{{ route('admin.devices.index') }}" class="ui ui-button ui-corner-all"><i
                    class="ui-icon ui-icon-gear"></i>View Devices</a>
            <a href="{{ route('logout') }}" class="ui ui-button ui-corner-all"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="ui-icon ui-icon-arrowthickstop-1-w"></i>Logout
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>

    </div>
@endsection
