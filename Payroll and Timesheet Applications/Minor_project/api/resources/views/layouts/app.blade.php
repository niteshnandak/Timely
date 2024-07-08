<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timely</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
</head>


<body>
    {{-- <div class="col-12 ">
        <div class="navbar">
            <div class="col-11">
                <div class="row">
                    <div class="col-2 d-flex">
                        <p class="text-dark  ms-2 my-auto logo">INTERN</p><b><span class="xlogo">X</span></b>
                        <p class="text-dark  my-auto logo">SHEETS</p>
                    </div>
                    <div class="col-1 my-auto">
                        <a class="text-decoration-none text-white " aria-current="page" href="{{ route('dashboard') }}">Timesheet</a>
                    </div>
                    <div class="col-1 my-auto">
                     <a class="text-decoration-none text-white" href="{{ route('invoice.index') }}">Invoice</a>
                    </div>
                </div>


            </div>
            <div class="col-1">
                <a class="text-white text-decoration-none " href="{{route('logout')}}">Logout</a>
            </div> --}}
            <!-- <div class="col-1">
                <button class="navbar-toggler bg-white " type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div> -->


        </div>
    </div>
    <!-- <div class="collapse navbar-collapse navbar p-2 " id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link text-white active" aria-current="page" href="{{ route('dashboard') }}">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('invoice.index') }}">Invoice</a>
            </li>
        </ul>
    </div> -->

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <div class="navbar-brand text-white">
                <img src="{{ asset('/logo/Timely1.png') }}" width="120">
            </div>
            <ul class="navbar-nav  me-5">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" aria-current="page" href="{{ route('dashboard') }}">TIMESHEET</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" href="{{ route('invoice.index') }}">INVOICE</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" href="{{route('logout')}}">LOGOUT</a>
                </li>
            </ul>
        </div>
    </nav>


    <div class="container-fluid m-0">
        <!-- Add Your Contents Here -->
        @yield('content')
    </div>

    <script src="{{ asset('/js/index.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>


</html>
