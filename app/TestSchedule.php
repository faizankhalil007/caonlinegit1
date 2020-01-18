<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestSchedule extends Model
{
    //
    protected $table = 'test_schedule';
    public function course_name()
    {
        return $this->belongsTo('App\Courses','course_id','id');
    }
}
