@extends('layouts.app')
@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Students List</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz Request List</h6>
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
                        <th>Student Name</th>
                        <th>Quiz</th>
                        <th>Request Time</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($test_requests) > 0)
                        @foreach($test_requests as $test)
                            <tr>
                                <td>{{ $test->student_name->name }}</td>
                                <td>
                                    {{ $test->quiz_title->title }}
                                </td>
                                <td>
                                    {{ $test->submit_time }}
                                </td>
                                <td>
                                    @if($test->is_accepted == 0)
                                        <button class="btn btn-outline-primary btn-circle" onclick="approve_cancel_test_request(1,'{{ $test->id }}')">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-circle" onclick="approve_cancel_test_request(3,'{{ $test->id }}')">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    @elseif($test->is_accepted == 1)
                                        <span class="text-dark">Approved</span>
                                    @elseif($test->is_accepted == 3)
                                        <span class="text-danger">
                                            Already Rejected
                                        </span>
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