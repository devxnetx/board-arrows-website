<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugReport extends Model
{
    protected $fillable = [
        'summary',
        'details',
        'surface',
        'version',
        'contact',
        'status',
    ];

    public static function surfaces(): array
    {
        return [
            'chess.com' => 'Chess.com',
            'lichess' => 'Lichess',
            'play' => 'The play page',
            'chrome' => 'Chrome or Edge',
            'firefox' => 'Firefox',
            'other' => 'Somewhere else',
        ];
    }
}
