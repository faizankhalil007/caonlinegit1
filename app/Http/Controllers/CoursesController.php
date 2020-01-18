<?php

namespace App\Http\Controllers;

use App\Courses;
use Illuminate\Http\Request;
use App\Traits\CommonTraits;
use App\Classes;
use PhpParser\Node\Expr\Array_;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $courses_model;
    protected $classes_model;
    use CommonTraits;
    public function __construct()
    {
        $this->middleware('auth');
        $this->courses_model = new Courses();
        $this->classes_model = new Classes();
    }

    public function index()
    {
        //
        $courses_list = $this->courses_model->get();
        return view('admin/courses_list',['courses_data'=>$courses_list]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $classes = $this->classes_model->get();
        return view('admin/add_edit_course',['form_type'=>1,'classes'=>$classes]);
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
        $request->validate([
            'name'          =>  'required|max:50',
            'course_code'   => 'required|max:50',
            'class_id'      => 'required'
        ]);
        $data = array(
            'name'          =>  $request->name,
            'course_code'   =>  $request->course_code,
            'class_id'      =>  $request->class_id,
            'created_by'    =>  auth()->user()->id,
            'created_at'    =>  date('d-m-Y H:i:s a')
        );
        $save_course = $this->courses_model->insert($data);
        if ($save_course){
            $this->crate_data_log('',$data,'Save Course');
            $request->session()->put('success','Operation performed successfully');
            return redirect('courses-list');
        }else{
            $request->session()->put('error','something went wrong please try again');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Courses  $courses
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
        $class_id  = $request->id;
        $courses_list = $this->courses_model->where(array('class_id'=>$class_id))->get();
        return view('admin/courses_list',['courses_data'=>$courses_list]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Courses  $courses
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $course_data  = $this->courses_model->where(array('id'=>$request->id))->first();
        $classes = $this->classes_model->get();
        return view('admin/add_edit_course',['form_type'=>2,'course_info'=>$course_data,'classes'=>$classes]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Courses  $courses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Courses $courses)
    {
        //
        $request->validate([
            'name'          =>  'required|max:50',
            'course_code'   => 'required|max:50',
            'class_id'      => 'required'
        ]);
        $data = array(
            'name'          =>  $request->name,
            'course_code'   =>  $request->course_code,
            'class_id'      =>  $request->class_id,
            'updated_by'    =>  auth()->user()->id,
            'updated_at'    =>  date('d-m-Y H:i:s a')
        );
//        $this->crate_data_log($request->id,$data,'Update Course');
        $update_course = $this->courses_model->where(array('id'=>$request->id))->update($data);
        if ($update_course){
            $request->session()->put('success','Operation performed successfully');
            return redirect('courses-list');
        }else{
            $request->session()->put('error','something went wrong please try again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Courses  $courses
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $this->courses_model->where(array('id'=>$request->id))->delete();
        return redirect('courses-list');
    }
    /* get courses by module id */
    public function get_course_by_module_id(Request $request){
        $courses_list = $this->courses_model->where(array('class_id'=>$request->class_id))->get();
        return view('admin/courses_list',['courses_data'=>$courses_list]);
    }
    /* get courses by module id */
    public function crate_data_log($id,$data,$operation)
    {
        if ($id){
            $old_data = $this->courses_model->where(array('id'=>$id))->first();

            $data = array(
                'old_data'  =>  $old_data,
                'new_data'  =>  $data,
            );
        } else{
            $data = $data;
        }
        $log = $this->crate_log($data,'Course Controller',$operation);
        if ($log){
            return true;
        } else{
            return false;
        }
    }
    public function get_course_by_class_id($class_id){
        $courses_list = $this->courses_model->where(array('class_id'=>$class_id))->get();
        return $courses_list;
    }
}
