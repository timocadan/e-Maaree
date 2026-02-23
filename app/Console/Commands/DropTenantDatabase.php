<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropTenantDatabase extends Command
{
    protected $signature = 'tenancy:drop-database {id : The tenant id (e.g. school1)} {--force : Skip confirmation}';
    protected $description = 'Drop a tenant database by id (e.g. when it exists but tenant record is missing).';

    public function handle(): int
    {
        $id = $this->argument('id');
        $prefix = config('tenancy.database.prefix', 'tenant');
        $suffix = config('tenancy.database.suffix', '');
        $dbName = $prefix . $id . $suffix;
        $connection = config('tenancy.database.central_connection', config('database.default'));

        if (!$this->option('force')) {
            $this->warn("This will DROP DATABASE `{$dbName}`.");
            if (!$this->confirm('Continue?', true)) {
                return self::SUCCESS;
            }
        }

        try {
            DB::connection($connection)->getPdo()->exec("DROP DATABASE IF EXISTS `" . str_replace('`', '``', $dbName) . "`");
            $this->info("Dropped database: {$dbName}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
