@extends('layouts.app')
@section('container_fluid')
    @php
        if ($form_type == 1){
            $id              = '';
            $student_name    = '';
            $student_email   = '';
            $student_phone   = '';
            //$student_session = '';
            $password        = '';
            $url             = route('save_student');
            $button_text     = 'Save';
            $heading         = 'Add Student';
        } else{
            $id              = $student_detail->id;
            $student_name    = $student_detail->name;
            $student_email   = $student_detail->email;
            $student_phone   = $student_detail->phone_number;
            //$student_session = $student_detail->student_session;
            $password        = '';
            $url             = route('update_student');
            $button_text     = 'Update';
            $heading         = 'Update Student';
        }
    @endphp
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <form class="user" method="post" action="{{ $url }}">
        @csrf
        <div class="form-group @if($errors->has('name')) has-error has-feedback  @endif" >
            <label>Student Name</label>
            <input type="text" class="form-control form-control-user" id="name" name="name" value="@if(old('name')){{ old('name') }}@else{{ $student_name }}@endif" placeholder="Enter Name...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('name') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('email')) has-error has-feedback  @endif" >
            <label>Student Email</label>
            <input type="text" class="form-control form-control-user" id="email" name="email" value="@if(old('email')){{ old('email') }}@else{{ $student_email }}@endif" placeholder="Enter Email...">
            @if($errors->has('email'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('email') }}</div>
            @endif
        </div>
        <div class="form-group @if($errors->has('student_session')) has-error has-feedback  @endif" >
            <label>Student Session</label>
            <select name="class_id" class="form-control custom-select" >
                @for($i = date('Y'); $i < date('Y') + 10; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="form-group @if($errors->has('phone_number')) has-error has-feedback  @endif" >
            <label>Phone Number</label>
            <input type="text"  name="phone_number" id="phone_number" value="@if(old('phone_number')){{ old('phone_number') }}@else{{ $student_phone }}@endif">
            @if($errors->has('phone_number'))
                <div class="invalid-feedback" style="font-size: 15px;display:block;">{{ $errors->first('phone_number') }}</div>
            @endif
        </div>
        <div class="form-group " >
            <label>Password</label>
            <input type="text"  name="password" id="password" value="@if(old('password')){{ old('password') }}@endif">
            <input type="hidden" name="old_password" id="old_password" value="{{ $password }}">
        </div>

        <input type="hidden" name="id" value="{{ $id }}">
        <button type="submit" class="btn btn-primary btn-user btn-block">
            {{ $button_text }}
        </button>
    </form>
@endsection
