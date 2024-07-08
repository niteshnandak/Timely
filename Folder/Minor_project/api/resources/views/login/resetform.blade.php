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

                <form action="{{ route('reset',['id'=>$id]) }}" method="post" class="">

                    <div class="col-6 ">
                        <h5 class="m-2">Reset Password</h5>
                        @csrf
                        <input type="password" class="form-control rounded-0 m-2" placeholder="Enter Password" name="password" id="password" onkeyup="check_password()">
                        @if ($errors->has('password'))
                            <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                        @endif
                        <b class="text-danger m-2" id="password_error" hidden></b>
                        <input type="password" class="form-control rounded-0 m-2" placeholder="Confirm Password" name="password_confirmation" id="password_confirmation" onkeyup="check_password()" >
                        @if ($errors->has('password_confirmation'))
                            <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                        <input type="submit" id="password_submit" class="form-control btn btn-primary text-dark submitbtn rounded-0 m-2" disabled >
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
