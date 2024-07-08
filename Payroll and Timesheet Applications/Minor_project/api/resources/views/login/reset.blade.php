@extends('layouts.base')
@section('content')
    <div class="col-12 ">
        <div class="row m-0">
            <div class="col-4"></div>
            <div class="col-7 mt-5">
                @if (session()->has('success'))
                    <div class="col-6">
                        <div class="alert alert-success">{{ session('success') }}</div>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="col-6">
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    </div>
                @endif
                <form action="{{ route('forgot') }}" method="post" class="">
                    @csrf
                    <div class="col-6 ">
                        <h5 class="m-2">Reset your Password</h5>
                        <p class="center-text m-2"> Don't worry ! Just enter your email address and we'll send you a link to
                            reset your password </p>
                        <input type="text" id="email" name="email"
                            class="form-control rounded-0 m-2 @if ($errors->has('email')) {{ 'is-invalid' }} @endif"
                            placeholder="Email Address">
                        @if ($errors->has('email'))
                            <div class="invalid-feedback m-2">{{ $errors->first('email') }}</div>
                        @endif
                        <input type="submit" value="Submit"
                            class="form-control btn btn-primary text-dark submitbtn rounded-0 m-2">
                        <div class="col-4 mx-auto m-3">
                            <p class="center-text m-2"> Back to <a href="{{ route('showlogin') }}" class="">Login</a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
