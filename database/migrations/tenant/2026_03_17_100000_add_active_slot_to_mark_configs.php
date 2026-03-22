<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveSlotToMarkConfigs extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mark_configs') && !Schema::hasColumn('mark_configs', 'active_slot')) {
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->unsignedTinyInteger('active_slot')->default(0)->after('exam_max');
            });
        }
    }

    public function down()
    {
        Schema::table('mark_configs', function (Blueprint $table) {
            $table->dropColumn('active_slot');
        });
    }
}
