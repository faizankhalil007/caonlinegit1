@extends('layouts.app')
@section('container_fluid')
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
                        <th>Courses</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($classes_data) > 0)
                        @foreach($classes_data as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>
                                    <a href="{{ route('courses-by-module',['class_id'=>$class->id]) }}" class="btn btn-circle btn-outline-info">
                                        <i class="fa fa-book-open"></i>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('edit_class',['id'=>$class->id]) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="post" action="{{ route('delete_class') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $class->id }}">
                                        <button type="submit" class="btn btn-circle btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('update_class',['id'=>$class->id]) }}">

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