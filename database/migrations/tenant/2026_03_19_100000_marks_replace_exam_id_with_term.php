<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MarksReplaceExamIdWithTerm extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('marks', 'term')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->unsignedTinyInteger('term')->default(1)->after('section_id');
            });
            if (Schema::hasColumn('marks', 'exam_id') && Schema::hasTable('exams')) {
                DB::statement('UPDATE marks m INNER JOIN exams e ON m.exam_id = e.id SET m.term = e.term');
            }
        }
        if (Schema::hasColumn('marks', 'exam_id')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->dropForeign(['exam_id']);
            });
            Schema::table('marks', function (Blueprint $table) {
                $table->dropColumn('exam_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('exams') && !Schema::hasColumn('marks', 'exam_id')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->unsignedInteger('exam_id')->nullable()->after('section_id');
            });
            // Cannot reliably backfill exam_id from term without mapping
            Schema::table('marks', function (Blueprint $table) {
                $table->dropColumn('term');
            });
        }
    }
}
