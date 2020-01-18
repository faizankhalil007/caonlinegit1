@extends('layouts.app')
@section('extend_css')
    <link href="{{ asset('asset/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endsection
@section('container_fluid')

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Select Class And Course</h1>
    <hr>
    <form class="user" method="post" action="{{ route('save_registration_info') }}">
        @csrf
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Class</label>
            <select name="class_id" class="form-control" required onchange="get_courses_by_class_id(this.value)">
                <option value="">Select Class</option>
                @if(count($classes_data) > 0)
                    @foreach($classes_data as $class)
                        <option value="{{$class->id}}" >
                            {{ $class->name }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if($errors->has('class_id'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('class_id') }}</div>
            @endif
        </div>

        <div class="form-group " >
            {{-- data expend here --}}
            <label class="font-weight-bolder">Courses</label>
            <select class="select2 form-control" multiple="multiple"  id="expand_courses" name="course_id[]" >
                {{-- options expand here--}}
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-user btn-block">
            Register
        </button>
    </form>
    @section('js-extend')
        <script type="text/javascript" src="{{ asset('asset/select2/dist/js/select2.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('asset/select2/dist/js/select2.init.js') }}"></script>

    @endsection
@endsection