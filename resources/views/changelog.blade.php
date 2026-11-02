@extends('layout')

@section('content')
    <article class="sheet">
        <h1>Changelog</h1>
        <p>Each published build of the add-on, newest first, with the files for that version.</p>

        @forelse ($releases as $release)
            <section class="release">
                <header class="release-head">
                    <h2>Board Arrows {{ $release->version }}</h2>
                    <time datetime="{{ $release->released_on->toDateString() }}">{{ $release->released_on->toDateString() }}</time>
                </header>
                <p>{{ $release->summary }}</p>
                <ul>
                    @foreach ($release->changes as $change)
                        <li>{{ $change }}</li>
                    @endforeach
                </ul>
                <div class="file-row">
                    @if ($release->fileUrl('chrome'))
                        <a class="cta chrome" href="{{ $release->fileUrl('chrome') }}">Download {{ $release->version }} for Chrome</a>
                    @else
                        <span class="missing">Chrome file for {{ $release->version }} is not on this server.</span>
                    @endif
                    @if ($release->fileUrl('firefox'))
                        <a class="cta firefox" href="{{ $release->fileUrl('firefox') }}">Download {{ $release->version }} for Firefox</a>
                    @else
                        <span class="missing">Firefox file for {{ $release->version }} is not on this server.</span>
                    @endif
                </div>
            </section>
        @empty
            <p>No builds have been published yet.</p>
        @endforelse

        <p class="fine">Chrome and Edge: unzip the file, open <code>chrome://extensions</code>, turn on Developer mode, and choose Load unpacked. Firefox: open <code>about:debugging</code>, choose This Firefox, and load the zip as a temporary add-on. Release Firefox drops an unsigned add-on when the browser closes. A store install updates on its own and does not use these files.</p>
    </article>
@endsection
