@extends('layouts.app')
@section('container_fluid')
    @php
        if ($form_type == 1){
            $id             = '';
            $course_name    = '';
            $course_code    = '';
            $class_id       = '';
            $url            = route('save_courses');
            $button_text    = 'Save';
            $heading        = 'Add Course';
        } else{
            $id             = $course_info->id;
            $course_name    = $course_info->name;
            $course_code    = $course_info->course_code;
            $class_id       = $course_info->class_id;
            $url            = route('update_courses');
            $button_text    = 'Update';
            $heading        = 'Update Course';
        }
    @endphp
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <hr>
    <form class="user" method="post" action="{{ $url }}">
        @csrf
        <div class="form-group @if($errors->has('name')) has-error has-feedback  @endif" >
            <label class="text-gray-800">Course Name</label>
            <input type="text" class="form-control form-control-user" id="name" name="name" value="@if(old('name')){{ old('name') }}@else{{ $course_name }}@endif" placeholder="Enter Course Name...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('name') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="text-gray-800">Course Code</label>
            <input type="text" class="form-control form-control-user" id="course_code" name="course_code" value="@if(old('course_code')){{ old('course_code') }}@else{{ $course_code }}@endif" placeholder="Enter Course Code...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('course_code') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="text-gray-800">Module</label>
            <select name="class_id" class="form-control custom-select" >
                <option value="">Select Class</option>
                @if(count($classes) > 0)
                    @foreach($classes as $class)
                        <option value="{{$class->id}}" @if(old('class_id') == $class->id) selected @elseif($class_id == $class->id) selected @endif>
                            {{ $class->name }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if($errors->has('class_id'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('class_id') }}</div>
            @endif
        </div>
        <input type="hidden" name="id" value="{{ $id }}">
        <button type="submit" class="btn btn-primary btn-user btn-block">
            {{ $button_text }}
        </button>
    </form>
@endsection