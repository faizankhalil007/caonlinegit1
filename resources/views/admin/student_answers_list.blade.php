@extends('layouts.app')@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Quiz Answers</h1>
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary text-center">Quiz Answers List</h6>
        </div>
        <div class="card-body">
            <form class="user" method="post" action="{{ route('search-student') }}">
                @csrf
                <div class="form-group col-md-8 float-left">
                    <label class='font-weight-bolder'>Student Name</label>
                    <input type="text" class="form-control" name="student_name">
                </div>
                <div class="col-md-3 form-group float-left mt-4">
{{--                    <label class='font-weight-bolder'>Search</label>--}}
                    <input type="hidden" class="form-control" name="quiz_id" value="{{ $quiz_id }}">
                    <button type="submit" value="Search Student" class="btn btn-circle btn-outline-info">
                        <i class="fa fa-search"></i>
                    </button>
                    <a href="{{ route('answer_by_quiz_id',['quiz_id'=>$quiz_id]) }}" class="btn btn-circle btn-outline-info">
                        <i class="fa fa-arrow-alt-circle-left"></i>
                    </a>
                </div>
            </form>
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
                            <th width="55%">Question</th>
                            <th width="10%">Question Marks</th>
                            <th width="15%">Student</th>
                            <th width="20%">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($answers_list) > 0)
                            @foreach($answers_list as $answer)
                                <tr>
                                    <td>
                                        {!! $answer->question_name->question !!}
                                    </td>
                                    <td>
                                        {{ $answer->question_name->question_mark }}
                                    </td>
                                    <td>
                                        @if(isset($answer->student_name->name))
                                            {{ $answer->student_name->name }}
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-circle btn-outline-primary" alt="View Answer" onclick="view_answer('{{ $answer->question_id }}','{{ $answer->student_id }}')">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-circle btn-outline-primary" alt="Add Marks" onclick="add_marks_and_review('{{ $answer->question_id }}','{{ $answer->student_id }}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a class="btn btn-circle btn-outline-primary" title="Download Answer" href="{{ route('download-answer-by-quiz',['question_id'=>$answer->question_id,'student_id'=>$answer->student_id]) }}">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            No Record Found
                        @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            {{ $answers_list->links() }}
                        </div>
                        <div class="col-md-2"></div>
                    </div>
            </div>
        </div>
    </div>
@endsection
