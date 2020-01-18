<?php

namespace App\Http\Controllers;

use App\Quiz;
use App\User;
use Illuminate\Http\Request;
use App\Classes;
use App\Courses;
use App\Traits\CommonTraits;
use App\StudentCourseRegitration;
use Benwilkins\FCM\FcmMessage;
use Illuminate\Notifications\Notifiable;
use App\TestQuestions;
use App\StudentQuizAnswer;
use Illuminate\Support\Arr;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use CommonTraits;
    protected $class_model;
    protected $course_model;
    protected $quiz_model;
    protected $student_registration_model;
    protected $questions_model;
    protected $quiz_answer_model;
    /* creating constructor */
    public function __construct()
    {
        $this->middleware('auth');
        $this->quiz_model   = new Quiz();
        $this->class_model  = new Classes();
        $this->course_model = new Courses();
        $this->student_registration_model = new StudentCourseRegitration();
        $this->questions_model  = new TestQuestions();
        $this->quiz_answer_model = new StudentQuizAnswer();
    }

    public function index()
    {
        //
        $quiz_detail = $this->quiz_model->get();
        $is_able = 0;
//        $return_message = $this->toFcm(1);
        return view('admin/quizes_list',['quiz_detail'=>$quiz_detail]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $classes = $this->class_model->get();
        $courses = $this->course_model->get();
        return view('admin/add_edit_quiz',['form_type'=>1,'classes'=>$classes,'courses'=>$courses]);
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
        $data = array(
            'title'         =>  $request->title,
            'class_id'      =>  $request->class_id,
            'course_id'     =>  $request->course_id,
            'quiz_time'     =>  $request->quiz_time,
            'created_by'    =>  auth()->user()->id,
            'created_at'    =>  date('d-m-Y H:i:s'),
            'added_at'      =>  strtotime('now')
        );
        $quiz_id = $this->quiz_model->insertGetId($data);
        $i = 0;
        $j = 0;
        $total_marks = 0;
        foreach ($request->questions as $que){
            $total_marks +=$request->marks[$j];
            $question = array(
                'quiz_id'       =>  $quiz_id,
                'question'      =>  $que,
                'question_mark' =>  $request->marks[$i],
                'created_at'    =>  date('d-m-Y H:i:s')
            );
            $save_quiz = $this->questions_model->insert($question);
            $i++;
            $j++;
        }
        if ($save_quiz){
            $this->quiz_model->where(array('id'=>$quiz_id))->update(array('total_marks'   =>  $total_marks));
            $request->session()->put('success','Operation performed successfully');
            $this->send_sms_for_reminder(1,array($request->course_id),$request->title,$request->quiz_time);
            return redirect('quiz_list');
        } else{
            $request->session()->put('error','something went wrong, please try again');
            return back();
        }
    }
    public function is_attempted_or_not($quiz_id){
        $student_id = auth()->user()->id;
        $added_time = $this->quiz_model->where(array('id'=>$quiz_id))->first()->added_at;
        $quiz_count = $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->count();
        $current_time  = strtotime("now");
        $quiz_add_time = $added_time+24*60*60;
        if ($current_time > $quiz_add_time){
            if ($quiz_count > 0){
                return 1;
            } else{
                return 0;
            }

        } else{
            return 1;
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // this function is use to search for specific topic, month, course or class quiz
        $where = array();
        if ($request->month){
            $where = array_merge($where,array(
                "quiz_month"    =>  $request->month
            ));
        }
        if ($request->topic){
            $where = array_merge($where,array(
                "title"  =>  $request->title
            ));
        }
        if ($request->class_id){
            $where = array_merge($where,array(
                "class_id"  =>  $request->class_id
            ));
        }
        if ($request->course_id){
            $where = array_merge($where,array(
                "course_id"  =>  $request->course_id
            ));
        }
        $search_result = $this->quiz_model->where($where)->get();
        if ($search_result){
            return view('admin/quizes_list',['quiz_detail'=>$search_result]);
        } else{
            $request->session()->put('success','no record found in this month');
            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $classes = $this->class_model->get();
        $courses = $this->course_model->get();
        $quiz_info = $this->quiz_model->where(array('id'=>$request->id))->first();
        $questions = $this->questions_model->where(array('quiz_id'=>$request->id))->get();
        return view('admin/add_edit_quiz',['form_type'=>2,'classes'=>$classes,'courses'=>$courses,'quiz_info'=>$quiz_info,'questions'=>$questions]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $request->validate([
            'class_id'  =>  'required',
            'course_id' =>  'required',
            'quiz_time' =>  'required',
            'questions' =>  'required|array',
            'questions.*' =>  'required',
            'title'     =>  'required',
        ]);
        $data = array(
            'title'         =>  $request->title,
            'class_id'      =>  $request->class_id,
            'course_id'     =>  $request->course_id,
            'quiz_time'     =>  $request->quiz_time,
            'updated_by'    =>  auth()->user()->id,
            'updated_at'    =>  date('d-m-Y H:i:s')
        );

        $update_quiz = $this->quiz_model->where(array('id'=>$request->id))->update($data);
        $i = 0;
        $j = 0;
        $total_marks = 0;
//        echo count($request->questions); exit;
        foreach ($request->questions as $que){
            $total_marks +=$request->marks[$j];
            $question_array = array(
                'question'      =>  $que,
                'quiz_id'       =>  $request->id,
                'question_mark' =>  $request->marks[$i],
                'updated_at'    =>  date('d-m-Y H:i:s')
            );
            if (isset($request->question_id[$i])){
                $this->questions_model->where(array('id'=>$request->question_id[$i]))->update($question_array);
            } else{
                $this->questions_model->insert($question_array);
            }
            $i++;
            $j++;
        }
        if ($update_quiz){
            $request->session()->put('success','Operation performed successfully');
            $this->quiz_model->where(array('id'=>$request->id))->update(array('total_marks'   =>  $total_marks));
            $this->send_sms_for_reminder(2,array($request->course_id),$request->title,$request->quiz_time);
            return redirect('quiz_list');
        } else{
            $request->session()->put('error','something went wrong, please try again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Quiz  $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $id = $request->id;
        $delete_record = $this->quiz_model->where(array('id'=>$id))->delete();
        if ($delete_record){
            $this->questions_model->where(array('quiz_id'=>$id))->delete();
            $request->session()->put('success','Operation performed successfully');
            return redirect('quiz_list');
        } else{
            $request->session()->put('error','something went wrong, please try again');
            return back();
        }
    }
    /* for pop up */
    public function quiz_questions(Request $request){
        $id = $request->quiz_id;
        $questions = $this->quiz_model->where(array('id'=>$id))->first();
        $questions_data = json_decode($questions->questions);
        ?>
            <span class="text-danger h5">
                 Once you pressed Ok you have to submit test in given time. Are you sure to start quiz
            </span>
        <a href="<?php echo route('start-student-quiz',['questions_data'=>$questions_data,'quiz_id'=>$id])?>" class="btn btn-danger">Start</a>

        <?php
    }
    public function get_quiz_by_student_id(Request $request){
        $student_id = auth()->user()->id;
        $student_class_course = $this->get_student_class_course($student_id);
        if (is_array($student_class_course)){
            $course_id = explode(',',$student_class_course['course_id']);
            $quiz_detail = $this->quiz_model->whereIn('course_id',$course_id)->get();
        }else{
            $quiz_detail = array();
        }
        return view('student/quiz_list',['quiz_detail'=>$quiz_detail]);
    }
    public function get_student_class_course($student_id){
        $student_info = $this->student_registration_model->where(array('student_id'=>$student_id))->get();
        $return_data = 0;
        foreach ($student_info as $info){
            if ($info->expiry_date > date('Y-m-d')){
                $return_data = array(
                    'class_id'  => $info->class_id,
                    'course_id' => $info->course_id,
                );
            }else{
                $return_data = 0;
            }
        }
        return $return_data;
    }
    public function send_sms_for_reminder($type,$course_id,$title,$time){
        $student_numbers = StudentCourseRegitration::join('users', function($join)
        {
            $join->on('users.id', '=', 'tbl_student_reg.student_id');
        })
            ->select('users.name','users.phone_number', 'tbl_student_reg.course_id')
            ->whereIn('tbl_student_reg.course_id',$course_id)
            ->get();
        foreach ($student_numbers as $number){
            if ($type == 1){
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. New quiz "'.$title.'" is uploaded. Time to attempt this test is "'.$time.'minutes". ';
            } else{
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. Quiz "'.$title.'" is changed. Time to attempt this test is "'.$time.'minutes".  ';
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
}
