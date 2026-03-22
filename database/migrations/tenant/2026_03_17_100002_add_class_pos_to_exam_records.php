<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClassPosToExamRecords extends Migration
{
    public function up()
    {
        Schema::table('exam_records', function (Blueprint $table) {
            $table->unsignedInteger('class_pos')->nullable()->after('pos');
        });
    }

    public function down()
    {
        Schema::table('exam_records', function (Blueprint $table) {
            $table->dropColumn('class_pos');
        });
    }
}
