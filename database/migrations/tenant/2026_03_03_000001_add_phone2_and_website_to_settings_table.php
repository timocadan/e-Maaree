<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPhone2AndWebsiteToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure secondary phone and website keys exist in the settings table
        $existing = DB::table('settings')
            ->whereIn('type', ['phone2', 'website'])
            ->pluck('type')
            ->all();

        $toInsert = [];

        if (!in_array('phone2', $existing, true)) {
            $toInsert[] = ['type' => 'phone2', 'description' => null, 'created_at' => now(), 'updated_at' => now()];
        }

        if (!in_array('website', $existing, true)) {
            $toInsert[] = ['type' => 'website', 'description' => null, 'created_at' => now(), 'updated_at' => now()];
        }

        if (!empty($toInsert)) {
            DB::table('settings')->insert($toInsert);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->whereIn('type', ['phone2', 'website'])->delete();
    }
}

