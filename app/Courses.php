<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    //
    protected $table = 'tbl_courses';
    public function class_name()
    {
        return $this->belongsTo('App\Classes','class_id','id');
    }

}
