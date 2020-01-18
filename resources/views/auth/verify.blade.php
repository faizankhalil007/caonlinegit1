@extends('layouts.app')

@section('container_fluid')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                        {{ session()->forget(['success']) }}
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                        {{ session()->forget(['error']) }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">{{ __('Verify Your Mobile Number') }} </div>

                    <div class="card-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('A fresh verification code has been sent to your mobile number.') }}
                            </div>
                        @endif

                        {{ __('Before proceeding, please check your mobile for a verification code.') }}
                        {{ __('If you did not receive the mobile') }},
                        <form class="d-inline" method="POST" action="{{ route('send_verification_code') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                        </form>
                    </div>
                </div>
                <h1 class="h3 mb-2 text-center text-black-50 mt-5">Verification Code</h1>
                <hr>
                    <form class="user" method="post" action="{{ route('verify_mobile_code') }}">
                        @csrf
                        <div class="form-group">
                            <input type="text" class="form-control" name="verification_code" >
                        </div>
                        <button type="submit" class="btn btn-primary btn-user btn-block">Verify</button>
                    </form>
            </div>
        </div>
    </div>
@endsection
