<?php

namespace App\Http\Controllers;

use App\QuizAnswer;
use Illuminate\Http\Request;
use App\Quiz;
use App\TestQuestions;
use App\Traits\CommonTraits;
use App\TestQuestionAnswerByStudent;
class QuizAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $quiz_solution_model;
    protected $quiz_model;
    protected $questions_model;
    protected $test_question_answer_model;
    use CommonTraits;
    public function __construct()
    {
        $this->middleware('auth');
        $this->quiz_solution_model = new QuizAnswer();
        $this->quiz_model          = new Quiz();
        $this->questions_model     = new TestQuestions();
        $this->test_question_answer_model = new TestQuestionAnswerByStudent();
    }

    public function index()
    {
        //
        $all_quiz_list = $this->quiz_model->get();
        return view('admin/quiz_list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $all_questions = $this->questions_model->where(array('quiz_id'=>$request->quiz_id))->get();
        return view('admin/upload_solution',['quiz_id'=>$request->quiz_id,'questions'=>$all_questions]);
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
        $image_name = array();
        $save_answer = array();
        $questions = $request->question_id;
        $question_id = array();
        foreach ($request->question_id as $question){
            if ($request->hasFile('answer_file'.$question)) {
                $images = $request->file('answer_file'.$question);
                $question_id[] = array($question);
                foreach ($images as $image){
                    $extension = $image->getClientOriginalExtension();
                    $filename = uniqid().'.'.$extension;
                    if (!file_exists('uploads/quiz_solution/'.date('Y'))){
                        mkdir('uploads/quiz_solution/'.date('Y'),0777,true);
                    }
                    if (!file_exists('uploads/quiz_solution/'.date('Y').'/'.date('m'))){
                        mkdir('uploads/quiz_solution/'.date('Y').'/'.date('m'),0777,true);
                    }
                    if (!file_exists('uploads/quiz_solution/'.date('Y').'/'.date('m').'/'.date('d'))){
                        mkdir('uploads/quiz_solution/'.date('Y').'/'.date('m').'/'.date('d'),0777,true);
                    }
                    $destinationPath = 'uploads/quiz_solution/'.date('Y').'/'.date('m').'/'.date('d');
                    $image->move($destinationPath, $filename);
                    $answer_data = array(
                        'quiz_solution' => $destinationPath.'/'.$filename,
                        'quiz_id'       => $request->quiz_id,
                        'question_id'   => $question,
                    );
                    $is_exist = $this->quiz_solution_model->where(array('question_id'=>$question))->first();
                    /*if ($is_exist){
                        $save_answer[] = $this->quiz_solution_model->where(array('question_id'=>$question))->update($answer_data);
                        $type = 2;
                    }else{
                        $save_answer[] = $this->quiz_solution_model->insert($answer_data);
                        $type = 1;
                    }*/
                    $save_answer[] = $this->quiz_solution_model->insert($answer_data);
                    $type = 1;
                }
            }
        }
        if ($save_answer){
            $request->session()->put('success','Operation Performed successfully');
            $this->send_sms_for_reminder($type,$question_id);
            return back();
//            return redirect('get-quiz-for-result');
        }else{
            $request->session()->put('error','Answer is not submitted please try again');
            return back();
        }

    }

    /*  for fetching user data */
    public function send_sms_for_reminder($type,$question_id){
        $student_numbers = TestQuestionAnswerByStudent::join('users', function($join)
        {
            $join->on('users.id', '=', 'student_answers.student_id');
        })
            ->select('users.name','users.phone_number', 'student_answers.question_id')
            ->whereIn('student_answers.question_id',$question_id)
            ->get();
        foreach ($student_numbers as $number){
            if ($type == 1){
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. Solution of your test is uploaded';
            } else{
                $msg = 'Hey, '.$number['name'].' take a look of your "CA ONLINE TEST" account. Solution of your test is uploaded';
            }
            $push_data = array(
                'title' =>  $number['phone_number'],
                'body'  =>  $msg,
            );
            $this->CustomerPushNotification($push_data);
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\QuizAnswer  $quizAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(QuizAnswer $quizAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\QuizAnswer  $quizAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(QuizAnswer $quizAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\QuizAnswer  $quizAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QuizAnswer $quizAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\QuizAnswer  $quizAnswer
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuizAnswer $quizAnswer)
    {
        //
    }
    /* for ajax */
    public function display_result(Request $request){
        $quiz_id = $request->quiz_id;
        $result = $this->quiz_solution_model->where(array('question_id'=>$quiz_id))->get();
        ?>
            <div class="row">
                <?php
                if (count($result) > 0) {
                    foreach ($result as $images) {
                        ?>
                        <div class="col-md-4">
                            <img src="<?php echo url($images['quiz_solution']); ?>" width="100%">
                            <a href="<?php echo url($images['quiz_solution']); ?>" download="CAONLINETEST" class="btn btn-outline-primary">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                        <?php
                    }
                }else{
                    echo '<span class="text-center text-black-50">No record found</span>';
                }
                ?>
            </div>
        <?php
    }
}
