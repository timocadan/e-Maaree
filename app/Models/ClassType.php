<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    protected $fillable = ['name', 'code'];

    public function myClasses()
    {
        return $this->hasMany(MyClass::class, 'class_type_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'class_type_id');
    }
}
