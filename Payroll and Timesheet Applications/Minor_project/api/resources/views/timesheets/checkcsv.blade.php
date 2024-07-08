@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-lg-12" style="height: 5rem">
        <div class="row py-3 mb-4">
            <h3 class="text-dark-emphasis">No Of Rows: {{$no_of_rows}}</h3>
        </div>
    </div>
    <div class="col-lg-12 table-responsive my-4">
        <form action="{{ route('timesheetcsv.store',['timesheet_id'=>$timesheet_id]) }}" method="post" >
            @csrf
            @method('post')
            <input type="hidden" name="file_id" value="{{$file_id}}">
            <input class="btn btn-primary" type="submit" value="Submit">
            <span><a class="btn btn-dark" href="{{ route('dashboard') }}">Back</a></span>
        </form><br>
        <table class="table table-bordered">
            <thead class="table-dark">
                <th style="width: 7%">Worker Id</th>
                <th>Worker Name</th>
                <th>Date</th>
                <th>Organisation</th>
                <th>Hourly Pay</th>
                <th>Hours Worked</th>
            </thead>
            <tbody>
                @foreach ($files as $csv_data)
                    <tr>
                        <td>{{$csv_data['worker_id']}}</td>
                        <td>{{$csv_data['worker_name']}}</td>
                        <td>{{$csv_data['timesheet_detail_date']}}</td>
                        <td>{{$csv_data['organisation']}}</td>
                        <td>{{$csv_data['hourly_pay']}}</td>
                        <td>{{$csv_data['hours_worked']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{$files->links()}}
    </div>
</div>
@endsection
