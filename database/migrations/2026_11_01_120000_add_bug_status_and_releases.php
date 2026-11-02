<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->string('status')->default('pending');
        });

        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->date('released_on');
            $table->string('summary');
            $table->json('changes');
            $table->string('chrome_file');
            $table->string('firefox_file');
            $table->timestamps();
        });

        $seed = require base_path('config/releases.php');
        foreach ($seed as $release) {
            DB::table('releases')->insert([
                'version' => $release['version'],
                'released_on' => $release['date'],
                'summary' => $release['summary'],
                'changes' => json_encode($release['changes']),
                'chrome_file' => $release['chrome'],
                'firefox_file' => $release['firefox'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
