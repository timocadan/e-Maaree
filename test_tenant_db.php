<?php

$tenantId = 'school1';
$dbName = 'tenant_' . $tenantId;

echo "Testing connection to tenant database: $dbName\n";

try {
    // 1. Check if database exists using central connection
    $centralPdo = DB::connection('mysql')->getPdo();
    $stmt = $centralPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
    if ($stmt->fetch()) {
        echo "Database $dbName exists.\n";
    } else {
        echo "Database $dbName DOES NOT exist.\n";
        exit;
    }

    // 2. Try to initialize tenancy
    $tenant = App\Models\Tenant::find($tenantId);
    if (!$tenant) {
        echo "Tenant model not found for ID: $tenantId\n";
        exit;
    }

    tenancy()->initialize($tenant);
    echo "Tenancy initialized.\n";

    // 3. Test tenant connection
    $users = DB::table('users')->count();
    echo "Tenant Users Count: $users\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
