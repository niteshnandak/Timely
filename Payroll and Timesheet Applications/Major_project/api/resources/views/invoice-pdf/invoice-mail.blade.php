<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $data['template_data']['invoice']['invoice_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            padding: 20px;
            background-color: #f2f2f2;
        }

        .header img {
            max-width: 100px;
            height: auto;
        }

        .content {
            margin-top: 20px;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .content table th,
        .content table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .content table th {
            background-color: #f2f2f2;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .details div {
            width: 48%;
        }

        .thank-you {
            margin-top: 40px;
            font-size: 19px;
        }

        .footer {
            text-align: center;
            margin-top: 100px;
            font-size: 12px;
            color: #666;
        }

        .invoice-details td.gross-amount {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ route('email.track', ['inv_id' =>$data['template_data']['invoice']['invoice_id'],'message_id' =>$data['template_data']['message_id']]) }}" alt="" style="display:none;">
            <h1>{{ $data['template_data']['organisation']['name'] }}</h1>
        </div>
        <div class="content">
            <p>Dear {{ $data['template_data']['customer']['customer_name'] }},</p>
            <p>Please see the attached invoice with due date {{ \Carbon\Carbon::now()->addWeek()->format('d-m-Y') }} generated for you.</p>
            <p>Don't hesitate to reach out if you have any questions.</p>

            <div class="details">
                <div>
                    <h3>Invoice Details</h3>
                    <p>Invoice Number: {{ $data['template_data']['invoice']['invoice_number'] }}</p>
                    <p>Period end Date: {{ \Carbon\Carbon::parse($data['template_data']['invoice']['period_end_date'])->format('d-m-Y') }}</p>
                    <p>Total Amount: £ {{ $data['template_data']['invoice']['total_amount'] }} </p>
                </div>
                <div>
                    <h3>Assignment Details</h3>
                    <p>Assignment Number: {{ $data['template_data']['assignment']['assignment_num'] }}</p>
                    <p>People Name: {{ $data['template_data']['people']['people_name'] }}</p>
                </div>
            </div>

            <h3>Invoice Breakdown</h3>
            <table class="invoice-details">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price (£)</th>
                        <th>VAT (%)</th>
                        <th>Gross Amount (£)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['template_data']['invoice_details'] as $detail)
                    <tr>
                        <td>{{ $detail['description'] }}</td>
                        <td>{{ $detail['quantity'] }}</td>
                        <td>{{ $detail['unit_price'] }}</td>
                        <td>{{ $detail['vat_percent'] }}</td>
                        <td class="gross-amount">{{ $detail['gross_amount'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="thank-you">
            <p>Thank you for your business!</p>
            <a href="{{ route('link.track', [
    'inv_id' => $data['template_data']['invoice']['invoice_id'],
    'message_id' => $data['template_data']['message_id'],
    'redirect_url' => 'http://localhost:4200/login'
]) }}">By Timely</a>




        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $data['template_data']['organisation']['name'] }}. All rights reserved.</p>
            <p>{{ $data['template_data']['organisation']['contact_number'] }} | {{ $data['template_data']['organisation']['email_address'] }}</p>
        </div>
    </div>
</body>

</html>