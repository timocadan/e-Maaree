<?php

namespace App\Models;

use Eloquent;

class Attendance extends Eloquent
{
    protected $fillable = [
        'student_id',
        'my_class_id',
        'section_id',
        'date',
        'status',
        'session',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function user()
    {
        return $this->student();
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
