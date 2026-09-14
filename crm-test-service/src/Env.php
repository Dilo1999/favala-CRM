<?php

declare(strict_types=1);

/**
 * Minimal .env reader for this standalone service — no framework or
 * dependency, just enough to keep its own API key out of source control
 * the same way the main app's .env does (see crm-test-service/.gitignore).
 */
function crm_test_service_env(string $key): ?string
{
    static $values = null;

    if ($values === null) {
        $values = [];
        $path = __DIR__.'/../.env';

        if (file_exists($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $values[trim($k)] = trim($v);
            }
        }
    }

    return $values[$key] ?? null;
}
