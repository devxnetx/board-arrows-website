<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\Release;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'chromeHref' => config('boardarrows.chrome_url') ?: route('install', 'chrome'),
            'firefoxHref' => config('boardarrows.firefox_url') ?: route('install', 'firefox'),
        ]);
    }

    public function play(): View
    {
        return view('play', [
            'title' => 'Play · Board Arrows',
        ]);
    }

    public function install(string $browser): View|RedirectResponse
    {
        abort_unless(in_array($browser, ['chrome', 'firefox'], true), 404);

        $url = config('boardarrows.'.$browser.'_url');
        if (is_string($url) && $url !== '') {
            return redirect()->away($url);
        }

        return view('install', ['browser' => $browser]);
    }

    public function privacy(): View
    {
        return view('privacy');
    }

    public function changelog(): View
    {
        return view('changelog', [
            'title' => 'Changelog · Board Arrows',
            'releases' => Release::query()->orderByDesc('released_on')->orderByDesc('id')->get(),
        ]);
    }

    public function bugs(): View
    {
        return view('bugs', [
            'title' => 'Report a bug · Board Arrows',
            'surfaces' => BugReport::surfaces(),
            'reports' => BugReport::query()
                ->whereIn('status', ['pending', 'fixed'])
                ->orderByRaw("case status when 'pending' then 0 else 1 end")
                ->latest()
                ->get(),
        ]);
    }

    public function about(): View
    {
        return view('about', [
            'title' => 'About · Board Arrows',
        ]);
    }

    public function installYourself(): View
    {
        return view('install-yourself', [
            'title' => 'Install it yourself · Board Arrows',
        ]);
    }

    public function storeBug(Request $request): RedirectResponse
    {
        if ($request->filled('company')) {
            return redirect()->route('bugs')->with('reported', true);
        }

        $data = $request->validate([
            'summary' => ['required', 'string', 'max:160'],
            'details' => ['required', 'string', 'max:5000'],
            'surface' => ['required', 'in:chess.com,lichess,play,chrome,firefox,other'],
            'version' => ['nullable', 'string', 'max:40'],
            'contact' => ['nullable', 'email', 'max:160'],
        ]);

        BugReport::query()->create($data);

        return redirect()->route('bugs')->with('reported', true);
    }

    public function donate(): View
    {
        return view('donate', [
            'title' => 'Donate · Board Arrows',
            'revolutUrl' => config('boardarrows.revolut_url'),
            'coins' => config('boardarrows.coins'),
        ]);
    }
}
