<?php

namespace Tests\Feature;

use App\Models\BugReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Download for Chrome');
        $response->assertSee('Download for Firefox');
        $response->assertSee('Test it now');
        $response->assertSee('/play');
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('Switch to the light theme');
    }

    public function test_the_play_page_is_the_board(): void
    {
        $response = $this->get('/play');

        $response->assertOk();
        $response->assertSee('Resign');
        $response->assertSee('New game');
        $response->assertSee('Revert');
        $response->assertSee('Stockfish strength');
        $response->assertSee('1320');
        $response->assertSee('full strength');
        $response->assertSee('Board Arrows');
        $response->assertSee('id="board"', false);
        $response->assertSee('id="eval"', false);
        $response->assertSee('id="moves"', false);
        $response->assertDontSee('id="lines"', false);
        $response->assertDontSee('id="ba-score"', false);
        $response->assertDontSee('aria-label="Board Arrows"', false);
    }

    public function test_install_pages_explain_the_missing_store_links(): void
    {
        $this->get('/download/chrome')->assertOk()->assertSee('CHROME_STORE_URL');
        $this->get('/download/firefox')->assertOk()->assertSee('FIREFOX_STORE_URL');
        $this->get('/privacy')->assertOk()->assertSee('does not make an account');
    }

    public function test_a_store_url_sends_the_download_button_there(): void
    {
        config(['boardarrows.chrome_url' => 'https://example.com/chrome']);

        $this->get('/')->assertSee('https://example.com/chrome');
        $this->get('/download/chrome')->assertRedirect('https://example.com/chrome');
    }

    public function test_the_changelog_links_the_published_zips(): void
    {
        $this->get('/changelog')
            ->assertOk()
            ->assertSee('Board Arrows 1.2.6')
            ->assertSee('board-arrows-1.2.6-chrome.zip')
            ->assertSee('board-arrows-1.2.6-firefox.zip')
            ->assertSee('about:debugging');
    }

    public function test_a_bug_report_is_saved_and_listed_for_the_owner(): void
    {
        $this->post('/bugs', [
            'summary' => 'Arrows vanish',
            'details' => 'They disappear after move 10 on Chess.com.',
            'surface' => 'chess.com',
            'version' => '1.2.6',
            'contact' => 'player@example.com',
        ])->assertRedirect('/bugs');

        $this->assertDatabaseHas('bug_reports', [
            'summary' => 'Arrows vanish',
            'surface' => 'chess.com',
        ]);

        $this->get('/bugs')
            ->assertSee('Saved. Thank you.')
            ->assertSee('Arrows vanish')
            ->assertSee('Pending')
            ->assertSee('class="spin"', false)
            ->assertDontSee('player@example.com');

        $this->get('/admin')->assertRedirect('/admin/login');

        config(['boardarrows.admin_password' => 'test-admin-pass']);
        $this->post('/admin/login', ['password' => 'test-admin-pass'])->assertRedirect('/admin');

        $report = BugReport::query()->first();
        $this->patch('/admin/bugs/'.$report->id, [
            'summary' => 'Arrows vanish',
            'details' => 'They disappear after move 10 on Chess.com.',
            'status' => 'fixed',
        ])->assertRedirect('/admin');

        $this->get('/bugs')
            ->assertSee('Fixed')
            ->assertSee('class="tick"', false)
            ->assertDontSee('player@example.com');

        $this->patch('/admin/bugs/'.$report->id, [
            'summary' => 'Arrows vanish',
            'details' => 'They disappear after move 10 on Chess.com.',
            'status' => 'rejected',
        ]);
        $this->get('/bugs')->assertDontSee('Arrows vanish');
    }

    public function test_an_admin_can_publish_a_changelog_build(): void
    {
        config(['boardarrows.admin_password' => 'test-admin-pass']);
        $this->post('/admin/login', ['password' => 'nope'])->assertSessionHasErrors('password');
        $this->post('/admin/login', ['password' => 'test-admin-pass']);

        $chrome = $this->addonZip('{"background":{"service_worker":"src/chrome-background.js"}}');
        $firefox = $this->addonZip('{"browser_specific_settings":{"gecko":{"id":"test@local"}}}');

        $this->post('/admin/releases', [
            'version' => '9.9.9',
            'released_on' => '2026-11-02',
            'summary' => 'A test build',
            'changes' => "Fixed the panel.\nShorter arrows.",
            'chrome' => $chrome,
            'firefox' => $firefox,
        ])->assertRedirect('/admin');

        $this->get('/changelog')
            ->assertSee('Board Arrows 9.9.9')
            ->assertSee('Fixed the panel.')
            ->assertSee('board-arrows-9.9.9-chrome.zip');

        @unlink(public_path('releases/board-arrows-9.9.9-chrome.zip'));
        @unlink(public_path('releases/board-arrows-9.9.9-firefox.zip'));
    }

    public function test_about_and_install_pages_explain_the_add_on(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('educational purposes')
            ->assertSee('Pause on live games')
            ->assertSee('illegal');

        $this->get('/install-yourself')
            ->assertOk()
            ->assertSee('Load unpacked')
            ->assertSee('about:debugging')
            ->assertSee('cheating');
    }

    private function addonZip(string $manifest): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ba');
        $zipPath = $path.'.zip';
        rename($path, $zipPath);
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('manifest.json', $manifest);
        $zip->close();

        return new UploadedFile($zipPath, 'addon.zip', 'application/zip', null, true);
    }

    public function test_an_empty_bug_report_is_rejected(): void
    {
        $this->post('/bugs', ['surface' => 'play'])
            ->assertSessionHasErrors(['summary', 'details']);
        $this->assertDatabaseCount('bug_reports', 0);
    }

    public function test_a_filled_company_field_is_not_stored(): void
    {
        $this->post('/bugs', [
            'summary' => 'Spam',
            'details' => 'Buy something.',
            'surface' => 'other',
            'company' => 'spammer',
        ])->assertRedirect('/bugs');

        $this->assertSame(0, BugReport::query()->count());
    }

    public function test_the_donate_page_shows_example_coins_until_real_ones_are_set(): void
    {
        $this->get('/donate')
            ->assertOk()
            ->assertSee('The Revolut link is not published yet.')
            ->assertSee('bc1qexampledonotsend000000000000000000000')
            ->assertSee('ExampleSoLanaDoNotSend11111111111111111111')
            ->assertSee('Lines marked Example are samples. Do not send coins to them.')
            ->assertDontSee('Crypto addresses are not published yet.');
    }

    public function test_the_donate_page_shows_a_revolut_link_and_a_coin_address(): void
    {
        config([
            'boardarrows.revolut_url' => 'https://revolut.me/boardarrows',
            'boardarrows.coins' => [
                ['name' => 'Bitcoin', 'symbol' => 'BTC', 'address' => 'bc1qexampleaddress'],
            ],
        ]);

        $this->get('/donate')
            ->assertOk()
            ->assertSee('https://revolut.me/boardarrows')
            ->assertSee('bc1qexampleaddress')
            ->assertDontSee('not published yet');
    }
}
