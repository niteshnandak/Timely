@extends('layouts.base')
@section('content')
<div class="row justify-content-end">
    <div class="col-8 mt-4" method="post">
        <form action="{{route('save-password',['id'=>$id])}}" method="post">
            @csrf
            <div class="col-6 ">
                <h5 class="m-2">SET UP YOUR PASSWORD</h5>
                <input type="password" class="form-control rounded-0 m-2" placeholder="Enter Password" name="password" id="password" onkeypress="check_password()">
                @if($errors->has('password'))
                <b class="text-danger m-2">{{$errors->first('password')}}</b>
                @endif
                <b class="text-danger m-2" id="password_error" hidden></b>
                <input type="password" class="form-control rounded-0 m-2" placeholder="Confirm Password" name="password_confirmation" id="password_confirmation" onkeyup="check_password()" >
                <input type="submit" class="form-control btn btn-primary text-dark submitbtn rounded-0 m-2" id="password_submit" disabled>
            </div>
        </form>
    </div>
</div>

@endsection
