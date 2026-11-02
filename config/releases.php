<?php

return [
    [
        'version' => '1.2.6',
        'date' => '2026-11-01',
        'summary' => 'First build published on this page. Earlier copies lived only in the project folder.',
        'changes' => [
            'Draws Stockfish arrows on Chess.com and Lichess. The thick arrow is the move to play. Thinner arrows are the moves after it, drawn solid.',
            'The 1 and 2 circles stay off unless “Show the 1, 2 circles on the arrows” is saved in the popup.',
            'The add-on name and the panel on the chess page both say 1.2.6.',
            'Chess.com’s canvas board is turned using the site’s own flipped option. In a live game, a dark clock on the bottom player means Black is at the bottom. The evaluation bar is not used.',
            'Arrows stay on during live games unless “Pause on live games” is checked. Chess.com and Lichess ban engine help against people.',
            'The engine is Stockfish 19 Lite and it runs in the browser. The position is not uploaded.',
        ],
        'chrome' => 'board-arrows-1.2.6-chrome.zip',
        'firefox' => 'board-arrows-1.2.6-firefox.zip',
    ],
];
