<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestQuestionAnswerByStudent extends Model
{
    //
    protected $table = 'student_answers';
    public function question_name(){
        return $this->belongsTo('App\TestQuestions','question_id','id');
    }
    public function student_name(){
        return $this->belongsTo('App\User','student_id','id');
    }
}
