<?php

namespace App\Http\Controllers;

use App\TestSchedule;
use Illuminate\Http\Request;
use App\Courses;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use App\StudentCourseRegitration;
use App\User;
class TestScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $schedule_model;
    protected $courses_model;
    public function __construct()
    {
        $this->middleware('auth');
        $this->schedule_model   = new TestSchedule();
        $this->courses_model    = new Courses();
    }

    public function index()
    {
        //
        $schedule_list = $this->schedule_model->get();
//        dd($schedule_list);
        return view('admin/course_schedule',['schedule_list'=>$schedule_list]);
    }
    public function get_course_name($id){
        $course_name = $this->courses_model->where(array('id'=>$id))->first();
        echo $course_name; exit;
        $course_name = $course_name['name'];
        return $course_name;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $courses = $this->courses_model->orderBy('name','asc')->get();
        return view('admin/add_edit_course_schedule',['form_type'=>1,'courses'=>$courses]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        try{
            $request->validate([
                'title'     =>  'required',
                'test_date' =>  'required',
                'course_id' =>  'required',
                'message'   =>  'required',
            ]);
            $data = array(
                'title'         =>  $request->title,
                'message'       =>  $request->message,
                'test_date'     =>  $request->test_date,
                'course_id'     =>  $request->course_id,
                'created_by'    =>  1,
            );
            $save_schedule = DB::table('test_schedule')->insert($data);
            if ($save_schedule){
                $this->send_sms_for_reminder(1,array($request->course_id));
                $request->session()->put('success','operation performed successfully');
                return redirect('schedule_list');
            } else{
                $request->session()->put('error','operation not performed, please try again');
                return back();
            }
        }catch (Exception $exception){
            print_r($exception);
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TestSchedule  $testSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(TestSchedule $testSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TestSchedule  $testSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $schedule_data  = DB::table('test_schedule')->where(array('id'=>$request->id))->first();
        $courses        = $this->courses_model->get();
        return view('admin/add_edit_course_schedule',['form_type'=>2,'courses'=>$courses,'data'=>$schedule_data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TestSchedule  $testSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $request->validate([
            'title'     =>  'required',
            'test_date' =>  'required',
            'course_id' =>  'required',
            'message'   =>  'required',
        ]);
        $data = array(
            'course_id'     =>  $request->course_id,
            'title'         =>  $request->title,
            'message'       =>  $request->message,
            'test_date'     =>  $request->test_date,
            'updated_by'    =>  auth()->user()->id,
            'updated_at'    =>  date('d-m-y h:i:s a')
        );
        $save_schedule = DB::table('test_schedule')->where(array('id'=>$request->id))->update($data);
        if ($save_schedule){
            $this->send_sms_for_reminder(2,array($request->course_id));
            $request->session()->put('success','operation performed successfully');
            return redirect('schedule_list');
        } else{
            $request->session()->put('error','operation not performed, please try again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TestSchedule  $testSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $delete_record = DB::table('test_schedule')->where(array('id'=>$request->id))->delete();
        if ($delete_record){
            $request->session()->put('success','operation performed successfully');
            return redirect('schedule_list');
        } else{
            $request->session()->put('error','operation not performed, please try again');
            return back();
        }
    }
    public function send_sms_for_reminder($type,$course_id){
        $student_numbers = StudentCourseRegitration::join('users', function($join)
        {
            $join->on('users.id', '=', 'tbl_student_reg.student_id');
        })
            ->select('users.name','users.phone_number', 'tbl_student_reg.course_id')
            ->whereIn('tbl_student_reg.course_id',$course_id)
            ->get();
        foreach ($student_numbers as $number){
            if ($type == 1){
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. New test schedule is uploaded ';
            } else{
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. Test schedule information is changed ';
            }
            $push_data = array(
                'title' =>  $number['phone_number'],
                'body'  =>  $msg,
            );
            $this->CustomerPushNotification($push_data);
        }
        return true;
    }
    /* to be move in trait */
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
        $tomorrow_date = date('Y-m-d',strtotime("tomorrow"));
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
        }
    }
}
