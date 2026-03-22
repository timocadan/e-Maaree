<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExamRecordsReplaceExamIdWithTerm extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('exam_records', 'term')) {
            Schema::table('exam_records', function (Blueprint $table) {
                $table->unsignedTinyInteger('term')->default(1)->after('section_id');
            });
            if (Schema::hasColumn('exam_records', 'exam_id') && Schema::hasTable('exams')) {
                DB::statement('UPDATE exam_records er INNER JOIN exams e ON er.exam_id = e.id SET er.term = e.term');
            }
        }
        if (Schema::hasColumn('exam_records', 'exam_id')) {
            Schema::table('exam_records', function (Blueprint $table) {
                $table->dropForeign(['exam_id']);
            });
            Schema::table('exam_records', function (Blueprint $table) {
                $table->dropColumn('exam_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('exams') && !Schema::hasColumn('exam_records', 'exam_id')) {
            Schema::table('exam_records', function (Blueprint $table) {
                $table->unsignedInteger('exam_id')->nullable()->after('section_id');
            });
            Schema::table('exam_records', function (Blueprint $table) {
                $table->dropColumn('term');
            });
        }
    }
}
