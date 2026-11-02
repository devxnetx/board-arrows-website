<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\Release;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use ZipArchive;

class AdminController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (session('admin') === true) {
            return redirect()->route('admin.home');
        }

        return view('admin.login', [
            'title' => 'Admin · Board Arrows',
            'configured' => config('boardarrows.admin_password') !== '',
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $expected = (string) config('boardarrows.admin_password');
        $given = (string) $request->input('password', '');
        if ($expected === '' || ! hash_equals($expected, $given)) {
            return back()->withErrors(['password' => 'That password is not right.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin', true);

        return redirect()->route('admin.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin');
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }

    public function home(): View
    {
        return view('admin.home', [
            'title' => 'Admin · Board Arrows',
            'reports' => BugReport::query()->latest()->limit(200)->get(),
            'releases' => Release::query()->orderByDesc('released_on')->orderByDesc('id')->get(),
        ]);
    }

    public function updateBug(Request $request, BugReport $report): RedirectResponse
    {
        $data = $request->validate([
            'summary' => ['required', 'string', 'max:160'],
            'details' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:pending,fixed,rejected'],
        ]);

        $report->update($data);

        return redirect()->route('admin.home')->with('saved', 'Bug updated.');
    }

    public function storeRelease(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'regex:/^[0-9A-Za-z][0-9A-Za-z._-]{0,30}$/'],
            'released_on' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:300'],
            'changes' => ['required', 'string', 'max:8000'],
            'chrome' => ['required', 'file', 'max:12288'],
            'firefox' => ['required', 'file', 'max:12288'],
        ]);

        $changes = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $data['changes']) ?: [])));
        if ($changes === []) {
            return back()->withInput()->withErrors(['changes' => 'Write at least one change, on its own line.']);
        }

        $chromeError = $this->zipError($request->file('chrome'), 'chrome');
        $firefoxError = $this->zipError($request->file('firefox'), 'firefox');
        if ($chromeError || $firefoxError) {
            return back()->withInput()->withErrors(array_filter([
                'chrome' => $chromeError,
                'firefox' => $firefoxError,
            ]));
        }

        $version = $data['version'];
        $chromeName = 'board-arrows-'.$version.'-chrome.zip';
        $firefoxName = 'board-arrows-'.$version.'-firefox.zip';
        $this->placeZip($request->file('chrome'), $chromeName);
        $this->placeZip($request->file('firefox'), $firefoxName);

        Release::query()->updateOrCreate(
            ['version' => $version],
            [
                'released_on' => $data['released_on'],
                'summary' => $data['summary'],
                'changes' => $changes,
                'chrome_file' => $chromeName,
                'firefox_file' => $firefoxName,
            ]
        );

        return redirect()->route('admin.home')->with('saved', 'Version '.$version.' is on the changelog.');
    }

    private function zipError(?UploadedFile $file, string $browser): ?string
    {
        if (! $file) {
            return 'Choose a zip file.';
        }
        if (! str_ends_with(strtolower($file->getClientOriginalName()), '.zip')) {
            return 'Upload a .zip file.';
        }

        $zip = new ZipArchive();
        if ($zip->open($file->getPathname()) !== true) {
            return 'That file could not be opened as a zip.';
        }

        $manifest = $zip->getFromName('manifest.json');
        $unsafe = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (str_contains($name, '..') || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                $unsafe = true;
                break;
            }
        }
        $zip->close();

        if ($unsafe) {
            return 'The zip contains an unsafe path.';
        }
        if (! is_string($manifest) || $manifest === '') {
            return 'manifest.json has to sit at the top of the zip, not inside a folder.';
        }
        if ($browser === 'chrome' && ! str_contains($manifest, 'chrome-background.js')) {
            return 'This is not the Chrome package. It needs the Chrome manifest, with the service worker.';
        }
        if ($browser === 'firefox' && ! str_contains($manifest, 'browser_specific_settings')) {
            return 'This is not the Firefox package. It needs the Firefox manifest.';
        }

        return null;
    }

    private function placeZip(UploadedFile $file, string $name): void
    {
        $directory = public_path('releases');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $dest = $directory.DIRECTORY_SEPARATOR.$name;
        if (is_file($dest)) {
            unlink($dest);
        }
        $file->move($directory, $name);
    }
}
