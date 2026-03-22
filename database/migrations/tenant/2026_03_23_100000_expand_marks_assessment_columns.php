<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandMarksAssessmentColumns extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('marks')) {
            return;
        }

        $missing = [];
        foreach (['t5', 't6', 't7', 't8', 't9', 't10'] as $col) {
            if (!Schema::hasColumn('marks', $col)) {
                $missing[] = $col;
            }
        }

        if (empty($missing)) {
            return;
        }

        Schema::table('marks', function (Blueprint $table) use ($missing) {
            foreach ($missing as $col) {
                $table->integer($col)->nullable();
            }
        });
    }

    public function down()
    {
        // Keep it non-destructive on downgrade (buffer columns may be required by existing templates).
    }
}

