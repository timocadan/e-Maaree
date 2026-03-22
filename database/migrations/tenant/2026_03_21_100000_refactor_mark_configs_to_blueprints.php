<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RefactorMarkConfigsToBlueprints extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mark_templates')) {
            Schema::create('mark_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('configuration')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('mark_templates', 'name')) {
                Schema::table('mark_templates', function (Blueprint $table) {
                    $table->string('name')->after('id');
                });
            }
            if (!Schema::hasColumn('mark_templates', 'configuration')) {
                Schema::table('mark_templates', function (Blueprint $table) {
                    $table->json('configuration')->nullable()->after('name');
                });
            }
        }

        Schema::dropIfExists('mark_configs');

        Schema::create('mark_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('class_type_id');
            $table->unsignedSmallInteger('term_id');
            $table->unsignedBigInteger('mark_template_id')->nullable();
            $table->string('school_year', 15);
            $table->timestamps();

            $table->unique(['class_type_id', 'term_id', 'school_year'], 'mark_configs_level_term_year_unique');
            $table->foreign('class_type_id')->references('id')->on('class_types')->onDelete('cascade');
            $table->foreign('mark_template_id')->references('id')->on('mark_templates')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mark_configs');
        Schema::dropIfExists('mark_templates');
    }
}
