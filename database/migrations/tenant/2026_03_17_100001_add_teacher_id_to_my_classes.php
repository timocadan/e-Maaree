<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeacherIdToMyClasses extends Migration
{
    public function up()
    {
        Schema::table('my_classes', function (Blueprint $table) {
            $table->unsignedInteger('teacher_id')->nullable()->after('class_type_id');
        });
        if (Schema::hasTable('my_classes')) {
            Schema::table('my_classes', function (Blueprint $table) {
                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('my_classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
}
