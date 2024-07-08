@extends('layouts.base')
@section('content')
    <div class="col-12 ">
        <div class="row m-0">
            <div class="col-4"></div>

            <div class="col-7 mt-5">
                @if (session()->has('message'))
                    <div class=" row col-6 m-2 alert alert-success alert-dismissible fade show" role="alert">
                        <div class="col-11">
                            <b>Hey {{ session()->get('fullname') }},</b>
                            <div>
                                Check Your Mailbox to Set the Password
                            </div>
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
                @if (session()->has('update'))
                    <div class=" row col-6 m-2 alert alert-success alert-dismissible fade show" role="alert">
                        <div class="col-11">
                            {{ session()->get('update') }}
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
                @if (session()->has('success'))
                    <div class=" row col-6 m-2 alert alert-success alert-dismissible fade show" role="alert">
                        <div class="col-11">
                            {{ session()->get('success') }}
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class=" row col-6 m-2 alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="col-11">
                            {{ session()->get('error') }}
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post" class="">
                    @csrf
                    <div class="col-6 ">
                        <h5 class="m-2">Login into your account</h5>
                        <input type="text" id="email" name="email"
                            class="form-control rounded-0 m-2 @if ($errors->has('email')) {{ 'is-invalid' }} @endif"
                            placeholder="Email Address">
                        @if ($errors->has('email'))
                            <div class="invalid-feedback m-2">{{ $errors->first('email') }}</div>
                        @endif
                        <input type="password" id="password" name="password"
                            class="form-control rounded-0 m-2 @if ($errors->has('password')) {{ 'is-invalid' }} @endif"
                            placeholder="Password">
                        @if ($errors->has('email'))
                            <div class="invalid-feedback m-2">{{ $errors->first('password') }}</div>
                        @endif
                        <input type="submit" value="Login"
                            class="form-control btn btn-primary text-dark submitbtn rounded-0 m-2">

                        <div class="col-5 my-3 mx-auto">
                            <a href="{{ route('showforgotpage') }}" class="">Forgot Password?</a>
                        </div>

                        <p class="center-text m-2"> Don't have an account ? <a href="{{ route('index') }}"
                                class="m-2">Register</a> </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
