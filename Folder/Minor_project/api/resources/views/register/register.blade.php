@extends('layouts.base')
@section('content')


<div class="row justify-content-end">
    <div class="col-lg-8 mt-5">
        <div class="row">
            <div class="col-lg-6">
                @if(session()->has('error'))
                <div class="col-12 alert alert-danger mx-2 my-4 ">
                    <div class="text-dark">{{ session()->get('error') }}</div>
                </div>
                @endif
                <form action="{{route('register')}}" method="post">
                    @csrf
                    <div class="col-7 mx-auto">
                        <h5 class="text-dark">Set Your Account</h5>
                    </div>
                    <input type="text" class="form-control text-dark rounded-0 m-2" placeholder="Enter First Name" name="first_name" id="first_name" onkeyup="keyup()" onkeypress="check_name(event)" >
                    @if($errors->has('first_name'))
                    <b class="text-danger m-2">{{$errors->first('first_name')}}</b>
                    @endif
                    <input type="text" class="form-control rounded-0 m-2" placeholder="Enter  Last Name" name="last_name" id="last_name" onkeyup="keyup()" onkeypress="check_name(event)">
                    @if($errors->has('last_name'))
                    <b class="text-danger m-2">{{$errors->first('last_name')}}</b>
                    @endif
                    <input type="text" class="form-control rounded-0 m-2" placeholder="Enter  Email Address" name="email" id="email" onkeyup="keyup()"  >
                    <b class="text-danger m-2" id="here" hidden></b>
                    @if($errors->has('email'))
                    <b class="text-danger m-2">{{$errors->first('email')}}</b>
                    @endif
                    <input type="text" class="form-control rounded-0 m-2" placeholder="Enter Phone Number" name="phone_number" id="phone_number" onkeyup="keyup()" onkeypress="check_phone_number(event)">
                    @if($errors->has('phone_number'))
                    <b class="text-danger m-2">{{$errors->first('phone_number')}}</b>
                    @endif
                    <div class="col-12 " id="btn-div">
                        <button type="submit" class="form-control btn btn-primary text-darl rounded-0 m-2" id="submit" name="submit" onmouseover="check_submit(event)" disabled>Submit </button>
                    </div>
                    <span class="m-2">Already Have An Account ? <a href="{{route('showlogin')}}" class="m-2">Login</a> </span>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
