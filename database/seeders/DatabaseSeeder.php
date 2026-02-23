<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central (landlord) database.
     * Only central/landlord seeders belong here.
     * Tenant-specific data is seeded via TenantDatabaseSeeder when a tenant is created.
     *
     * @return void
     */
    public function run()
    {
        // Central database seeders only (e.g. optional landlord data).
        // Tenant data is seeded by TenantDatabaseSeeder in tenant context.
    }
}
