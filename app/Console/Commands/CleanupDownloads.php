<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupDownloads extends Command
{
    protected $signature = 'cleanup:downloads';
    protected $description = 'Cleans up old WebTorrent download folders from /public/downloads';

    public function handle()
    {
        $basePath = public_path('downloads');
        $maxAgeSeconds = 18; // 30 minutes

        if (!file_exists($basePath)) {
            $this->info("No downloads folder found.");
            return;
        }

        $this->info("🧹 Starting cleanup of old stream folders...");

        foreach (glob($basePath . '/*', GLOB_ONLYDIR) as $folder) {
            $age = time() - filemtime($folder);

            // Skip if still fresh
            if ($age < $maxAgeSeconds) {
                continue;
            }

            $this->cleanupFolder($folder);
        }

        $this->info("✅ Cleanup completed!");
    }

    private function cleanupFolder($folder)
    {
        $escapedPath = str_replace('/', '\\', $folder);
        $this->info("Deleting: {$escapedPath}");

        // Step 1: Kill possible WebTorrent processes
        exec('tasklist', $processes);
        foreach ($processes as $line) {
            if (stripos($line, 'webtorrent-runner.exe') !== false) {
                exec('taskkill /F /IM webtorrent-runner.exe');
                Log::info("💀 Killed webtorrent-runner.exe before deleting: {$escapedPath}");
            }
        }

        // Step 2: Delete folder via PowerShell forcefully
        $deleteCmd = "powershell -NoProfile -Command \"Remove-Item -LiteralPath '{$escapedPath}' -Recurse -Force -ErrorAction SilentlyContinue\"";
        exec($deleteCmd);

        if (!file_exists($folder)) {
            $this->info("✅ Deleted: {$escapedPath}");
            Log::info("✅ Deleted old stream folder: {$escapedPath}");
        } else {
            $this->warn("⚠️ Failed to delete: {$escapedPath}");
            Log::warning("⚠️ Could not delete folder: {$escapedPath}");
        }
    }
}
