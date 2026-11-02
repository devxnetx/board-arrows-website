@extends('layout')

@section('content')
    <article class="privacy">
        <h1>Privacy</h1>
        <p>Board Arrows does not make an account, and it does not send your games, your moves, or your name to this website or to anyone else.</p>
        <p>The extension keeps its settings in the browser's local storage on your computer. That is the depth, how many arrows to draw, and whether live games are paused.</p>
        <p>Stockfish runs inside the browser. On Chess.com and Lichess the extension reads the board so it can draw arrows. That position stays on the machine.</p>
        <p>The test board on this site does the same thing. The engine file is loaded from this server, then the game is played in the tab. The server does not receive the moves.</p>
        <p>This page does not use analytics cookies.</p>
        <p>A bug report is stored in the database on this server. The headline and the explanation are shown on the report page. The email is shown only in the admin panel. Reports are not sold and are not used for advertising.</p>
        <p>Donations, when the Revolut link or a crypto address is published, are paid to that account directly. This site does not see the payment and does not keep a donor account.</p>
    </article>
@endsection
