<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class CreateTestTenant extends Command
{
    protected $signature = 'create:test-tenant';
    protected $description = 'Creates a test tenant school1';

    public function handle()
    {
        $this->info("Creating tenant 'school1'...");
        try {
            $tenant = Tenant::create(['id' => 'school1']);
            $this->info("Tenant created.");

            $this->info("Creating domain 'school1.localhost'...");
            $tenant->domains()->create(['domain' => 'school1.localhost']);
            $this->info("Domain created.");

            $this->info("Tenant 'school1' ready. Migrations and seeders should have run.");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
