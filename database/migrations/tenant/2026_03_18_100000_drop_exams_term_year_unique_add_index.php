<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropExamsTermYearUniqueAddIndex extends Migration
{
    /**
     * Run the migrations.
     * Allow multiple exams per term/year (e.g. Bisha 1, Bisha 2, Final Exam in Term 1).
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropUnique(['term', 'year']);
            $table->index(['term', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex(['term', 'year']);
            $table->unique(['term', 'year']);
        });
    }
}
