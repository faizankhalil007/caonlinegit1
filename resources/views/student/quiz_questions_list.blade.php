@extends('layouts.app')
@section('container_fluid')
    @inject('answer_count','App\Http\Controllers\StudentQuizAnswerController');
    @inject('is_requested',''App\Http\Controllers\TestRequestController)
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Exam Start</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz Questions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                        {{ session()->forget(['success']) }}
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                        {{ session()->forget(['error']) }}
                    </div>
                @endif
                    <form action="{{ route('submit-quiz-answer') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th width="10%">Question#</th>
                                <th width="70%">Question</th>
                                <th width="20%">Answer</th>
                            </tr>
                            </thead>
                            @if($question_data)
                                <tbody>
                                <input type="hidden" name="quiz_id" value="{{ $quiz_id }}">
                                @foreach($question_data as $quiz)
                                    @php
                                        $already_requested = $is_requested->test_result_check($quiz->quiz_id);
                                    @endphp
                                    <tr>
                                        <td width="10%">{{ $loop->iteration }}</td>
                                        <td width="70%">
                                            {!! $quiz->question !!}
                                            {{--                                            {{ $quiz->question }}--}}
                                        </td>
                                        <td width="20%">
                                            @if($answer_count->get_answer_count($quiz->id) > 0)
                                                @if($already_requested == 1 && $answer_count->get_answer_count($quiz->id) < 0)
                                                    <input type="file" name="answer_file{{ $quiz->id }}[]" multiple>
                                                    <input type="hidden" name="question_id[]" value="{{ $quiz->id }}">
                                                @else
                                                    <button type="button" class="btn btn-outline-primary d-inline" title="Check Result" onclick="open_quiz_marks_suggesstion({{ $quiz->id }})">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary d-inline" title="Check Solution" onclick="open_solution({{ $quiz->id }})">
                                                        <i class="fa fa-book-open"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <input type="file" name="answer_file{{ $quiz->id }}[]" multiple>
                                                <input type="hidden" name="question_id[]" value="{{ $quiz->id }}">
                                                @if($errors->has('answer_file'))
                                                    <div class="invalid-feedback" style="display:block;font-size: 15px;">{{ $errors->first('answer_file') }}</div>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if($is_eligible == 1 || $already_requested == 1 && $answer_count->get_answer_count($quiz->id) < 0)
                                    <tr>
                                        <td colspan="3">
                                            <button class="btn btn-primary">
                                                <span class="icon text-white-50">
                                                  <i class="fas fa-save"></i>
                                                </span>
                                                <span class="text">Submit Answer</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @else
                                        @if($answer_count->get_answer_count($quiz->id) < 0)
                                            <tr>
                                                <td colspan="3">
                                                    <span class="text-danger">
                                                        <strong>Note</strong>
                                                        Submit answer button is disabled for you, It seems like you viewed question but didn't submit in given time <br/>
                                                        or 24 hours has been passed. If you want to submit answer, make a request by clicking button:
                                                    </span>
                                                    <button type="button" class="btn btn-info" onclick="return again_test_request('{{ $quiz_id }}')">
                                                        <i class="fa fa-comment" title="Again Quiz Request"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                @endif
                                </tbody>
                            @endif
                        </table>
                    </form>
            </div>
        </div>
    </div>
@endsection
