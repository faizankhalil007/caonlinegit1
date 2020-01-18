@extends('layouts.app')
@section('container_fluid')
    @inject('check_pass_fail','App\Http\Controllers\StudentQuizAnswerController')
    @inject('is_requested',''App\Http\Controllers\TestRequestController)
    @inject('is_attempted_quiz',''App\Http\Controllers\QuizController)
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Quiz</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz List</h6>
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
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Course Title</th>
                        <th>Total/Obtain Marks</th>
                        <th>Quiz Time</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($quiz_detail) > 0)
                        @foreach($quiz_detail as $quiz)
                            @php
                                $is_pass_fail      = $check_pass_fail->is_pass_or_fail($quiz->id);
                                $already_requested = $is_requested->test_result_check($quiz->id);
                                $is_attempted      = $is_attempted_quiz->is_attempted_or_not($quiz->id);
                            @endphp
                            <tr>
                                <td>{{ $quiz->title }}</td>
                                <td>
                                    {{ $quiz->course_name->name }}
                                </td>
                                <td>
                                    {{ $quiz->total_marks }}/{{ $is_pass_fail['obtain_marks'] }}

                                    @if($is_pass_fail['pass_fail'] == 0 && isset($is_pass_fail['pass_fail']))
                                        <span class="text-danger"><strong>(Fail)</strong></span>
                                    @elseif($is_pass_fail['pass_fail'] == 1 )
                                        <span class="text-success"><strong>(Pass)</strong></span>
                                    @else
                                        <span class="text-info"><strong>(Not Marked)</strong></span>
                                    @endif
                                </td>
                                <td>{{ $quiz->quiz_time }} Minutes</td>
                                <td>

                                    @if($is_pass_fail['pass_fail'] == 0 && isset($is_pass_fail['pass_fail']))
                                        @if($already_requested['is_accepted'] == 0)
                                            <span class="text-info">Already Applied</span>
                                        @elseif($already_requested['is_accepted'] == 3)
                                            <span class="text-danger">Request Rejected</span>
                                        @elseif($already_requested['is_accepted'] == 1)
                                            <span class="text-success">Request Accepted</span>
                                            <button class="btn btn-primary" onclick="open_confirm_popup({{ $quiz->id }})" >
                                                <i class="fas fa-pen-nib" title="Start Quiz"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-danger" onclick="again_test_request('{{ $quiz->id }}')">
                                                <i class="fa fa-arrow-circle-up" title="Again Quiz Request"></i>
                                            </button>
                                        @endif

                                    @endif
                                    @if($is_attempted == 1)
                                        @if($already_requested['is_accepted'] != 1 && !isset($already_requested['is_accepted']))
                                            <button class="btn btn-primary" onclick="open_confirm_popup({{ $quiz->id }})" >
                                                <i class="fas fa-pen-nib" title="Start Quiz"></i>
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn btn-info" onclick="again_test_request('{{ $quiz->id }}')">
                                            <i class="fa fa-comment" title="Again Quiz Request"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        No Record Found
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
