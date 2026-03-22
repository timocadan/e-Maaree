<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConductToExamRecordsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('exam_records') && ! Schema::hasColumn('exam_records', 'conduct')) {
            Schema::table('exam_records', function (Blueprint $table) {
                $table->string('conduct', 1)->nullable()->after('ps');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('exam_records') && Schema::hasColumn('exam_records', 'conduct')) {
            Schema::table('exam_records', function (Blueprint $table) {
                $table->dropColumn('conduct');
            });
        }
    }
}
