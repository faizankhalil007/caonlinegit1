@extends('layouts.app')
@section('container_fluid')
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
                        <th>Question</th>
                        <th>Answer</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($quiz_data) > 0)
                        @foreach($quiz_data as $quiz)
                            <tr>
                                <td>
                                    {{ $quiz->title }}
                                </td>
                                <td>
                                    <a href="{{ route('answer_by_quiz_id',['quiz_id'=>$quiz->id]) }}" class="btn btn-circle btn-outline-primary">
                                        <i class="fa fa-check"></i>
                                    </a>
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