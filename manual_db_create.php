<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\DatabaseManager;

$tenantId = 'school1';

try {
    $tenant = Tenant::find($tenantId);
    if (!$tenant) {
        echo "Creating tenant model...\n";
        $tenant = Tenant::create(['id' => $tenantId]);
        $tenant->domains()->create(['domain' => 'school1.localhost']);
    }

    // Ensure credentials/db name are set
    if (!$tenant->tenancy_db_name) {
        echo "Generating credentials...\n";
        $tenant->database()->makeCredentials(); // Sets tenancy_db_name, etc.
        $tenant->save();
    }

    $dbName = $tenant->tenancy_db_name;
    echo "Target Database Name: '$dbName'\n";

    // Drop if exists
    echo "Dropping database '$dbName' if exists...\n";
    DB::statement("DROP DATABASE IF EXISTS `$dbName`");

    // Create
    echo "Creating database...\n";
    // We skip ensureTenantCanBeCreated because we just dropped it.
    // Also, we call createDatabase on the manager specific to the tenant connection
    $tenant->database()->manager()->createDatabase($tenant);
    echo "Database '$dbName' created.\n";

    echo "Migrating...\n";
    Artisan::call('tenants:migrate', [
        '--tenants' => [$tenantId],
    ]);
    echo Artisan::output();

    echo "Seeding...\n";
    Artisan::call('tenants:seed', [
        '--tenants' => [$tenantId],
        '--class' => 'DatabaseSeeder',
    ]);
    echo Artisan::output();

    echo "Manual setup complete.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
