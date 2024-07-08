@extends('layouts.app')
@section('content')
<div class="container">
    <div class="border border-2 p-3 my-4">
        <div class="d-flex justify-content-between">
            <h5></i> Create/Upload Timesheet</h5>
            <div class="d-flex">
                <a class="btn btn-success me-3" data-bs-toggle="collapse" href="#manual_creation" role="button" aria-expanded="false" aria-controls="manual_creation"><i class="bi bi-plus-circle"></i> Create Timesheet</a>
                <a class="btn btn-primary" data-bs-toggle="collapse" href="#upload_csv" role="button" aria-expanded="false" aria-controls="upload_csv"><i class="bi bi-upload"></i> Upload Timesheet</a>
            </div>
        </div>
    </div>

    <div class="collapse" id="manual_creation">
        <div class="card card-body">
            <h4>Manual Timesheet</h4>
            <form id="timesheetForm" method="post" action="{{ route('timesheets.store') }}">
                @csrf
                @method('post')
                <input type="hidden" name="created_by" value="1">

                {{-- <div class="mb-3">
                    <label for="timesheetName" class="form-label">Timesheet Name</label>
                    <input type="text" class="form-control" id="timesheetName" name="timesheet_name" required>
                </div>
                <div class="mb-3">
                    <label for="timesheetDate" class="form-label">Timesheet Date</label>
                    <input type="date" class="form-control" id="timesheetDate" name="timesheet_date" required>
                </div> --}}
                <input type="hidden" name="upload_type_csv" value="0">
                <input type="hidden" name="created_by" value="{{$user}}">
                {{-- <div class="input-group mb-3">
                    <span class="input-group-text">Timesheet Name and Date</span>
                    <input type="text" class="form-control" id="timesheetName" name="timesheet_name" required>
                    <input type="date" class="form-control" id="timesheetDate" name="timesheet_date" required>
                    <button type="submit" id="submitButton" class="btn btn-primary" disabled>Submit</button>
                </div> --}}

                <div class="container">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="">
                                <label for="timesheetName" class="form-label">Timesheet Name *</label>
                                <input type="text" class="form-control" id="timesheetName" name="timesheet_name" placeholder="Enter Timesheet Name" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="">
                                <label for="timesheetDate" class="form-label">Timesheet Date *</label>
                                <input type="date" class="form-control" id="timesheetDate" name="timesheet_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex flex-row-reverse">
                            <button type="submit" id="submitButton" class="btn btn-primary" disabled>Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="collapse" id="upload_csv">
        <div class="card card-body">
            <h4>Upload CSV Timesheet</h4>
            <form id="uploadCsvForm" method="post" action="{{ route('timesheet.create') }}" enctype="multipart/form-data">
                @csrf
                @method('post')
                {{-- <div class="mb-3">
                    <label for="timesheetNameUpload" class="form-label">Timesheet Name</label>
                    <input type="text" class="form-control" id="timesheetNameUpload" name="timesheet_name" required>
                </div>
                <div class="mb-3">
                    <label for="timesheetDateUpload" class="form-label">Timesheet Date</label>
                    <input type="date" class="form-control" id="timesheetDateUpload" name="timesheet_date" required>
                </div>
                <div class="mb-3">
                    <label for="fileUpload" class="form-label">Choose File</label>
                    <input type="file" class="form-control" id="fileUpload" name="file_upload">
                </div> --}}
                <input type="hidden" name="upload_type_csv" value="0">
                <input type="hidden" name="created_by" value="{{$user}}">
                {{-- <div class="mb-3 text-end">
                    <button type="submit" id="uploadCsvSubmitButton" class="btn btn-primary" disabled >Submit</button>
                </div> --}}
                <div class="container">
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="">
                                <label for="timesheetName" class="form-label">Timesheet Name *</label>
                                <input type="text" class="form-control" id="timesheetName" name="timesheet_name" placeholder="Enter Timesheet Name" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="">
                                <label for="timesheetDate" class="form-label">Timesheet Date *</label>
                                <input type="date" class="form-control" id="timesheetDate" name="timesheet_date" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="">
                                <label for="fileUpload" class="form-label">Choose File *</label>
                                <input type="file" class="form-control" id="fileUpload" name="file_upload">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex flex-row-reverse">
                            <button type="submit" id="submitButton" class="btn btn-primary" disabled>Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="col-lg-12 table-responsive mt-3">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th style="width: 7%">S.No</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th style="width: 15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($timesheets as $timesheet)
                    @if ($timesheet->is_deleted != 1)
                        <tr>
                            <td>{{ $loop -> index + $timesheets->firstItem()}}</td>
                            <td>{{$timesheet['timesheet_name']}}</td>
                            <td>{{\Carbon\Carbon::parse($timesheet->timesheet_date)->format('d/m/Y')}}</td>
                            <td>
                                <a href="{{ route('timesheetdetail.index', ['timesheet_id' => $timesheet->id]) }}" class="btn btn-dark btn-sm btn-smaller" data-toggle="tooltip" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        {{$timesheets->links()}}
    </div>
</div>
@endsection
