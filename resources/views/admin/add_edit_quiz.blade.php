@extends('layouts.app')
@section('extend_css')
    <link href="{{ asset('asset/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    @inject('courses','App\Http\Controllers\CoursesController');
@endsection
@section('container_fluid')
    @php
        if ($form_type == 1){
            $id          = '';
            $quiz_title  = '';
            $class_id    = '';
            $course_id   = '';
            $quiz_time   = '';
            $questions   = array();
            $url         = route('save_quiz');
            $button_text = 'Save';
            $heading     = 'Add Quiz';
        } else{
            $id          = $quiz_info->id;
            $quiz_title  = $quiz_info->title;
            $class_id    = $quiz_info->class_id;
            $course_id   = $quiz_info->course_id;
            $quiz_time   = $quiz_info->quiz_time;
            $url         = route('update_quiz');
            $button_text = 'Update';
            $heading     = 'Update Quiz';
        }
    @endphp
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <hr>
    <form class="user" method="post" action="{{ $url }}">
        @csrf
        <div class="form-group @if($errors->has('title')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Quiz Title</label>
            <input required type="text" class="form-control " id="title" name="title" value="@if(old('title')){{ old('title') }}@else{{ $quiz_title }}@endif" placeholder="Enter Quiz Title...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('quiz_time')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Quiz Time</label>
            <input required type="text" class="form-control " id="quiz_time" name="quiz_time" value="@if(old('quiz_time')){{ old('quiz_time') }}@else{{ $quiz_time }}@endif" placeholder="Enter Quiz Time...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('quiz_time') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Class</label>
            <select name="class_id" class="form-control select2" required onchange="get_courses_by_class_id(this.value)">
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
        <div class="form-group @if($errors->has('course_code')) has-error has-feedback  @endif" >
            <label class="font-weight-bolder">Course</label>
            <select name="course_id" class=" select2 form-control" id="expand_courses" required>
                <option value="">Select Course</option>
                @if($class_id != '')
                    @php $courses_list = $courses->get_course_by_class_id($class_id); @endphp
                    @foreach($courses_list as $course)
                        <option value="{{ $course->id }}" @if($course->id == $course_id) selected @endif >{{ $course->name }}</option>
                    @endforeach
                @endif
            </select>
            @if($errors->has('course_id'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('course_id') }}</div>
            @endif
        </div>
        {{-- sections for adding questions --}}
        <button class="btn btn-info btn-sm float-right" onclick="add_text_area()" type="button">Add Question</button>
        <div class="row" id="textarea">
            @if(count($questions) > 0)
                @foreach($questions as $ques)
                    <div class="form-group col-md-9" >
                        <label class='font-weight-bolder'>Question</label>
                        <textarea class="form-control col-md-12" name="questions[]" required rows="5" style="resize: none">{{ $ques->question }}</textarea>
                           @if($errors->has('questions'))
                               <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('questions') }}</div>
                           @endif
                           <br>
                    </div>
                    <div class="form-group col-md-3" {{--id="marks"--}}>
                        <label class='font-weight-bolder'>Marks</label>
                        <input type="text" class="form-control col-md-12" name="marks[]" required value="{{ $ques->question_mark }}">
                        <input type="hidden" name="question_id[]" value="{{ $ques->id }}">
                    </div>
                @endforeach
            @else
                <div class="form-group col-md-9">
                    <label class='font-weight-bolder'>Question</label>
                    <textarea class="form-control col-md-12" name="questions[]" rows="5" required style="resize: none"></textarea>
                </div>
                <div class="form-group col-md-3" {{--id="marks"--}}>
                    <label class='font-weight-bolder'>Marks</label>
                    <input type="text" class="form-control col-md-12" name="marks[]" required >

                </div>
                @if($errors->has('questions'))
                    <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('questions') }}</div>
                @endif
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
    {{--<script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'questions[]' );
    </script>--}}
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
        $('textarea').ckeditor();
        // $('.textarea').ckeditor(); // if class is prefered.
    </script>
@endsection
@endsection
