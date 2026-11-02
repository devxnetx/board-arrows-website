@extends('layout')

@section('content')
    <article class="sheet">
        <header class="release-head">
            <h1>Admin</h1>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="choice" type="submit">Log out</button>
            </form>
        </header>

        @if (session('saved'))
            <p class="note saved">{{ session('saved') }}</p>
        @endif

        <section>
            <h2>Bugs</h2>
            <p>Pending stays on the public list with a loader. Fixed shows a check. Rejected is removed from that list. The email is only visible here.</p>
            @forelse ($reports as $report)
                <form class="task" method="post" action="{{ route('admin.bugs.update', $report) }}">
                    @csrf
                    @method('patch')
                    <p class="meta">{{ $report->surface }}@if ($report->version) · {{ $report->version }}@endif @if ($report->contact) · {{ $report->contact }}@endif</p>
                    <label class="stack">Headline
                        <input name="summary" required maxlength="160" value="{{ $report->summary }}">
                    </label>
                    <label class="stack">Explanation
                        <textarea name="details" required maxlength="5000">{{ $report->details }}</textarea>
                    </label>
                    <div class="row">
                        <button class="choice {{ $report->status === 'pending' ? 'on' : '' }}" name="status" value="pending" type="submit">Pending</button>
                        <button class="choice {{ $report->status === 'fixed' ? 'on' : '' }}" name="status" value="fixed" type="submit">Fixed</button>
                        <button class="choice {{ $report->status === 'rejected' ? 'on' : '' }}" name="status" value="rejected" type="submit">Reject</button>
                    </div>
                </form>
            @empty
                <p>No reports yet.</p>
            @endforelse
        </section>

        <section class="pay">
            <h2>Publish a version</h2>
            <p>Uploading the same version again replaces that changelog entry and its two files. The Chrome zip and the Firefox zip are different packages.</p>
            @if ($errors->any())
                <ul class="errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <form method="post" action="{{ route('admin.releases.store') }}" enctype="multipart/form-data">
                @csrf
                <label class="stack">Version
                    <input name="version" required maxlength="31" placeholder="1.2.7" value="{{ old('version') }}">
                </label>
                <label class="stack">Date
                    <input name="released_on" type="date" required value="{{ old('released_on', now()->toDateString()) }}">
                </label>
                <label class="stack">Summary
                    <input name="summary" required maxlength="300" value="{{ old('summary') }}">
                </label>
                <label class="stack">What changed, one item per line
                    <textarea name="changes" required maxlength="8000">{{ old('changes') }}</textarea>
                </label>
                <label class="stack">Chrome zip
                    <input name="chrome" type="file" accept=".zip,application/zip" required>
                </label>
                <label class="stack">Firefox zip
                    <input name="firefox" type="file" accept=".zip,application/zip" required>
                </label>
                <button class="send" type="submit">Publish on the changelog</button>
            </form>
            @if ($releases->isNotEmpty())
                <ul>
                    @foreach ($releases as $release)
                        <li>Board Arrows {{ $release->version }} · {{ $release->released_on->toDateString() }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </article>
@endsection
