@extends('layout')

@section('content')
    <article class="sheet">
        <h1>About</h1>
        <div class="disclaimer">
            <p><strong>Board Arrows is published for educational purposes.</strong> Using an engine while you play another person is cheating. That can be illegal, and Chess.com and Lichess suspend accounts for it. You can lose the account.</p>
            <p>Turn on <strong>Pause on live games</strong> in the add-on popup before a game against a person. Leave the arrows for analysis boards, puzzles, and games against a computer.</p>
        </div>

        <h2>What it does</h2>
        <p>Board Arrows draws Stockfish on Chess.com and Lichess. The thick arrow is the move to play. The thinner solid arrows are the moves that follow it. Other colors are the next-best first moves. A panel on the page lists those lines and the evaluation.</p>
        <p>The engine is Stockfish 19 Lite. It runs in the browser. The position is not uploaded.</p>

        <h2>How a position is read</h2>
        <p>On Lichess the board is the usual piece markup. On Chess.com the current board is a canvas. The add-on reads the game's own flipped flag to decide whether Black is at the bottom. In a live game, a dark clock on the bottom player means the same thing. It does not use the evaluation bar, and it does not treat a light-colored username as the White pieces.</p>
        <p>Alt+A hides the arrows. The panel can be dragged by its title.</p>

        <h2>Options</h2>
        <p>The toolbar button opens the popup:</p>
        <ul>
            <li><strong>Show arrows</strong> turns the drawings on or off.</li>
            <li><strong>Show the line list</strong> shows or hides the panel.</li>
            <li><strong>Arrows only on the bottom side's turn</strong> waits until the side at the bottom is to move.</li>
            <li><strong>Number the next moves of every line</strong> adds the later arrows on the other candidate lines, not only the best one.</li>
            <li><strong>Show the 1, 2 circles on the arrows</strong> is off unless you check it. The continuation arrows stay solid either way.</li>
            <li><strong>Pause on live games</strong> clears the arrows on a game against a person. It stays off until you check it and the choice is saved.</li>
            <li><strong>Depth</strong> runs from 6 to 22.</li>
            <li><strong>Candidate arrows</strong> runs from 1 to 5.</li>
            <li><strong>Next moves in the line</strong> runs from 0 to 8.</li>
        </ul>

        <h2>The board on this site</h2>
        <p><a href="{{ route('play') }}">Test it now</a> is a separate board. You play Stockfish there at a rating you pick. The arrows on that board stay at full strength, so they match the add-on rather than the weakened opponent. Resign, new game, revert, clocks, and the evaluation bar are part of that page.</p>
    </article>
@endsection
