<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestRequest extends Model
{
    //
    protected $table = 'student_test_request';
    public function student_name(){
        return $this->belongsTo('App\User','student_id','id');
    }
    public function quiz_title(){
        return $this->belongsTo('App\Quiz','quiz_id','id');
    }
}
