<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gnBEKSQ5W5XHZ3P4GNEaBbXnbJIv6fCtJSQKxD7+CGNuxsd3+f3DC1J8++Q8/2v9" crossorigin="anonymous">
    <title>{{$people_name}}'s Payslip</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .payslip {
            font-size: 54px;
            margin-top: 0;
            padding-top: 0;
            text-align: center;
        }

        hr.firstline {
            border: 2px solid;
            margin-bottom: 2px;
        }

        hr.secondline {
            margin-top: 0.5px;
            border: 0.5px solid black;
        }

        h2 {
            font-size: 54px;
        }

        h3 {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        h4 {
            font-size: 17px;
            font-weight: normal;
            margin-top: 0;
        }

        .container {
            width: 100%;
            margin: auto;
            line-height: 1.6;
        }

        .header {
            display: flex;
            min-height: 200px;
            align-items: center;
            justify-content: center;
            background-color: #f2f2f2;
        }

        .logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .alt-text {
            margin-top: 5px;
            font-size: 12px;
            text-align: center;
        }

        .logo-container {
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }

        .company-details {
            text-align: center;
            flex-grow: 1;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .value {
            display: inline-block;
            color: #666;
        }

        .details {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        #info {
            border: none;
        }

        #info td {
            border: none;
            padding: 1px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .summary-section {
            border: 1px solid #ddd;
        }

        .summary-section td {
            border: none;
        }

        .summary-section tr:first-child td {
            padding-top: 10px;
        }

        .summary-section tr:last-child td {
            padding-bottom: 10px;
        }

        .subtotal,
        .total {
            font-weight: bold;
        }

        .align-right {
            text-align: right;
        }

        .align-center {
            text-align: center;
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

        .company-details {
            float: right;
            width: 75%;
            margin-top: 0;
            margin-left: -8%;
            text-align: left;
            /* display: flex; */
            /* align-items: center; */
            /* justify-content: center; */
        }

        .org-logo {
            float: left;
            width: 25%;
            /* display:flex; */
            padding-top: 50px;
            padding-left: 30px;

        }

        .comp-title {
            display: flex;
            justify-content: center;
        }

        .footer {
            text-align: center;
            padding: 2px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="payslip">
        <h2>Payslip</h2>
    </div>

    <hr class="firstline">
    <hr class="secondline">

    <div class="container">

        <div class="header">
            <div class="org-logo">
                <img src="{{ $org_logo }}" height="100px" width="100px" alt="">
            </div>
            <div class="company-details">
                <h3>{{ $company_name ?? 'Company Name Not Available' }}</h3>
                <h4>{{ $company_address }}</h4>
            </div>
        </div>

        <hr>
        <div class="details">
            <table id="info">
                <tr>
                    <td>
                        <div><span class="label">People Name:</span> <span class="value">{{ $people_name }}</span></div>
                        <div><span class="label">Job Title:</span> <span class="value">{{ $job_title }}</span></div>
                        <div><span class="label">Date of Joining:</span> <span class="value">{{ $date_of_joining }}</span></div>
                        <div><span class="label">Payslip Date:</span> <span class="value">{{ $payslip_date }}</span></div>
                    </td>
                    <td>
                        <div><span class="label">Bank:</span> <span class="value">{{ $bank }}</span></div>
                        <div><span class="label">Branch:</span> <span class="value">{{ $branch }}</span></div>
                        <div><span class="label">A/c No.:</span> <span class="value">{{ $account_no }}</span></div>
                        <div><span class="label">NINO:</span> <span class="value">{{ $nino }}</span></div>
                    </td>
                </tr>
            </table>
        </div>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Earnings</th>
                    <th class="align-right">Amount</th>
                    <th>Deductions</th>
                    <th class="align-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gross Salary</td>
                    <td class="align-right">£{{ number_format($gross_salary, 2) }}</td>
                    <td>Employee Tax</td>
                    <td class="align-right">£{{ number_format($ee_tax, 2) }}</td>
                </tr>
                <tr>
                    <td>Taxable Amount</td>
                    <td class="align-right">£{{ number_format($taxable_amount, 2) }}</td>
                    <td>Employer Tax</td>
                    <td class="align-right">£{{ number_format($er_tax, 2) }}</td>
                </tr>
                <tr>
                    <td>Expenses</td>
                    <td class="align-right">£{{ number_format($expenses, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="subtotal">Total Earnings</td>
                    <td class="align-right subtotal">£{{ number_format($total_payment_amount, 2) }}</td>
                    <td class="subtotal">Total Deductions</td>
                    <td class="align-right subtotal">£{{ number_format($total_tax_deduction, 2) }}</td>
                </tr>
            <tbody class="summary-section">
                <tr>
                    <td colspan="2"></td>
                    <td class="align-right subtotal">Total Earnings</td>
                    <td class="align-right">£{{ number_format($total_payment_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td class="align-right subtotal">Total Deductions</td>
                    <td class="align-right">£({{ number_format($total_tax_deduction, 2) }})</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td class="align-right subtotal">Net Salary</td>
                    <td class="align-right subtotal">£{{ number_format($net_salary, 2) }}</td>
                </tr>
            </tbody>
            </tbody>
        </table>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $company_name }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>