@extends('layouts.app')@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Quiz</h1>
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Course</th>
                        <th>Question</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($quiz_detail) > 0)
                        @foreach($quiz_detail as $quiz)
                            <tr>
                                <td>{{ $quiz->title }}</td>
                                <td>
                                    {{ $quiz->class_name->name }}
                                </td>
                                <td>
                                </td>
                                <td>
                                    <a href="{{ route('add-quiz-solution',['quiz_id'=>$quiz->id]) }}" class="btn btn-outline-primary">
                                        <i class="fa fa-upload"></i>
                                    </a>
                                    <form action="{{ route('delete_quiz',['id'=>$quiz->id]) }}" method="post" style="display: inline-block">
                                        @csrf
                                        <button class="btn btn-circle btn-outline-danger" type="submit">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a class="btn btn-circle btn-outline-info" href="{{ route('edit_quiz',['id'=>$quiz->id]) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button class="btn btn-circle btn-outline-primary" onclick="open_question_popup({{ $quiz->id }})">
                                        <i class="fas fa-eye "></i>
                                    </button>
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
