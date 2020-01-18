@extends('layouts.app')
@section('container_fluid')
    @php
        if ($form_type == 1){
            $id             = '';
            $name           = '';
            $url            = route('save_class');
            $button_text    = 'Save';
            $heading        = 'Add Class';
        } else{
            $id             = $class_data->id;
            $name           = $class_data->name;
            $url            = route('update_class');
            $button_text    = 'Update';
            $heading        = 'Update Class';
        }
    @endphp
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ $heading }}</h1>
    <form class="user" method="post" action="{{ $url }}">
        @csrf
        <div class="form-group @if($errors->has('name')) has-error has-feedback  @endif" >
            <input type="text" class="form-control form-control-user" id="name" name="name" value="@if(old('name')){{ old('name') }}@else{{ $name }}@endif" placeholder="Enter Class Name...">
            @if($errors->has('name'))
                <div class="invalid-feedback" style="display:block;font-size: 15px;">{{ $errors->first('name') }}</div>
            @endif
            <input type="hidden" name="id" value="{{ $id }}">
        </div>
        <button type="submit" class="btn btn-primary btn-user btn-block">
            {{ $button_text }}
        </button>
    </form>
@endsection