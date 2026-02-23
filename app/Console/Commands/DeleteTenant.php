<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class DeleteTenant extends Command
{
    protected $signature = 'tenancy:delete {id : The tenant id (e.g. school1)}';
    protected $description = 'Delete a tenant and drop its database.';

    public function handle(): int
    {
        $id = $this->argument('id');
        $tenant = Tenant::find($id);

        if ($tenant === null) {
            $this->warn("Tenant with id '{$id}' not found in central database.");
            return self::FAILURE;
        }

        $this->info("Deleting tenant: {$id} (and dropping its database).");
        try {
            $tenant->delete();
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), "database doesn't exist") || str_contains($e->getMessage(), '1008')) {
                $this->info('Tenant removed from central database (tenant database was already missing).');
                return self::SUCCESS;
            }
            throw $e;
        }
        $this->info('Tenant deleted.');
        return self::SUCCESS;
    }
}
