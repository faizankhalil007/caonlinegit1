@extends('layouts.app')
@section('container_fluid')
    <!-- Page Heading -->
    @inject('course_name','App\Http\Controllers\TestScheduleController')
    <h1 class="h3 mb-2 text-gray-800">Course Schedule</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course Schedule List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th width="20%">Title</th>
                        <th width="40%">Message</th>
                        <th width="10%">Course</th>
                        <th width="10%">Date</th>
                        <th width="5%">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($schedule_list) > 0)
                        @foreach($schedule_list as $schedule)
                            <tr>
                                <td width="20%">{{ $schedule->title }}</td>
                                <td width="40%">{{ $schedule->message }}</td>
                                <td width="10%">{{ $schedule->course_name->name }}</td>
                                <td width="10%">{{ $schedule->test_date }}</td>
                                <td width="10%">
                                    <a href="{{ route('edit_schedule',['id'=>$schedule->id]) }}" class="btn btn-circle btn-outline-info">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="post" action="{{ route('delete_schedule') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $schedule->id }}">
                                        <button class="btn btn-circle btn-outline-danger" type="submit">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
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