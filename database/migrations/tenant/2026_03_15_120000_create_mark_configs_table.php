<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarkConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('mark_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('my_class_id');
            $table->unsignedInteger('subject_id');
            $table->json('configuration')->nullable();
            $table->unsignedTinyInteger('exam_max')->default(60);
            $table->unsignedTinyInteger('active_slot')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'my_class_id', 'subject_id']);
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('my_class_id')->references('id')->on('my_classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mark_configs');
    }
}
