<?php

namespace Modules\Batch\Console\Commands;

use Illuminate\Console\Command;
use Modules\Batch\Models\Batch;

class SyncBatchStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batches:sync-statuses {--dry-run : Show counts without updating the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync batch statuses based on start_date (upcoming → running).';

    public function handle(): int
    {
        $today = now()->toDateString();

        $query = Batch::query()
            ->where('status', 'upcoming')
            ->whereDate('start_date', '<=', $today);

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("Would update {$count} batch(es) to running.");
            return self::SUCCESS;
        }

        $updated = $query->update(['status' => 'running']);

        $this->info("Updated {$updated} batch(es) to running.");

        return self::SUCCESS;
    }
}
