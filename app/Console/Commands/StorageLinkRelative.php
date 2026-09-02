<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLinkRelative extends Command
{
    protected $signature = 'storage:link-relative';
    protected $description = 'Create storage symlink using relative path (for cPanel/shared hosting)';

    public function handle(): int
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (!is_dir($target)) {
            $this->error('Target directory does not exist: ' . $target);
            return 1;
        }

        if (file_exists($link)) {
            if (is_link($link)) {
                $this->info('Storage link already exists.');
                return 0;
            }
            $this->error('A file or directory already exists at: ' . $link);
            return 1;
        }

        // Use relative path - works better on cPanel/shared hosting
        $publicPath = realpath(public_path());
        $targetReal = realpath($target);
        if (!$publicPath || !$targetReal) {
            $this->error('Could not resolve paths. Falling back to absolute symlink.');
            return $this->runAbsolute($link, $target);
        }

        $relativeTarget = $this->getRelativePath($publicPath, $targetReal);
        if ($relativeTarget === null) {
            $this->warn('Could not compute relative path. Using absolute path.');
            $relativeTarget = $targetReal;
        }

        if (symlink($relativeTarget, $link)) {
            $this->info('Storage link created successfully (relative path).');
            return 0;
        }

        $this->warn('Relative symlink failed. Trying absolute path...');
        return $this->runAbsolute($link, $target);
    }

    private function runAbsolute(string $link, string $target): int
    {
        if (symlink($target, $link)) {
            $this->info('Storage link created successfully (absolute path).');
            return 0;
        }
        $this->error('Failed to create symlink. Check permissions and that symlinks are allowed.');
        return 1;
    }

    private function getRelativePath(string $from, string $to): ?string
    {
        $fromParts = explode(DIRECTORY_SEPARATOR, rtrim($from, DIRECTORY_SEPARATOR));
        $toParts = explode(DIRECTORY_SEPARATOR, rtrim($to, DIRECTORY_SEPARATOR));

        $commonLength = 0;
        $maxLength = min(count($fromParts), count($toParts));
        while ($commonLength < $maxLength && $fromParts[$commonLength] === $toParts[$commonLength]) {
            $commonLength++;
        }

        $upCount = count($fromParts) - $commonLength;
        $downParts = array_slice($toParts, $commonLength);
        $relative = str_repeat('..' . DIRECTORY_SEPARATOR, $upCount) . implode(DIRECTORY_SEPARATOR, $downParts);

        return $relative ?: null;
    }
}
