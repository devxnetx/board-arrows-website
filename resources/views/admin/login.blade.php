@extends('layout')

@section('content')
    <article class="sheet">
        <h1>Admin</h1>
        @if ($configured)
            <p>This page manages bug reports and publishes changelog builds.</p>
        @else
            <p>Set <code>ADMIN_PASSWORD</code> in the server <code>.env</code> before this panel will open.</p>
        @endif

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('admin.login.store') }}">
            @csrf
            <label class="stack">Password
                <input name="password" type="password" required autocomplete="current-password">
            </label>
            <button class="send" type="submit">Open the panel</button>
        </form>
    </article>
@endsection
