@extends('layouts.base')
@section('content')

<div class="d-flex justify-content-end align-items-center">
<div class="col-7">
    <h1 class="display-1">404</h1>
    <p class="h6">Page Not Found</p>
    <a class="btn btn-primary  rounded-pill" href="{{route('index')}}" >Back To Home</a>
</div>
</div>

@endsection
