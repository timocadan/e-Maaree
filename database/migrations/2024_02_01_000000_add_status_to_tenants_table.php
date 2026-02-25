<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'status')) {
                $table->string('status', 20)->default('active')->after('id');
            }
        });
        // Backfill from is_active if column existed
        if (Schema::hasColumn('tenants', 'is_active')) {
            \DB::table('tenants')->where('is_active', false)->update(['status' => 'suspended']);
            \DB::table('tenants')->where('is_active', true)->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
