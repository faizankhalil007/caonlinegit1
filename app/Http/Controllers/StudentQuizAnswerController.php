<?php

namespace App\Http\Controllers;

use App\StudentQuizAnswer;
use App\TestQuestionAnswerByStudent;
use Illuminate\Http\Request;
use App\Quiz;
use App\TestQuestions;
use App\Traits\CommonTraits;
use Chumper\Zipper\Facades\Zipper;
use App\User;
use function Sodium\add;

class StudentQuizAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $quiz_answer_model;
    protected $quiz_model;
    protected $question_model;
    protected $question_answer_model;
    protected $user_model;
    use CommonTraits;
    public function __construct()
    {
        $this->middleware('auth');
        $this->quiz_answer_model        = new StudentQuizAnswer();
        $this->quiz_model               = new Quiz();
        $this->question_model           = new TestQuestions();
        $this->question_answer_model    = new TestQuestionAnswerByStudent();
        $this->user_model               = new User();
    }

    public function index()
    {
        //
        $all_answers = $this->quiz_model->get();
        return view('admin/quiz_result',['quiz_data'=>$all_answers]);
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
        $request->validate([
            'answer_file.*'   => 'required|image|mimes:jpg,jpeg,png'
        ]);
        $image_name = array();
        $save_answer = array();
        $questions = $request->question_id;
        foreach ($request->question_id as $question){
        if ($request->hasFile('answer_file'.$question)) {
            $images = $request->file('answer_file'.$question);
                foreach ($images as $image){
                    $extension = $image->getClientOriginalExtension();
                    $filename = uniqid().'.'.$extension;
                    if (!file_exists('uploads/quiz_answer/'.date('Y'))){
                        mkdir('uploads/quiz_answer/'.date('Y'),0777,true);
                    }
                    if (!file_exists('uploads/quiz_answer/'.date('Y').'/'.date('m'))){
                        mkdir('uploads/quiz_answer/'.date('Y').'/'.date('m'),0777,true);
                    }
                    if (!file_exists('uploads/quiz_answer/'.date('Y').'/'.date('m').'/'.date('d'))){
                        mkdir('uploads/quiz_answer/'.date('Y').'/'.date('m').'/'.date('d'),0777,true);
                    }
                    $destinationPath = 'uploads/quiz_answer/'.date('Y').'/'.date('m').'/'.date('d');
                    $image->move($destinationPath, $filename);
                    $answer_data = array(
                        'answer'       => $destinationPath.'/'.$filename,
                        'quiz_id'      => $request->quiz_id,
                        'student_id'   => auth()->user()->id,
                        'question_id'  => $question,
                        'submitted_at' => date('d-m-Y h:i:s a'),
                    );
                    $save_answer[] = $this->question_answer_model->insert($answer_data);
                }
            }
        }
        if ($save_answer){
            $request->session()->put('success','Thanks for your answer, result will be uploaded soon');
            return redirect('student-quiz-list');
        }else{
            $request->session()->put('error','Answer is not submitted please try again');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StudentQuizAnswer  $studentQuizAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
        $quiz_id = $request->quiz_id;
        $answers_list = $this->question_answer_model->where(array('quiz_id'=>$request->quiz_id))->paginate(15);

        return view('admin/student_answers_list',['answers_list'=>$answers_list,'quiz_id'=>$quiz_id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StudentQuizAnswer  $studentQuizAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentQuizAnswer $studentQuizAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StudentQuizAnswer  $studentQuizAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentQuizAnswer $studentQuizAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StudentQuizAnswer  $studentQuizAnswer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentQuizAnswer $studentQuizAnswer)
    {
        //
    }
    /*
        start customer function to start quiz of student
    */
    public function check_quiz_request_status($quiz_id,$student_id){

    }
    public function start_quiz(Request $request){
        $check_expiry_time = $this->quiz_answer_model->where(array('quiz_id'=>$request->quiz_id,'student_id'=>auth()->user()->id))->first();
        $questions = $this->quiz_model->where(array('id'=>$request->quiz_id))->first();
        $quiz_request = $this->check_quiz_request_status($request->quiz_id,auth()->user()->id);
        if ($check_expiry_time){
            if ($check_expiry_time->end_time < strtotime('now')){

                $questions_data = $this->question_model->where(array('quiz_id'=>$request->quiz_id))->get();
                return view('student/quiz_questions_list',['question_data'=>$questions_data,'quiz_id'=>$request->quiz_id,'is_eligible'=>0]);
            } else{
                $questions_data = $this->question_model->where(array('quiz_id'=>$request->quiz_id))->get();
                return view('student/quiz_questions_list',['question_data'=>$questions_data,'quiz_id'=>$request->quiz_id,'is_eligible'=>1]);
            }
        } else{
            $data = array(
                'student_id'    => auth()->user()->id,
                'quiz_id'       => $request->quiz_id,
                'class_id'      => $questions->class_id,
                'end_time'      => strtotime('+'.$questions->quiz_time.' minutes'),
                'start_time'    => date('d-m-Y h:i:s a'),
            );
            $start_quiz = $this->quiz_answer_model->insert($data);
            if ($start_quiz){
                if (empty($questions)){
                    $questions1 = $this->quiz_model->where(array('id'=>$request->quiz_id))->first();
                } else{
                    $questions1 = $this->quiz_model->where(array('id'=>$request->quiz_id))->first();
                }
                $questions_data = $this->question_model->where(array('quiz_id'=>$request->quiz_id))->get();
                return view('student/quiz_questions_list',['question_data'=>$questions_data,'quiz_id'=>$request->quiz_id,'is_eligible'=>1]);
            } else{
                $request->session()->put('error','something went wrong please try again');
                return back();
            }
        }
    }
    /* open popup to show images of answers */
    public function display_answers(Request $request){
        $answer_images = $this->question_answer_model->where(array('student_id'=>$request->student_id,'question_id'=>$request->question_id))->get();
        ?>
        <div class="row">
            <?php
            foreach ($answer_images as $image) {
                ?>
                <div class="col-md-6">
                    <img src="<?php echo url($image['answer']); ?>" style="width: 100%">
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    /* open popup to add numbers and remarks */
    public function add_quiz_marks_and_review(Request $request){
        $mark   = '';
        $remark = '';
        $already_given_mark = $this->question_answer_model->where(array('student_id'=>$request->student_id,'question_id'=>$request->question_id))->first();
//        print_r($already_given_mark); exit;
        $marks = $already_given_mark['question_mark'];
        $remarks = $already_given_mark['teacher_remark'];
        $quiz_id = $this->question_model->where(array('id'=>$request->question_id))->first()->quiz_id;
        ?>
        <form action="<?php echo route('save_quiz_marks') ?>" method="post">
            <div class="form-group col-md-12">
                <label for="marks">Marks</label>
                <input type="text" class="form-control" name="marks" required value="<?=$marks?>">
            </div>
            <div class="form-group col-md-12">
                <label>Remarks</label>
                <textarea name="remark" class="form-control " rows="5"  style="resize: none" required><?=$remarks?></textarea>
            </div>
            <input type="hidden" name="student_id" value="<?php echo $request->student_id; ?>">
            <input type="hidden" name="question_id" value="<?php echo $request->question_id; ?>">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="_token" id="csrf-token" value="<?php echo csrf_token() ?>" />
            <?php
            if (!isset($already_given_mark['teacher_remark'])){
                ?>
                <button type="submit" class="btn btn-outline-primary">Save Marks</button>
                <?php
            }
            ?>
        </form>
        <?php
    }
    public function save_quiz_marks_and_remarks(Request $request){
        $marks = $request->marks;
        $remark = $request->remark;
        $student_data = $this->user_model->where(array('id'=>$request->student_id))->first();
        $data = array(
            'teacher_remark'  => $remark,
            'question_mark'   =>  $marks,
            'updated_at'      =>  date('d-m-Y H:i:s a')
        );
        $sms_data = array(
            'title' => $student_data->phone_number,
            'body'  => 'Hey, '.$student_data->name.' check your "CA ONLINE TEST" account. Your test result is upload.'
        );
        $update_data = $this->question_answer_model->where(array('student_id'=>$request->student_id,'question_id'=>$request->question_id))->update($data);
        if ($update_data){
            $this->is_pass_or_fail1($request->student_id,$request->quiz_id,$marks);
            $this->CustomerPushNotification($sms_data);
            $request->session()->put('success','Student data updated successfully');
            return back();
        }else{
            $request->session()->put('errror','please try again');
            return back();
        }
    }
    public function get_answer_count($question_id){
        $total_count = $this->question_answer_model->where(array('student_id'=>auth()->user()->id,'question_id'=>$question_id))->count();
        return $total_count;
    }
    public function quiz_result(Request $request){
        $question_id = $request->question_id;
        $student_id  = auth()->user()->id;
        $quiz_remarks = $this->question_answer_model->where(array('question_id'=>$question_id,'student_id'=>$student_id))->groupBy('question_id','student_id')->get();
        ?>
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>Remarks</th>
                        <th>Marks</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($quiz_remarks as $marks) {
                ?>
                        <tr>
                            <td><?= $marks->teacher_remark ?></td>
                            <td><?= $marks->question_mark ?></td>
                        </tr>
                <?php
                    }
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    public function is_pass_or_fail($quiz_id){
        $student_id = auth()->user()->id;
        $result = 0;
        $quiz_count = $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->count();
        if ($quiz_count > 0){
            $result = $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->first();
            return $result;
        }
//        $result = $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->first();
        return $result;
    }
    public function is_pass_or_fail1($student_id,$quiz_id,$marks){
        $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->increment('obtain_marks',$marks);
        $total_quiz_marks = $this->quiz_model->where(array('id'=>$quiz_id))->first()->total_marks;
        $obtain_percentage = ($marks/$total_quiz_marks)*100;
        if ($obtain_percentage >= 50){
            $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->update(array('pass_fail'=>1));
        } else{
            $this->quiz_answer_model->where(array('student_id'=>$student_id,'quiz_id'=>$quiz_id))->update(array('pass_fail'=>0));
        }
        return true;
    }

    /* download student answers */
    public function downloadAnswerImage(Request $request){
        $student_id  = $request->student_id;
        $question_id = $request->question_id;
        $answer_images = $this->question_answer_model->where(array('student_id'=>$request->student_id,'question_id'=>$request->question_id))->get();
        $images = array();
        $folder_name = 'public/student_'.$student_id.'_question_'.$question_id.'.zip';
        Zipper::make($folder_name);
        foreach ($answer_images as $image) {
            $images[] = Zipper::add($image['answer']);
            'uploads/quiz_answer/'.date('Y').'/'.date('m').'/'.date('d');
            /*$image_name = explode('/',$image['answer']);
            unlink($image_name);*/
//            unlink($image['answer']);
        }
        Zipper::close();
        return response()->download($folder_name)->deleteFileAfterSend(true);;
    }
    /* for student search*/
    public function search_student(Request $request){
        $answers_list = array();
        $student_name = $request->student_name;
        $quiz_id = $request->quiz_id;
        $get_student_id = $this->user_model->where('name','like','%'.$student_name.'%')->get();
        if (count($get_student_id) > 0){
            $answers_list = $this->question_answer_model->whereIn('student_id',$get_student_id)->where(array('quiz_id'=>$quiz_id))->paginate(15);
        }
        return view('admin/student_answers_list',['answers_list'=>$answers_list,'quiz_id'=>$quiz_id]);
    }
}
