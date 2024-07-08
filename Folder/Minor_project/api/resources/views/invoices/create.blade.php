@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 my-4">
                    <div class="" >
                        <h5>Create an Invoice</h5>
                    </div>
                    <form method="POST" action="{{route('invoice.store')}}">
                        @csrf
                        <div class="mb-3">
                            <label for="worker_id">Worker ID *</label>
                            <input type="text" class="form-control" id="worker_id" name="worker_id">
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="worker_name">Worker Name *</label>
                                <input type="text" class="form-control" id="worker_name" name="worker_name">
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="invoice_date">Date *</label>
                                    <input type="date" class="form-control" id="date" name="invoice_date">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="total_amount">Amount *</label>
                                    <input type="text" class="form-control" id="total_amount" name="total_amount">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="organsiation">Organisation *</label>
                                    <input type="text" class="form-control" id="organisation" name="organisation">
                                </div>
                            </div>
                        </div></br>
                        <div class="mb-3 text-end">
                            <input type="hidden" name="" value="0">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
