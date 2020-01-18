@extends('layouts.app')
@section('container_fluid')
    @inject('course_name','App\Http\Controllers\StudentCourseRegistrationController')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Classes</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Class List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Courses Name</th>
                        <th>Reg Date</th>
                        <th>End Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($course_info) > 0)
                        @foreach($course_info as $course)
                            <tr>
                                <td>{{ $course->class_name->name }}</td>
                                <td>
                                    @php
                                    $course_ids = $course->course_id;
                                    $course_ids = explode(',',$course_ids);
                                    $name = '';
                                    foreach ($course_ids as $id){
                                        $name .= $course_name->get_course_name_by_id($id).' - ';
                                    }
                                    echo $name;
                                    @endphp
                                </td>
                                <td>{{ $course->registration_date }}</td>
                                <td>{{ $course->expiry_date }}</td>
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