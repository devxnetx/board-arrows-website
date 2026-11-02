@extends('layout')

@section('content')
    <article class="sheet">
        <h1>Report a bug</h1>
        <p>Say what went wrong, and what it should do instead. The headline and the explanation are shown in the list below. The email is not. Leave out passwords and payment details.</p>

        @if (session('reported'))
            <p class="note saved">Saved. Thank you. It is in the list as pending.</p>
        @endif

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('bugs.store') }}">
            @csrf
            <label class="stack">What should be fixed
                <input name="summary" required maxlength="160" value="{{ old('summary') }}">
            </label>
            <label class="stack">What happened
                <textarea name="details" required maxlength="5000">{{ old('details') }}</textarea>
            </label>
            <label class="stack">Where
                <select name="surface" required>
                    @foreach ($surfaces as $value => $label)
                        <option value="{{ $value }}" @selected(old('surface') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="stack">Add-on version, if you know it
                <input name="version" maxlength="40" placeholder="1.2.6" value="{{ old('version') }}">
            </label>
            <label class="stack">Email, if you want a reply
                <input name="contact" type="email" maxlength="160" value="{{ old('contact') }}" autocomplete="email">
            </label>
            <div class="hp" aria-hidden="true">
                <label>Company
                    <input name="company" tabindex="-1" autocomplete="off">
                </label>
            </div>
            <button class="send" type="submit">Send report</button>
        </form>

        <section class="bug-board">
            <h2>Reported bugs</h2>
            @forelse ($reports as $report)
                <details class="bug">
                    <summary>
                        @if ($report->status === 'fixed')
                            <span class="tick" aria-hidden="true">✓</span>
                        @else
                            <span class="spin" aria-hidden="true"></span>
                        @endif
                        <span class="bug-title">{{ $report->summary }}</span>
                        <span class="state">{{ $report->status === 'fixed' ? 'Fixed' : 'Pending' }}</span>
                    </summary>
                    <div class="bug-body">
                        <p class="meta">{{ $surfaces[$report->surface] ?? $report->surface }}@if ($report->version) · {{ $report->version }}@endif</p>
                        <p>{{ $report->details }}</p>
                    </div>
                </details>
            @empty
                <p>No open reports yet.</p>
            @endforelse
        </section>
    </article>
@endsection
