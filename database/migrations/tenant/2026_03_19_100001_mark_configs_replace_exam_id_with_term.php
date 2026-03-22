<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MarkConfigsReplaceExamIdWithTerm extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('mark_configs', 'term')) {
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->unsignedTinyInteger('term')->default(1)->after('subject_id');
            });
            if (Schema::hasTable('exams') && Schema::hasColumn('mark_configs', 'exam_id')) {
                DB::statement('UPDATE mark_configs mc INNER JOIN exams e ON mc.exam_id = e.id SET mc.term = e.term');
            }
        }
        if (Schema::hasColumn('mark_configs', 'exam_id')) {
            try {
                Schema::table('mark_configs', function (Blueprint $table) {
                    $table->dropForeign(['exam_id']);
                });
            } catch (\Throwable $e) {
                // FK might already be dropped
            }
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->dropUnique(['exam_id', 'my_class_id', 'subject_id']);
                $table->dropColumn('exam_id');
            });
            // Deduplicate: keep one row per (term, my_class_id, subject_id), delete the rest
            DB::statement('DELETE t1 FROM mark_configs t1 INNER JOIN mark_configs t2 ON t1.term = t2.term AND t1.my_class_id = t2.my_class_id AND t1.subject_id = t2.subject_id AND t1.id > t2.id');
            try {
                Schema::table('mark_configs', function (Blueprint $table) {
                    $table->unique(['term', 'my_class_id', 'subject_id']);
                });
            } catch (\Throwable $e) {
                // Unique might already exist (e.g. from prior run)
            }
        }
        // If term exists but unique missing (e.g. migration failed after dropColumn on a tenant with duplicates)
        if (Schema::hasColumn('mark_configs', 'term') && !Schema::hasColumn('mark_configs', 'exam_id')) {
            DB::statement('DELETE t1 FROM mark_configs t1 INNER JOIN mark_configs t2 ON t1.term = t2.term AND t1.my_class_id = t2.my_class_id AND t1.subject_id = t2.subject_id AND t1.id > t2.id');
            try {
                Schema::table('mark_configs', function (Blueprint $table) {
                    $table->unique(['term', 'my_class_id', 'subject_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if unique already exists
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('exams') && Schema::hasColumn('mark_configs', 'term')) {
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->dropUnique(['term', 'my_class_id', 'subject_id']);
                $table->unsignedInteger('exam_id')->nullable()->after('subject_id');
            });
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->unique(['exam_id', 'my_class_id', 'subject_id']);
                $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
                $table->dropColumn('term');
            });
        }
    }
}
