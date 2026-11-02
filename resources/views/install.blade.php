@extends('layout')

@section('content')
    <article class="install">
        @if ($browser === 'chrome')
            <h1>Download for Chrome</h1>
            <p>The Chrome Web Store link is not set yet. When it is, this address will send people straight there. Put the listing URL in <code>CHROME_STORE_URL</code> in the website <code>.env</code>.</p>
            <p>Until then, Chrome can load the extension from the folder on this machine:</p>
            <ol>
                <li>Copy <code>manifest.chrome.json</code> over <code>manifest.json</code> in <code>D:\chess\chess-arrows</code>.</li>
                <li>Open <code>chrome://extensions</code>.</li>
                <li>Turn on Developer mode.</li>
                <li>Click Load unpacked and choose that folder.</li>
            </ol>
            <p>Packaging the zip for the store is written in <code>D:\chess\chess-arrows\chrome.md</code>. Edge uses that same zip.</p>
        @else
            <h1>Download for Firefox</h1>
            <p>The addons.mozilla.org link is not set yet. When it is, this address will send people straight there. Put the listing URL in <code>FIREFOX_STORE_URL</code> in the website <code>.env</code>.</p>
            <p>Release Firefox will not keep an unsigned add-on. Until Mozilla signs it, a temporary load lasts until Firefox closes:</p>
            <ol>
                <li>Open <code>about:debugging</code>.</li>
                <li>Choose This Firefox.</li>
                <li>Click Load Temporary Add-on.</li>
                <li>Select <code>manifest.json</code> in <code>D:\chess\chess-arrows</code>.</li>
            </ol>
            <p>The name in that list should say Board Arrows 1.2.6. Publishing and signing are written in <code>D:\chess\chess-arrows\firefox.md</code>.</p>
        @endif
        <p><a href="{{ route('home') }}">Back to the board</a></p>
    </article>
@endsection
