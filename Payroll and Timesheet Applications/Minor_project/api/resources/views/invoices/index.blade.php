@extends('layouts.app')
@section('content')
    <div class="container">
        {{-- <div class="border p-3 mx-4 my-4 d-flex justify-content-between">
            <a class="btn btn-primary" href="{{ route('invoice.create') }}"><i class="bi bi-plus-circle"></i> Create
                Invoice</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportsModal">
                Generate Reports
            </button>
        </div> --}}


        <div class="border border-2 p-3 my-4">
            <div class="d-flex justify-content-between">
                <h5> Invoices List</h5>
                <div class="d-flex">
                    <a class="btn btn-success me-3" href="{{ route('invoice.create') }}">
                        <i class="bi bi-plus-circle"></i> Create Invoice
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportsModal">
                        <i class="bi bi-box-arrow-down"></i> Generate Reports
                    </button>
                </div>
            </div>
        </div>



        <div>
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
        <div>
            @if (session()->has('fail_pdf'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('fail_pdf') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
        {{-- <div>
            @if (session()->has('deleted_pdf'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('deleted_pdf') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div> --}}
        {{-- <div class="border">
        @if ($response['success'] ?? false)
            <div class="alert alert-success">
                {{ $response['message'] }}
            </div>
        @endif
    </div> --}}
        {{-- <div class="border">
        @isset($success)
            <div class="alert alert-success">
                {{ $success }}
            </div>
        @endisset
    </div> --}}



        <!--Reports Modal -->
        <div class="modal fade" id="reportsModal" tabindex="-1" aria-labelledby="reportsModalLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Download Reports</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <form id="reportForm" action="{{ route('generate.reports') }}" method="post">
                            @csrf
                            {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                            {{-- <div class="mb-3">
                        <label for="timesheet_id" class="form-label">Timesheet ID</label>
                        <input type="text" class="form-control" id="timesheet_id" name="timesheet_id" value="{{ old('timesheet_id') }}">
                        @error('timesheet_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div> --}}
                            <div class="mb-3">
                                <label for="worker_id" class="form-label">Worker ID</label>
                                <input type="text" class="form-control" id="worker_id" name="worker_id"
                                    value="{{ old('worker_id') }}">
                                @error('worker_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="organisation" class="form-label">Organisation</label>
                                <input type="text" class="form-control" id="organisation" name="organisation"
                                    value="{{ old('organisation') }}">
                                @error('organisation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                        value="{{ old('date_from') }}">
                                    @error('date_from')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                        value="{{ old('date_to') }}">
                                    @error('date_to')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="downloadBtn">Download</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- <script>

        </script> --}}

        {{-- TABLE CODE --}}
        <div class="col-lg-12 table-responsive my-4">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <th style="width: 7%">S.No</th>
                    <th>Worker Name</th>
                    <th>Invoiced Date</th>
                    <th>Total Amount</th>
                    <th>Organisation</th>
                    <th style="width:20%">Actions</th>
                </thead>
                <tbody>
                    <?php $i = $invoices->perPage() * ($invoices->currentPage() - 1); ?>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td><?php $i++; ?>{{ $i }}</td>
                            <td>{{ $invoice['worker_name'] }}</td>
                            @if (isset($invoice['invoiced_date']))
                                <td>{{ \Carbon\Carbon::parse($invoice['invoiced_date'])->format('d/m/Y') }}</td>
                            @else
                                <td>{{ $invoice['invoiced_date'] }}</td>
                            @endif
                            <td class="text-end">&#163;{{ $invoice['total_amount'] + $invoice['total_amount'] * 0.18 }}</td>
                            <td>{{ $invoice['organisation'] }}</td>
                            @php
                                // $isGenerated = DB::table('generated_files')
                                //     ->where('invoice_id', $invoice['id'])
                                //     ->exists();
                                    // ->value('is_deleted'); // checking soft delete value
                                // if($isGenerated){
                                //     // Count the number of records with is_deleted = 0 for the specified invoice_id
                                //     $countGeneratedFiles = DB::table('generated_files')
                                //                         ->where('invoice_id', $invoice['id'])
                                //                         ->where('is_deleted', 0)
                                //                         ->count();
                                // }

                                // checking if invoice_id is in generated_files table and its is_deleted value = 0
                                $isGenerated = DB::table('generated_files')
                                    ->where('invoice_id', $invoice['id']) // checking if invoice_id is present in generated_files table
                                    ->where('is_deleted', 0) // checking for soft delete
                                    ->exists();
                            @endphp
                            <td>
                                @if (isset($isGenerated) && $isGenerated)
                                    <!-- Show all buttons -->
                                    <a class="btn btn-primary btn-sm btn-smaller"
                                        href="{{ route('pdf.view', ['invoice' => $invoice['id']]) }}"
                                        title="Click to view PDF" target="__blank"><i class="bi bi-eye"></i></a>
                                    {{-- <a class="btn btn-primary" href="{{ route('pdf.view', $invoice ) }}" target="__blank"><i class="bi bi-eye"></i></a> --}}

                                    <a class="btn btn-secondary btn-sm btn-smaller"
                                        href="{{ route('pdf.download', ['invoice' => $invoice['id']]) }}"
                                        title="Click to download PDF"><i class="bi bi-download"></i></a>

                                    <a class="btn btn-success btn-sm btn-smaller"
                                        href="{{ route('mail.invoice', ['invoice' => $invoice['id']]) }}"
                                        title="Click to mail Invoice PDF"><i class="bi bi-envelope-at"></i></a>

                                    <button type="button" class="btn btn-danger btn-sm btn-smaller"
                                        data-bs-toggle="modal" data-bs-target="#deletepdfModal"
                                        title="Click to Delete Pdf"><i class="bi bi-trash"></i></button>


                                    <!-- Deleted PDF Invoice Modal -->
                                    <div class="modal fade" id="deletepdfModal" tabindex="-1"
                                        aria-labelledby="deletepdfModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Delete Invoiced PDF</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{$invoice['id']}}</p>
                                                        Are you sure you want to Delete this Invoice?
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('pdf.delete', ['invoice' => $invoice['id']]) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Yes</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <a class="btn btn-danger"
                                        href="{{ route('pdf.delete', ['invoice' => $invoice['id']]) }}"
                                        title="Click to Delete Pdf"><i class="bi bi-trash"></i></a> --}}
                                @else
                                    <!-- Show the 'Generate' button -->
                                    <a class="btn btn-dark btn-sm btn-smaller"
                                        href="{{ route('pdf.generate', ['invoice' => $invoice['id']]) }}"><i
                                            class="bi bi-file-earmark-arrow-down"></i> Generate</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $invoices->links() }}
        </div>
    </div>
@endsection
