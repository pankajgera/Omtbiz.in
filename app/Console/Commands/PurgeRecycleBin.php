<?php

namespace App\Console\Commands;

use App\Models\RecycleBinEntry;
use App\Services\RecycleBin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class PurgeRecycleBin extends Command
{
    protected $signature = 'recycle-bin:purge';

    protected $description = 'Permanently delete recycle-bin records older than ' . RecycleBinEntry::RETENTION_DAYS . ' days';

    public function handle(): int
    {
        $purged = 0;

        RecycleBinEntry::withoutGlobalScopes()->expired()->orderBy('id')->chunkById(100, function ($entries) use (&$purged) {
            foreach ($entries as $entry) {
                try {
                    RecycleBin::purge($entry);
                    $purged++;
                } catch (Throwable $e) {
                    // Leave the entry for the next run rather than stopping the whole purge.
                    Log::error('Recycle bin purge failed', ['entry' => $entry->id, 'error' => $e->getMessage()]);
                }
            }
        });

        $this->info("Purged {$purged} recycle-bin record(s).");

        return self::SUCCESS;
    }
}
