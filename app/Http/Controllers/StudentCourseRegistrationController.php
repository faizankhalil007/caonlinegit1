<?php

namespace App\Http\Controllers;

use App\StudentCourseRegitration;
use Illuminate\Http\Request;
use App\Classes;
use App\Courses;

class StudentCourseRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $registration_model;
    protected $classes_model;
    protected $course_model;
    public function __construct()
    {
        $this->middleware('auth');
        $this->registration_model = new StudentCourseRegitration();
        $this->classes_model      = new Classes();
        $this->course_model       = new Courses();
    }

    public function index()
    {
        //
        $classes_list = $this->classes_model->get();
        return view('student/add_course',['classes_data'=>$classes_list]);
    }
    /* get courses by class id */
    public function get_course_by_class_id(Request $request){
        $class_id = $request->class_id;
        $courses    = $this->course_model->where(array('class_id'=>$class_id))->get(['id','name']);
        return $courses;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $existing_data = $this->registration_model->where(array('student_id'=>auth()->user()->id))->first();
//        $course_id = $request->course_id;
        $course_id = implode(',',$request->course_id);
        $current_date = date('Y-m-d');
        $expiry_date = date('Y-m-d', strtotime("+3 months", strtotime($current_date)));
        $data = array(
            'student_id'    =>  auth()->user()->id,
            'class_id'      =>  $request->class_id,
            'course_id'     =>  $course_id,
            'registration_date'   =>  date('d-m-Y'),
            'expiry_date'   =>  $expiry_date,
        );
        $register_course = $this->registration_model->insert($data);
        if ($register_course){
            $request->session()->put('success','congratulations');
            return redirect('home');
        }else{
            $request->session()->put('error','something went wrong');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StudentCourseRegitration  $studentCourseRegitration
     * @return \Illuminate\Http\Response
     */
    public function show(StudentCourseRegitration $studentCourseRegitration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StudentCourseRegitration  $studentCourseRegitration
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentCourseRegitration $studentCourseRegitration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StudentCourseRegitration  $studentCourseRegitration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentCourseRegitration $studentCourseRegitration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StudentCourseRegitration  $studentCourseRegitration
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentCourseRegitration $studentCourseRegitration)
    {
        //
    }
    /* get student class and course information */
    public function registred_course_information(Request $request){
        $course_info    = $this->registration_model->where(array('student_id'=>auth()->user()->id))->get();
        return view('student/course_information',['course_info'=>$course_info]);
    }
    public function get_course_name_by_id($id){
        $course_name = $this->course_model->where(array('id'=>$id))->first('name');
        return $course_name->name;
    }
}
