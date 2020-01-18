<?php

namespace App\Http\Controllers;

use App\TestRequest;
use foo\bar;
use Illuminate\Http\Request;

class TestRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $test_request_model;
    public function __construct()
    {
        $this->middleware('auth');
        $this->test_request_model = new TestRequest();
    }

    public function index()
    {
        //
        $all_requests = $this->test_request_model->get();
        return view('admin/test_request_list',['test_requests'=>$all_requests]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $quiz_id = $request->quiz_id;
        ?>
        <div class="row">
            <div class="col-md-12">
                <span class="text-dark">
                    Are you sure you want to take this test again?
                </span>
                <form method="post" action="<?php echo route('save-test-request'); ?>">
                    <input type="hidden" name="student_id" value="<?php echo auth()->user()->id ?>">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="_token" id="csrf-token" value="<?php echo csrf_token() ?>" />
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
        <?php
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
            'student_id'    => $request->student_id,
            'quiz_id'       => $request->quiz_id,
            'submit_time'   => date('d-m-Y h:i:s a')
        );
        $save_request = $this->test_request_model->insert($data);
        if ($save_request){
            $request->session()->put('success','Your request submitted successfully, our team contact you');
            return back();
        } else{
            $request->session()->put('error','Please try again');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TestRequestController  $testRequestController
     * @return \Illuminate\Http\Response
     */
    public function show(TestRequestController $testRequestController)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TestRequestController  $testRequestController
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
        $request_id  = $request->request_id;
        $test_status = $request->test_status;
        ?>
        <div class="row">
            <div class="col-md-12">
                <span class="text-dark">
                    Are you sure you?
                </span>
                <form method="post" action="<?php echo route('update_test_request_status'); ?>">
                    <input type="hidden" name="request_id" value="<?php echo $request_id ?>">
                    <input type="hidden" name="is_accepted" value="<?php echo $test_status ?>">
                    <input type="hidden" name="_token" id="csrf-token" value="<?php echo csrf_token() ?>" />
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TestRequestController  $testRequestController
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $data = array(
            'is_accepted'    => $request->is_accepted,
        );
        $save_request = $this->test_request_model->where(array('id'=>$request->request_id))->update($data);
        if ($save_request){
            $request->session()->put('success','Operation performed successfully');
            return back();
        } else{
            $request->session()->put('error','Please try again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TestRequestController  $testRequestController
     * @return \Illuminate\Http\Response
     */
    public function destroy(TestRequestController $testRequestController)
    {
        //
    }
    public function test_result_check($quiz_id){
        $student_id = auth()->user()->id;
        $request_count = $this->test_request_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->count();
        /*if ($request_count){
            $is_requested = $this->test_request_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->first();
            return $is_requested;
        }else{
            return 3;
        }*/
        $is_requested = $this->test_request_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->first();
        return $is_requested;
    }
}
