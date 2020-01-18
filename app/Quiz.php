<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    //
    protected $table = 'tbl_quizes';
    protected $dates = ['created_at','updated_at'];

    public function class_name()
    {
        return $this->belongsTo('App\Classes','class_id','id');
    }
    public function course_name()
    {
        return $this->belongsTo('App\Courses','course_id','id');
    }
    public function student_test_result(){
        return $this->belongsTo('App\StudentQuizAnswer','quiz_id','id');
    }
}
