<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public function isSuspended(): bool
    {
        return $this->getAttribute('status') === self::STATUS_SUSPENDED;
    }

    public function isActive(): bool
    {
        return $this->getAttribute('status') !== self::STATUS_SUSPENDED;
    }
}
