<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RefactorMarkConfigsToJson extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('mark_configs', 'ca1_label')) {
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->json('configuration')->nullable()->after('subject_id');
            });
            $configs = DB::table('mark_configs')->get();
            foreach ($configs as $row) {
                $arr = [];
                if (isset($row->ca1_max) && (int) $row->ca1_max > 0) {
                    $arr[] = ['label' => $row->ca1_label ?? '1st CA', 'max' => (int) $row->ca1_max];
                }
                if (isset($row->ca2_max) && (int) $row->ca2_max > 0) {
                    $arr[] = ['label' => $row->ca2_label ?? '2nd CA', 'max' => (int) $row->ca2_max];
                }
                if (isset($row->ca3_max) && (int) $row->ca3_max > 0) {
                    $arr[] = ['label' => $row->ca3_label ?? 'Assignment', 'max' => (int) $row->ca3_max];
                }
                if (empty($arr)) {
                    $arr = [['label' => '1st CA', 'max' => 20], ['label' => '2nd CA', 'max' => 20], ['label' => 'Assignment', 'max' => 0]];
                }
                DB::table('mark_configs')->where('id', $row->id)->update(['configuration' => json_encode($arr)]);
            }
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->dropColumn(['ca1_label', 'ca1_max', 'ca2_label', 'ca2_max', 'ca3_label', 'ca3_max']);
            });
        } elseif (!Schema::hasColumn('mark_configs', 'configuration')) {
            Schema::table('mark_configs', function (Blueprint $table) {
                $table->json('configuration')->nullable()->after('subject_id');
            });
        }
    }

    public function down()
    {
        Schema::table('mark_configs', function (Blueprint $table) {
            $table->string('ca1_label', 60)->default('1st CA');
            $table->unsignedTinyInteger('ca1_max')->default(20);
            $table->string('ca2_label', 60)->default('2nd CA');
            $table->unsignedTinyInteger('ca2_max')->default(20);
            $table->string('ca3_label', 60)->default('Assignment');
            $table->unsignedTinyInteger('ca3_max')->default(0);
        });
        Schema::table('mark_configs', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });
    }
}
