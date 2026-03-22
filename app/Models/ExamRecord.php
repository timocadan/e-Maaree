<?php

namespace App\Models;

use Eloquent;

class ExamRecord extends Eloquent
{
    protected $fillable = ['term', 'my_class_id', 'student_id', 'section_id', 'af', 'af_id', 'ps', 'ps_id', 't_comment', 'p_comment', 'year', 'total', 'ave', 'class_ave', 'pos', 'class_pos'];
}
