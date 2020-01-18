@extends('layouts.app')
@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Classes</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Course Code</th>
                        <th>Class Name</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($courses_data) > 0)
                        @foreach($courses_data as $course)
                            <tr>
                                <td>{{ $course->name }}</td>
                                <td>
                                    {{ $course->course_code }}
                                </td>
                                <td>
                                    {{ $course->class_name->name }}
                                </td>
                                <td>
                                    <a href="{{ route('edit_course',['id'=>$course->id]) }}" class="btn btn-circle btn-outline-info">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form class="d-inline" action="{{ route('delete_course') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $course->id }}">
                                       <button type="submit" class="btn btn-circle btn-outline-danger">
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