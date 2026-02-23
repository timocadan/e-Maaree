<?php

$host = 'school1.localhost';
echo "Testing resolution for host: $host\n";

try {
    $resolver = app(Stancl\Tenancy\Resolvers\DomainTenantResolver::class);
    $tenant = $resolver->resolve($host);
    echo "Resolved Tenant ID: " . $tenant->id . "\n";
} catch (\Exception $e) {
    echo "Error resolving tenant: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
