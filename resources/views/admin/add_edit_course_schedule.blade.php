@extends('layouts.app')
@section('extend_css')
    <link href="{{ asset('asset/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endsection
@section('container_fluid')
    @php
        if ($form_type == 1){
            $id         = '';
            $title      = '';
            $message    = '';
            $course_id  = '';
            $test_date  = '';
            $url            = route('save_schedule');
            $button_text    = 'Save';
            $heading        = 'Add Test Schedule';
        } else{
            $id          = $data->id;
            $title       = $data->title;
            $message     = $data->message;
            $course_id   = $data->course_id;
            $test_date   = $data->test_date;
            $url         = route('update_schedule');
            $button_text = 'Update';
            $heading     = 'Update Test Schedule';
        }
    @endphp
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <hr>
    <form class="user" method="post" action="{{ $url }}">
        @csrf
        @if($errors->has('id'))
            <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('id') }}</div>
        @endif
        <div class="form-group @if($errors->has('title')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Topic/Title</label>
            <input  type="text" class="form-control " id="title" name="title" value="@if(old('title')){{ old('title') }}@else{{ $title }}@endif" placeholder="Enter Topic...">
            @if($errors->has('title'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('quiz_time')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Test Date</label>
            <input  type="date" class="form-control " id="test_date" name="test_date" value="@if(old('test_date')){{ old('test_date') }}@else{{ $test_date }}@endif" >
            @if($errors->has('test_date'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('test_date') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Course</label>
            <select name="course_id" class="select2 form-control" >
                <option value="">Select Course</option>
                @if(count($courses) > 0)
                    @foreach($courses as $info)
                        <option value="{{$info->id}}" @if(old('course_id') == $info->id) selected @elseif($course_id == $info->id) selected @endif>
                            {{ $info->name }}
                        </option>
                    @endforeach
                @endif
            </select>
            @if($errors->has('course_id'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('course_id') }}</div>
            @endif
        </div>
        {{-- sections for adding questions --}}
        <div class="form-group" id="textarea">
            <label class="font-weight-bolder">Message</label>
            <textarea class="form-group col-md-12" name="message"  rows="5" style="resize: none">{{ $message }}</textarea>
            @if($errors->has('message'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('message') }}</div>
            @endif
        </div>

        {{-- sections for adding questions --}}
        <input type="hidden" name="id" value="{{ $id }}">
        <button type="submit" class="btn btn-primary btn-user btn-block">
            {{ $button_text }}
        </button>
    </form>
@section('js-extend')
    <script type="text/javascript" src="{{ asset('asset/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('asset/select2/dist/js/select2.init.js') }}"></script>

@endsection
@endsection