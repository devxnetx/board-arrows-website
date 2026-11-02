<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $fillable = [
        'version',
        'released_on',
        'summary',
        'changes',
        'chrome_file',
        'firefox_file',
    ];

    protected $casts = [
        'released_on' => 'date',
        'changes' => 'array',
    ];

    public function fileUrl(string $browser): ?string
    {
        $file = $browser === 'chrome' ? $this->chrome_file : $this->firefox_file;
        if (! is_string($file) || $file === '' || str_contains($file, '/') || str_contains($file, '\\')) {
            return null;
        }

        return is_file(public_path('releases/'.$file)) ? asset('releases/'.$file) : null;
    }
}
