{{-- @extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-lg-12 my-4">
            <form method="POST" action="{{ route('updateTimesheet', ['timesheet_id'=>$timesheet_id, 'id' => $id]) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="workerName" class="form-label">Worker Name</label>
                    <input type="text" class="form-control" id="workerName" name="worker_name" value="{{ $timesheet_details->worker_name }}">
                </div>
                <div class="mb-3">
                    <label for="workerId" class="form-label">Worker ID</label>
                    <input type="text" class="form-control" id="workerId" name="worker_id" value="{{ $timesheet_details->worker_id }}">
                </div>
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="timesheet_detail_date" value="{{ $timesheet_details->date }}">
                </div>
                <div class="mb-3">
                    <label for="organisation" class="form-label">Organisation</label>
                    <input type="text" class="form-control" id="organisation" name="organisation" value="{{ $timesheet_details->organisation }}">
                </div>
                <div class="mb-3">
                    <label for="hourlyPay" class="form-label">Hourly Pay</label>
                    <input type="number" class="form-control" id="hourlyPay" name="hourly_pay" value="{{ $timesheet_details->hourly_pay }}">
                </div>
                <div class="mb-3">
                    <label for="hoursWorked" class="form-label">Hours Worked</label>
                    <input type="number" class="form-control" id="hoursWorked" name="hours_worked" value="{{ $timesheet_details->hours_worked }}">
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
@endsection --}}



@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 my-4">
            <form method="POST" action="{{ route('updateTimesheet', ['timesheet_id'=>$timesheet_id, 'id' => $id]) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class = "col-6">
                        <div class="mb-3">
                            <label for="worker_name" class="form-label">Worker Name *</label>
                            <input type="text" class="form-control rounded-0" id="worker_name" name="worker_name" class="form-control @if($errors->has('worker_name')){{'is-invalid'}} @endif" value="{{ old('worker_name',$timesheet_details->worker_name) }}">
                            @if($errors->has('worker_name'))
                            <div class="invalid-feedback">{{$errors->first("worker_name")}}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label for="worker_id" class="form-label">Worker ID *</label>
                            <input type="text" class="form-control" id="worker_id" name="worker_id" class="form-control @if($errors->has('worker_id')){{'is-invalid'}} @endif" value="{{old ('worker_id', $timesheet_details->worker_id )}}">
                            @if($errors->has('worker_id'))
                            <div class="invalid-feedback">{{$errors->first("worker_id")}}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class = "row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="timesheet_detail_date" class="form-control @if($errors->has('timesheet_detail_date')){{'is-invalid'}} @endif" value="{{ old ('timesheet_detail_date', $timesheet_details->timesheet_detail_date) }}">
                            @if($errors->has('timesheet_detail_date'))
                            <div class="invalid-feedback">{{$errors->first("timesheet_detail_date")}}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="organisation" class="form-label">Organisation *</label>
                            <input type="text" class="form-control" id="organisation" name="organisation" class="form-control @if($errors->has('organisation')){{'is-invalid'}} @endif" value="{{ old('organisation', $timesheet_details->organisation) }}">
                            @if($errors->has('organisation'))
                            <div class="invalid-feedback">{{$errors->first("organisation")}}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="hourly_pay" class="form-label">Hourly Pay *</label>
                            <input type="text" class="form-control" id="hourly_pay" name="hourly_pay" class="form-control @if($errors->has('hourly_pay')){{'is-invalid'}} @endif" value="{{ old('hourly_pay',$timesheet_details->hourly_pay) }}">
                            @if($errors->has('hourly_pay'))
                            <div class="invalid-feedback">{{$errors->first("hourly_pay")}}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label for="hours_worked" class="form-label">Hours Worked *</label>
                            <input type="text" class="form-control" id="hours_worked" name="hours_worked" class="form-control @if($errors->has('hours_worked')){{'is-invalid'}} @endif" value="{{ old('hours_worked', $timesheet_details->hours_worked) }}">
                            @if($errors->has('hours_worked'))
                            <div class="invalid-feedback">{{$errors->first("hours_worked")}}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-4 text-end">
                    <button type="submit" class="btn btn-primary ml-2">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
