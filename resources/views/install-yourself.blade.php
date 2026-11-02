@extends('layout')

@section('content')
    <article class="sheet">
        <h1>Install it yourself</h1>
        <p>These steps load a file from the <a href="{{ route('changelog') }}">changelog</a>. A store listing, once it exists, updates on its own and does not use this page.</p>
        <p class="disclaimer">Using the arrows against another person is cheating. Chess.com and Lichess can suspend the account, and cheating in a competition can be illegal. Read the <a href="{{ route('about') }}">About</a> page before you install.</p>

        <h2>1. Download a build</h2>
        <p>Open the changelog and take the Chrome zip or the Firefox zip for the version you want. They are different files. Do not install the Chrome zip in Firefox, or the Firefox zip in Chrome.</p>

        <h2>2. Chrome or Edge</h2>
        <ol>
            <li>Unzip the Chrome file. <code>manifest.json</code> should sit next to the <code>src</code> folder, not inside another folder.</li>
            <li>Open <code>chrome://extensions</code>. In Edge that page is <code>edge://extensions</code>.</li>
            <li>Turn on Developer mode.</li>
            <li>Click Load unpacked and choose the unzipped folder.</li>
        </ol>
        <p>After a new version, unzip it again and click Reload on the Board Arrows card. Then refresh the chess tab.</p>

        <h2>3. Firefox</h2>
        <ol>
            <li>Open <code>about:debugging</code>.</li>
            <li>Choose This Firefox.</li>
            <li>Click Load Temporary Add-on.</li>
            <li>Select the Firefox zip, or <code>manifest.json</code> inside the unzipped folder.</li>
        </ol>
        <p>The name in that list should include the version, for example Board Arrows 1.2.6. Refresh the chess tab after the add-on loads.</p>
        <p>Release Firefox drops an unsigned add-on when the browser closes. Load it again after a restart. A signed add-on from addons.mozilla.org stays installed. This zip is not that signed file.</p>

        <h2>4. Check that it is the new copy</h2>
        <p>Reload the add-on until the list shows the version you downloaded, then refresh the chess tab. The panel on the board uses that same version number. <strong>Pause on live games</strong> stays unchecked until you turn it on.</p>
    </article>
@endsection
