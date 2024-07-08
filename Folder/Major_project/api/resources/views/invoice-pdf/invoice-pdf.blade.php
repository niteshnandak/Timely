<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
        }

        .invoice-header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #dddddd;
        }

        .header {
            text-align: center;
            padding: 10px 0;
        }

        .invoice-header {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            padding: 5px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        .invoice-title {
            font-size: 17px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;

        }



        .invoice-details {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .invoice-details th,
        .invoice-details td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }

        .invoice-details th {
            background-color: #f0f0f0;
        }

        .customer-details,
        .inv-assg-details {
            margin-bottom: 20px;
        }

        .customer-details {
            float: left;
            width: 50%;
        }

        .inv-assg-details {
            float: right;
            width: 50%;
        }

        .total {
            text-align: right;
            padding: 10px;
            font-weight: bold;
        }

        .invoice-details td.net-amount,
        .invoice-details td.unit-price {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="invoice-header">INVOICE</div>
        <div class="header">
            <img src="{{ $invoice_data['logo_path'] }}" height="50px" width="50px">
            <h2>{{ $invoice_data['company']->company_name }}</h2>
            <p>Phone: {{ $invoice_data['company']->phone_number }}<br>
                Email: {{ $invoice_data['company']->email_address }}
            </p>
        </div>

        <div class="customer-details">
            <h2>Customer Details:</h2>
            <p>{{ $invoice_data['customer']->customer_name }}<br>
                {{ $invoice_data['address']->address_line_1 }}<br>
                {{ $invoice_data['address']->city }}, {{ $invoice_data['address']->state }}, {{ $invoice_data['address']->country }}<br>
                <strong> Phone:</strong> {{ $invoice_data['customer']->phone_number }}<br>
                <strong>Email:</strong>  {{ $invoice_data['customer']->email_address }}
            </p>
        </div>

        <div class="inv-assg-details">
            <h2>Invoice Details:</h2>
            <p>
                <strong>Invoice Number:</strong> {{ $invoice_data['invoice']->invoice_number }}<br>
                <strong>Assignment Number:</strong> {{ $invoice_data['assignment']->assignment_num }}<br>
                <strong>People Name:</strong> {{ $invoice_data['people']->people_name }}<br>
                <strong>Invoice Date:</strong> {{ date('d-m-Y') }}<br>
                <strong>Period End Date:</strong> {{ \Carbon\Carbon::parse($invoice_data['invoice']->period_end_date)->format('d-m-Y') }}
            </p>

        </div>

        <div class="invoice-title">
            <p>Invoice Breakdown</p>
        </div>

        <table class="invoice-details">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price (£)</th>
                    <th>VAT (%)</th>
                    <th>Net Amount (£)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice_data['invoice_details'] as $detail)
                <tr>
                    <td>{{ $detail->description }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td class="unit-price">
                        @if($detail->unit_price < 0) ({{ number_format(abs($detail->unit_price), 2) }}) @else {{ number_format($detail->unit_price, 2) }} @endif </td>
                    <td>{{ $detail->vat_percent }}</td>
                    <td class="net-amount">
                        @if($detail->gross_amount < 0) ({{ number_format(abs($detail->gross_amount), 2) }}) @else {{ number_format($detail->gross_amount, 2) }} @endif </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            <p>Total Amount: £{{ number_format($invoice_data['invoice']->total_amount, 2) }}</p>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>