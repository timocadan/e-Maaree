<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('student_id');
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('section_id');
                $table->date('date');
                $table->string('status', 20)->default('present');
                $table->string('session', 20);
                $table->timestamps();
            });
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['my_class_id', 'section_id', 'date'], 'attendances_class_section_date_index');
            $table->unique(['student_id', 'date', 'session'], 'attendances_student_date_session_unique');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('my_class_id')->references('id')->on('my_classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
