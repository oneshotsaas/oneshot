<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Downloads the latest disposable email domain list from the community-maintained
 * repository and rewrites the $disposable array in EmailDomains.php.
 *
 * Source: https://github.com/disposable-email-domains/disposable-email-domains
 */
class AuthUpdateDisposable extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'auth:update-disposable';
    protected $description = 'Fetch the latest disposable email domain list from GitHub and update EmailDomains.php';

    private string $sourceUrl  = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/main/disposable_email_blocklist.conf';
    private string $targetFile = __DIR__ . '/../../../Auth/Config/EmailDomains.php';

    public function run(array $params): void
    {
        CLI::write('Fetching disposable domain list…', 'yellow');

        $raw = $this->fetch($this->sourceUrl);

        if ($raw === null) {
            CLI::error('Failed to fetch the domain list. Check your internet connection.');
            return;
        }

        $domains = array_filter(
            array_map('trim', explode("\n", $raw)),
            fn($line) => $line !== '' && $line[0] !== '#'
        );

        if (count($domains) < 100) {
            CLI::error('Fetched list looks too small (' . count($domains) . ' entries). Aborting.');
            return;
        }

        CLI::write('Fetched ' . count($domains) . ' domains.', 'green');

        $this->rewrite($domains);

        CLI::write('EmailDomains::$disposable updated successfully.', 'green');
    }

    private function fetch(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout'        => 15,
                'user_agent'     => 'OneShot/auth-update-disposable',
                'follow_location'=> 1,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);

        return $result !== false ? $result : null;
    }

    private function rewrite(array $domains): void
    {
        $file = realpath($this->targetFile);

        if (! $file || ! is_writable($file)) {
            CLI::error('Cannot write to ' . $this->targetFile);
            return;
        }

        $content = file_get_contents($file);

        // Replace everything between $disposable = [ ... ];
        $entries = '';
        foreach ($domains as $domain) {
            $domain   = mb_strtolower(preg_replace('/[^a-z0-9.\-]/i', '', $domain));
            if ($domain === '') {
                continue;
            }
            $escaped  = addslashes($domain);
            $entries .= "        '{$escaped}' => 1,\n";
        }

        $content = preg_replace(
            '/public static array \$disposable = \[.*?\];/s',
            "public static array \$disposable = [\n{$entries}    ];",
            $content
        );

        file_put_contents($file, $content);
    }
}
