<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveTenantRecord extends Command
{
    protected $signature = 'tenancy:remove {id : The tenant id (e.g. school1)}';
    protected $description = 'Remove tenant record (and domains) from central DB only. Use when tenant DB is already gone and you want to run tenancy:create again.';

    public function handle(): int
    {
        $id = $this->argument('id');

        $deleted = DB::table('tenants')->where('id', $id)->delete();
        if ($deleted === 0) {
            $this->warn("No tenant found with id '{$id}'.");
            return self::FAILURE;
        }

        $this->info("Removed tenant '{$id}' and its domains from central database.");
        return self::SUCCESS;
    }
}
