<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentCourseRegitration extends Model
{
    //
    protected $table = 'tbl_student_reg';
    public function class_name(){
        return $this->belongsTo('App\Classes','class_id','id');
    }
    public function course_name(){
        return $this->belongsTo('App\Courses','course_id','id');
    }
}
