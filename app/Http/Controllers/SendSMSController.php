<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TestSchedule;
use App\Courses;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use App\StudentCourseRegitration;
use App\User;

class SendSMSController extends Controller
{
    //
    protected $schedule_model;
    protected $courses_model;
    public function __construct()
    {
        $this->schedule_model   = new TestSchedule();
        $this->courses_model    = new Courses();
    }
    public function CustomerPushNotification($data)
    {
        $headers = array
        (
            'Authorization: key=AAAAV9Mf4wo:APA91bEB_q0NbcDqZMPpGPYDg4DvfBkbBv7vldds2WgOSYEXnEP_cmoynGlVd2BkvH7jALa1ebA5-U6ZWD72fWLaNtfo_Ib-1E3-oq41V-U2TgR82mn-Z2CC41uNq7MksTrd9RtpKpJu',
            'Content-Type: application/json'
        );
        $fields = array
        (
            'to'            => '/topics/SmsSend',
            'data'          => $data,
            "priority"      => "high"
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
        // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

        $result = curl_exec($ch);
        if ($result === FALSE)

        {
            die('FCM Send Error: ' . curl_error($ch));
        }

        // echo $result;
        curl_close($ch);
        return $ch;
    }
    /* for cron job */
    public function set_cron_job_for_reminder(Request $request){
        /*$tomorrow_date = date('Y-m-d',strtotime("tomorrow"));
        $student_numbers = StudentCourseRegitration::join('users', function($join)
        {
            $join->on('users.id', '=', 'tbl_student_reg.student_id');
        })
            ->select('users.name','users.phone_number', 'tbl_student_reg.course_id', 'tbl_student_reg.title')
            ->where('tbl_student_reg.test_date','=',date('Y-m-d',strtotime("tomorrow")))
            ->get();
        foreach ($student_numbers as $number){
            $msg = 'Hey, '.$number['name'].' you have a test of topic "'.$number['title'].' on '.$tomorrow_date.'. Best of luck "';
            $push_data = array(
                'title' =>  $number['phone_number'],
                'body'  =>  $msg,
            );
            $this->CustomerPushNotification($push_data);
        }*/
        $course_id = array(4);
        $student_numbers = StudentCourseRegitration::join('users', function($join)
        {
            $join->on('users.id', '=', 'tbl_student_reg.student_id');
        })
            ->select('users.name','users.phone_number', 'tbl_student_reg.course_id')
            ->whereIn('tbl_student_reg.course_id',$course_id)
            ->get();

        foreach ($student_numbers as $number){
            $msg = 'Hey, '.$number['name'].' you have a test of topic "'.$number['title'].' on '.date('Y-m-d',strtotime("tomorrow")).'. Best of luck "';
            $push_data = array(
                'title' =>  $number['phone_number'],
                'body'  =>  $msg,
            );
//            $this->CustomerPushNotification($push_data);
        }

    }
}
