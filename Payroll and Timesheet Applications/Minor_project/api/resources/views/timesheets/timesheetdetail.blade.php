@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-lg-12" style="height: {{ $csv_flag == 1 ? '65px' : '50px' }}">
            <div class="d-flex justify-content-between {{ $csv_flag == 1 ? 'py-3' : 'py-4' }} ">
                <h5 class="text-dark-emphasis">{{$csv_flag == 1 ?'Uploaded From File':'Manual Timesheet'}}: {!! $timesheet_name !!}</h5>
                <div class="text-end">
                    @if($csv_flag == 0)
                        <button class="btn btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#addRowForm" aria-expanded="false" aria-controls="addRowForm">
                            <i class="bi bi-plus-circle"></i> Add Row
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="">
            @if($csv_flag == 0)
                <div class="col-lg-12 py-3">
                    <div class="collapse" id="addRowForm">
                        <br>
                        <div class="card card-body">
                            <form method="POST" action="{{ route('addRow', ['timesheet_id'=>$timesheet_id]) }}">
                                @csrf
                                <div class="container">
                                    <div class="row m-0">
                                        <div class="col-4">
                                            <div class="mb-3">
                                                <label for="worker_name" class="form-label">Worker Name *</label>
                                                <input type="text" class="form-control rounded-0 @if($errors->has('worker_name')){{'is-invalid'}} @endif" id="worker_name" name="worker_name" placeholder="Enter Worker Name">
                                                @if($errors->has('worker_name'))
                                                <div class="invalid-feedback">{{$errors->first("worker_name")}}</div>
                                                @endif
                                            </div>
                                        </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="worker_id" class="form-label">Worker ID *</label>
                                            <input type="text" class="form-control @if($errors->has('worker_id')){{'is-invalid'}} @endif" id="worker_id" name="worker_id" placeholder="Enter Worker ID">
                                            @if($errors->has('worker_id'))
                                            <div class="invalid-feedback">{{$errors->first("worker_id")}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Date *</label>
                                            <input type="date" class="form-control @if($errors->has('timesheet_detail_date')){{'is-invalid'}} @endif" id="date" name="timesheet_detail_date" placeholder="Enter Date">
                                            @if($errors->has('timesheet_detail_date'))
                                            <div class="invalid-feedback">{{$errors->first("timesheet_detail_date")}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="organisation" class="form-label">Organisation *</label>
                                            <input type="text" class="form-control @if($errors->has('organisation')){{'is-invalid'}} @endif" id="organisation" name="organisation" placeholder="Enter Organisation">
                                            @if($errors->has('organisation'))
                                            <div class="invalid-feedback">{{$errors->first("organisation")}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="hourly_pay" class="form-label">Hourly Pay *</label>
                                            <input type="text" class="form-control @if($errors->has('hourly_pay')){{'is-invalid'}} @endif" id="hourly_pay" name="hourly_pay" placeholder="Enter Hourly Pay">
                                            @if($errors->has('hourly_pay'))
                                            <div class="invalid-feedback">{{$errors->first("hourly_pay")}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="hours_worked" class="form-label">Hours Worked *</label>
                                            <input type="text" class="form-control @if($errors->has('hours_worked')){{'is-invalid'}} @endif" id="hours_worked" name="hours_worked" placeholder="Enter Hours Worked">
                                            @if($errors->has('hours_worked'))
                                            <div class="invalid-feedback">{{$errors->first("hours_worked")}}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-lg-12 table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <th style="width: 7%">S.No</th>
                    <th>Worker Name</th>
                    <th>Worker Id</th>
                    <th>Organisation</th>
                    <th>Hourly Pay</th>
                    <th>Hours Worked</th>
                    <th>Status</th>
                    <th width="width: 15%">Actions</th>
                </thead>
                <tbody>
                    @foreach ($timesheetDetails as $timesheetDetail)
                        <tr>
                            <td>{!! $loop->index+$timesheetDetails->firstItem() !!}</td>
                            <td>{{$timesheetDetail['worker_name']}}</td>
                            <td>{{$timesheetDetail['worker_id']}}</td>
                            <td>{{$timesheetDetail['organisation']}}</td>
                            <td class="text-end">£{{$timesheetDetail['hourly_pay']}}</td>
                            <td class="text-end">{{$timesheetDetail['hours_worked']}}</td>
                            <td>{{ $timesheetDetail['draft_status'] == 1 ? 'Draft':'Saved' }}</td>
                            <td>
                                @if($timesheetDetail['draft_status'] == 1)
                                    <a class="btn btn-dark text-white-emphasis btn-smaller" href="{{ route('editTimesheet', ['timesheet_id'=>$timesheet_id,'id' => $timesheetDetail['id']]) }}" data-toggle="tooltip" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <a class="btn btn-success btn-smaller" href=" {{route('draftUpdateTimesheet',['timesheet_id'=>$timesheet_id, 'id'=>$timesheetDetail['id']])}} " data-toggle="tooltip" title="Finalize"><i class="bi bi-check-square-fill"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm btn-smaller"
                                        data-bs-toggle="modal" data-bs-target="#deletepdfModal"
                                        title="Click to Delete Pdf"><i class="bi bi-trash"></i></button>

                                    <div class="modal fade" id="deletepdfModal" tabindex="-1"
                                        aria-labelledby="deletepdfModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Delete Timesheet entry</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                        Are you sure you want to Delete this Timesheet Entry?
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{route('deleteTimesheet',['timesheet_id'=>$timesheet_id, 'id'=>$timesheetDetail['id']])}} " method="post">
                                                    @csrf
                                                    @method('post')
                                                        <button type="submit" class="btn btn-danger">Yes</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    INVOICING
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{$timesheetDetails->links()}}
            <a class="btn btn-dark" href="{{ route('dashboard') }}"><i class="bi bi-arrow-return-left"></i> Back</a>
        </div>
    </div>
@endsection
