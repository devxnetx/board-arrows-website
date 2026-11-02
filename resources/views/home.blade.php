@extends('layout')

@section('content')
    <section class="hero">
        <div>
            <h1>Arrows for the move, and the moves after it.</h1>
            <p class="lede">Board Arrows draws Stockfish on Chess.com and Lichess. The thick arrow is the move to play. The thinner ones are what follows. The engine stays on your computer.</p>
            <div class="actions">
                <a class="cta chrome" href="{{ $chromeHref }}">Download for Chrome</a>
                <a class="cta firefox" href="{{ $firefoxHref }}">Download for Firefox</a>
                <a class="cta test" href="{{ route('play') }}">Test it now</a>
            </div>
        </div>
        <div class="poster" aria-hidden="true">
            @foreach (range(1, 64) as $cell)
                <i @class(['on' => in_array($cell, [12, 20, 28, 36], true)])></i>
            @endforeach
        </div>
    </section>

    <p class="note">Chess.com and Lichess ban engine help in games against people. Use the arrows on analysis, puzzles, and games against a computer, or turn on Pause on live games.</p>
@endsection
