<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <style>
        /* Style for the layout */
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .container {
            width: 80%;
            margin: auto;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .invoice-logo {
            max-width: 200px;
            margin-bottom: 20px;
        }

        .invoice-info {
            font-size: 16px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .value {
            display: inline-block;
            color: #666;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .subtotal, .total {
            font-weight: bold;
        }

        .align-right {
            text-align: right;
        }

        .subtotal {
            text-align: right;
        }

        .total {
            text-align: right;
            border-top: 2px solid #333;
        }

        .total-value {
            border-top: 2px solid #333;
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 1em 0;
        }

    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            {{-- <img src="../public/logo/Timely2.png" alt="Company Logo" class="invoice-logo"> --}}
            <h1>INVOICE DETAILS</h1>
        </div>
        <hr><br>
        <div class="invoice-info">
            <div><span class="label">Invoice No.:</span> <span class="value">{{ 'INV_0' . $invoice->id }}</span></div>
            <div><span class="label">Date:</span> <span class="value">{{ $date }}</span></div>
            <div><span class="label">Worker ID:</span> <span class="value">{{ $invoice->worker_id }}</span></div>
            <div><span class="label">Worker Name:</span> <span class="value">{{ $invoice->worker_name }}</span></div>
            <div><span class="label">Organisation:</span> <span class="value">{{ $invoice->organisation }}</span></div>
        </div>
        <br><hr>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Serial No.</th>
                        <th>Date</th>
                        <th>Hourly Pay (<span style="font-family: DejaVu Sans; sans-serif;">&#163;</span>)</th>
                        <th>Hours Worked</th>
                        <th class="align-right">Amount (<span style="font-family: DejaVu Sans; sans-serif;">&#163;</span>)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $counter = 1; // Initialize counter
                    @endphp
                    @foreach ($invoiceDetails as $invoiceDetail)
                        <tr>
                            <td>{{ $counter++ }}</td>
                            <td>{{\Carbon\Carbon::parse($invoiceDetail->invoice_date)->format('d-m-Y')}}</td>
                            <td class="align-right">{{ $invoiceDetail->hourly_pay }}</td>
                            <td class="align-right">{{ $invoiceDetail->hours_worked }}</td>
                            <td class="align-right">{{ $invoiceDetail->total_amount }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="subtotal">Sub Total:</td>
                        <td class="align-right">{{ $invoice->total_amount }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="subtotal">Tax (18%):</td>
                        <td class="align-right">{{ number_format($invoice->total_amount * 0.18, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="total">Total Amount:</td>
                        <td class="total-value align-right"><span style="font-family: DejaVu Sans; sans-serif;">&#163;</span>{{ $invoice->taxed_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
