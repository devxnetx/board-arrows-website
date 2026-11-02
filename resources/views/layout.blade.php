<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            try {
                if (localStorage.getItem("ba-theme") === "light") {
                    document.documentElement.setAttribute("data-theme", "light");
                }
            } catch (err) {}
        })();
    </script>
    <title>{{ $title ?? 'Board Arrows' }}</title>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @stack('head')
</head>
<body class="@yield('bodyClass')">
    <header class="wrap top">
        <a class="mark" href="{{ route('home') }}">Board Arrows</a>
        <div class="top-rest">
            <nav class="nav">
                <a href="{{ route('play') }}">Test it now</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('install-yourself') }}">Install it yourself</a>
                <a href="{{ route('changelog') }}">Changelog</a>
                <a href="{{ route('bugs') }}">Report a bug</a>
                <a href="{{ route('donate') }}">Donate</a>
                <a href="{{ route('privacy') }}">Privacy</a>
            </nav>
            <button class="theme" id="theme-toggle" type="button" aria-pressed="false" aria-label="Switch to the light theme">Light</button>
        </div>
    </header>
    <main class="wrap">
        @yield('content')
    </main>
    <footer class="wrap foot">
        <nav class="nav">
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('install-yourself') }}">Install it yourself</a>
            <a href="{{ route('changelog') }}">Changelog</a>
            <a href="{{ route('bugs') }}">Report a bug</a>
            <a href="{{ route('donate') }}">Donate</a>
            <a href="{{ route('privacy') }}">Privacy</a>
        </nav>
        <p>Stockfish is GPL-3.0. The copy on this site is Stockfish.js 19 lite.
        chess.js is BSD. The arrows extension does not send your games anywhere.</p>
    </footer>
    <script>
        (function () {
            var button = document.getElementById("theme-toggle");
            if (!button) return;
            var root = document.documentElement;
            function paint() {
                var light = root.getAttribute("data-theme") === "light";
                button.textContent = light ? "Dark" : "Light";
                button.setAttribute("aria-pressed", light ? "true" : "false");
                button.setAttribute("aria-label", light ? "Switch to the dark theme" : "Switch to the light theme");
            }
            paint();
            button.addEventListener("click", function () {
                var next = root.getAttribute("data-theme") === "light" ? "dark" : "light";
                if (next === "light") root.setAttribute("data-theme", "light");
                else root.removeAttribute("data-theme");
                try { localStorage.setItem("ba-theme", next); } catch (err) {}
                paint();
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
