@extends('layouts.app')
@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Quiz Solution</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz Solution</h6>
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
                <form action="{{ route('save-quiz-solution') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th width="10%">Question#</th>
                            <th width="80%">Question</th>
                            <th width="10%">Upload Solution</th>
                        </tr>
                        </thead>
                        <tbody>
                        <input type="hidden" name="quiz_id" value="{{ $quiz_id }}">
                        @foreach($questions as $question)
                            <tr>
                                <td width="10%">{{ $loop->iteration }}</td>
                                <td width="80%">
                                    {!! $question->question !!}
                                </td>
                                <td width="10%">
                                    <input type="file" name="answer_file{{ $question->id }}[]" multiple>
                                    <input type="hidden" name="question_id[]" value="{{ $question->id }}">

                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3">
                                <button class="btn btn-primary">
                                        <span class="icon text-white">
                                          <i class="fas fa-save"></i>
                                        </span>
                                    <span class="text">Submit Answer</span>
                                </button></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
@endsection