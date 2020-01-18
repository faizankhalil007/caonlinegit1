@extends('layouts.app')
@section('container_fluid')
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Students List</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quiz List</h6>
        </div>
        <div class="card-body">
            <form class="user" method="post" action="{{ route('students_list') }}">
                @csrf
                <div class="form-group col-md-8 float-left">
                    <label class='font-weight-bolder'>Student Name</label>
                    <input type="text" class="form-control" name="student_name" value="@if(old('student_name')){{ old('student_name') }}@endif">
                </div>
                <div class="col-md-3 form-group float-left mt-4">
                    {{--                    <label class='font-weight-bolder'>Search</label>--}}
                    <button type="submit" value="Search Student" class="btn btn-circle btn-outline-info">
                        <i class="fa fa-search"></i>
                    </button>
                    <a href="{{ route('students_list') }}" class="btn btn-circle btn-outline-info">
                        <i class="fa fa-arrow-alt-circle-left"></i>
                    </a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($students_list) > 0)
                        @foreach($students_list as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>
                                    {{ $student->email }}
                                </td>
                                <td>
                                    {{ $student->phone_number }}
                                </td>
                                <td>
                                    <form action="{{ route('delete_student',['id'=>$student->id]) }}" method="post" style="display: inline;">
                                        @csrf
                                        <button class="btn btn-circle btn-outline-danger" type="submit">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    <a class="btn btn-circle btn-outline-primary" href="{{ route('edit_student',['id'=>$student->id]) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a class="btn btn-circle btn-outline-info" href="{{ route('student_detail',['id'=>$student->id]) }}">
                                        <i class="fa fa-eye"></i>
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
                        {{ $students_list->links() }}
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
