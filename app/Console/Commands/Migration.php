<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class Migration extends Command
{
    protected $signature = 'start:migration {--step}';

    protected $description = 'Start Migration';

    public function handle(): int
    {
        $failedMarker = config('constants.migration.failed_marker');

        if (config('constants.migration.is_migration_enabled')) {
            $this->info('Migration is enabled on this server.');
            try {
                $this->call('migrate', ['--force' => true, '--step' => $this->option('step')]);
            } catch (Throwable $e) {
                // The driver's message has no SQL or bindings, unlike the QueryException that wraps it.
                File::put($failedMarker, Str::before($e->getPrevious()?->getMessage() ?? $e->getMessage(), "\n"));

                throw $e;
            }
        } else {
            $this->info('Migration is disabled on this server.');
        }
        File::delete($failedMarker);

        return self::SUCCESS;
    }
}
