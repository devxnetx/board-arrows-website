@extends('layout')

@section('bodyClass', 'app')

@section('content')
    <div class="game">
        <div class="stage">
            <div class="seat" id="seat-top">
                <div class="avatar engine" aria-hidden="true">S</div>
                <div class="who">
                    <strong id="name-top">Stockfish</strong>
                    <span class="sub" id="sub-top">1500</span>
                </div>
                <time class="clock" id="clock-top">10:00</time>
            </div>
            <div class="board-row">
                <div class="eval" id="eval" aria-label="Evaluation">
                    <div class="eval-rest"></div>
                    <div class="eval-white" id="eval-white"></div>
                    <span class="eval-score" id="eval-score">0.0</span>
                </div>
                <div class="board-wrap">
                    <div id="board" class="board" role="grid" aria-label="Chess board"></div>
                    <svg id="arrows" class="arrows" viewBox="0 0 100 100" aria-hidden="true"></svg>
                    <div id="promo" class="promo" hidden></div>
                    <div id="result" class="result" hidden>
                        <strong id="result-title"></strong>
                        <p id="result-detail"></p>
                        <button id="result-new" type="button">New game</button>
                    </div>
                </div>
            </div>
            <div class="seat" id="seat-bottom">
                <div class="avatar you" aria-hidden="true">Y</div>
                <div class="who">
                    <strong id="name-bottom">You</strong>
                    <span class="sub" id="sub-bottom">White</span>
                </div>
                <time class="clock" id="clock-bottom">10:00</time>
            </div>
        </div>
        <aside class="side">
            <div class="moves-pane">
                <div class="moves-head">Moves</div>
                <div class="moves" id="moves"></div>
            </div>
            <div class="controls">
                <p id="status">Loading Stockfish…</p>
                <div class="row">
                    <button id="revert" type="button" disabled>
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9l5-5"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 9h10.5a5.5 5.5 0 1 1 0 11H11"/></svg>
                        Revert
                    </button>
                    <button id="resign" type="button" disabled>
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 3h2v18H6z"/><path fill="currentColor" d="M8 4h11l-2.2 4L19 12H8z"/></svg>
                        Resign
                    </button>
                    <button id="new-game" type="button">
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        New game
                    </button>
                </div>
                <div class="row">
                    <button class="choice on" id="as-white" type="button">
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="#f4f4f4" stroke="#222" stroke-width="1.5"/></svg>
                        Play as White
                    </button>
                    <button class="choice" id="as-black" type="button">
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="#1a1a1a" stroke="#f4f4f4" stroke-width="1.5"/></svg>
                        Play as Black
                    </button>
                </div>
                <label class="field" for="elo">Stockfish strength
                    <select id="elo">
                        <option value="1320">1320 · Beginner</option>
                        <option value="1500" selected>1500 · Casual</option>
                        <option value="1700">1700 · Intermediate</option>
                        <option value="1900">1900 · Club</option>
                        <option value="2100">2100 · Expert</option>
                        <option value="2300">2300 · Master</option>
                        <option value="2500">2500 · International</option>
                        <option value="2700">2700 · Grandmaster</option>
                        <option value="2900">2900 · Super GM</option>
                        <option value="3190">3190 · Maximum</option>
                        <option value="full">Best · full strength</option>
                    </select>
                </label>
                <p class="hint">The opponent plays at the rating you pick. Arrows on the board stay at full strength. Revert takes back your last move.</p>
            </div>
        </aside>
    </div>
@endsection

@push('head')
    <link rel="stylesheet" href="{{ asset('css/board.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/chess/chess-global.js') }}"></script>
    <script src="{{ asset('js/addon/uci.js') }}"></script>
    <script src="{{ asset('js/addon/arrows.js') }}"></script>
    <script src="{{ asset('js/board.js') }}"></script>
@endpush
