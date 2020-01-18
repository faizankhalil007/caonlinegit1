<?php

namespace App\Http\Controllers;

use App\StudentQuizAnswer;
use Illuminate\Http\Request;
use App\StudentCourseRegitration;
use Illuminate\Support\Facades\DB;
use App\TestSchedule;
use App\TestQuestionAnswerByStudent;
use App\Quiz;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $course_registration_model;
    protected $test_schedule_model;
    protected $test_answer_model;
    protected $quiz_model;
    protected $student_quiz_answer;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['auth', 'verified']);
        $this->course_registration_model = new StudentCourseRegitration();
        $this->test_answer_model         = new TestQuestionAnswerByStudent();
        $this->test_schedule_model       = new TestSchedule();
        $this->quiz_model                = new Quiz();
        $this->student_quiz_answer_model = new StudentQuizAnswer();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->user()->user_type == 4){
            $course_data = $this->course_registration_model->where(array('student_id'=>auth()->user()->id))->count();
            if ($course_data > 0){
                $course_id = $this->course_registration_model->where(array('student_id'=>auth()->user()->id))->first();
                $course_id = explode(',',$course_id['course_id']);
                $schedule_list = $this->test_schedule_model->whereIn('course_id',$course_id)->get();
                $taken_count = $this->student_quiz_answer_model->where(array('student_id'=>auth()->user()->id))->groupBy('student_id')->count();
                $total_quiz = $this->quiz_model->whereIn('course_id',$course_id)->count();
                $total_pass = $this->student_quiz_answer_model->where(array('student_id'=>auth()->user()->id,'pass_fail'=>1))->count();
                $total_fail = $this->student_quiz_answer_model->where(array('student_id'=>auth()->user()->id,'pass_fail'=>0))->count();
                return view('student/dashboard',['schedule_list'=>$schedule_list,'taken_count'=>$taken_count,'test_count'=>$total_quiz,'total_pass'=>$total_pass,'total_fail'=>$total_fail]);
            } else{
                return redirect('add_class_course');
            }
        } elseif (auth()->user()->user_type == 1 || auth()->user()->user_type == 2 || auth()->user()->user_type == 3){
            $total_student = DB::table('users')->where(array('user_type'=>4))->count();
            $total_quiz = DB::table('tbl_quizes')->count();
            $pending_quiz = DB::table('tbl_quizes')->count();
            return view('admin/dashboard',['total_student'=>$total_student,'total_quiz'=>$total_quiz]);
        } else{
            redirect('logout');
        }
//        return view('home');
    }
    /* students list */
    public function regitered_students(Request $request){
        if (isset($request->student_name)){
            $student_list =  DB::table('users')->where(array('user_type'=>4))->where('name','like','%'.$request->student_name.'%')->paginate(15);
        }else{
            $student_list =  DB::table('users')->where(array('user_type'=>4))->paginate(15);
        }
        return view('admin/students_list',['students_list'=>$student_list]);
    }
    public function delete_student(Request $request){
        $delete_student = DB::table('users')->where(array('id'=>$request->id))->delete();
        if ($delete_student){
            $request->session()->put('success','Student deleted successfully');
            return back();
        }else{
            $request->session()->put('error','error in deleting student');
            return back();
        }
    }
    public function edit_student(Request $request){
        $student_detail = DB::table('users')->where(array('id'=>$request->id))->first();
        return view('admin/add_edit_student',['student_detail'=>$student_detail,'form_type'=>2]);
    }
    public function update_student(Request $request){
        $request->validate([
            'name'              =>  'required|max:50',
            'email'             =>  'required|email',
            'student_session'   =>  'required',
            'phone_number'      =>  'required',
        ]);
        if ($request->password){
            $password = Hash::make($request->password);
        }else{
            $password = $request->old_password;
        }
        $student_data = array(
            'name'              =>  $request->name,
            'email'             =>  $request->email,
            'phone_number'      => $request->phone_number,
            'student_session'   => $request->student_session,
            'user_type'         => 4,
            'password'          => $password,
        );
        $update = DB::table('users')->where(array('id'=>$request->id))->update($student_data);
        if ($update){
            $request->session()->put('success','operation performed successfully');
            return redirect('students_list');
        } else{
            $request->session()->put('error','something went wrong please try again');
            return back();
        }
    }
    /* students list */

}
