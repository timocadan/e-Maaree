<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'tenancy:create 
                            {name : Display name of the school/tenant} 
                            {domain : Subdomain or domain (e.g. school1.localhost or school1.mysystem.com)} 
                            {--id= : Optional tenant id (default: UUID)}';
    protected $description = 'Create a new tenant (school) with database, run migrations and seed.';

    public function handle(): int
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $id = $this->option('id');

        $payload = ['name' => $name];
        if ($id !== null) {
            $payload['id'] = $id;
        }

        $this->info("Creating tenant: {$name} ({$domain})");

        try {
            $tenant = Tenant::create($payload);
            $tenant->createDomain($domain);
            $this->info('Tenant created. Database created, migrations and seed have been run.');
            $this->line('Tenant id: ' . $tenant->getTenantKey());
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
