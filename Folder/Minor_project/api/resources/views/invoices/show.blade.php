@extends('layouts.app')
@section('content')
    @foreach($aggregates as $invoice)
        <input type="text" value={{$invoice->worker_name}} readonly>
        <input type="text" value={{$invoice->Total_Amount}} readonly>
        <input type="text" value={{$invoice->organisation}} readonly>
        <a href="#">View</a>
        <a href="#">Send</a> <br/>
    @endforeach

    <a href="{{ route('invoice.index', ['id'=>'1']) }}">Invoices</a> 
@endsection
