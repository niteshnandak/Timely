<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{$data['people_name'] ?? 'Employee'}}'s Payslip</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gnBEKSQ5W5XHZ3P4GNEaBbXnbJIv6fCtJSQKxD7+CGNuxsd3+f3DC1J8++Q8/2v9" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            line-height: 1.6;
        }

        .header {
            /* align-items: center; */
            /* align-content: center; */
            /* text-align: center;
            justify-content: center;
            background-color: #f2f2f2;
            padding: 20px;
            margin-bottom: 20px; */

            display: flex;
            min-height: 200px;
            align-items: center;
            justify-content: center;
            background-color: #f2f2f2;
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

        .company-details {
            float: right;
            width: 75%;
            margin-top: -4%;
            margin-left: -8%;
        }

        .org-logo {
            float: left;
            width: 25%;
            /* display:flex; */
            padding-top: 0px;
            padding-left: 30px;

        }

        h2 {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        h3 {
            font-size: 16px;
            font-weight: normal;
            margin-top: 0;
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

        .thank-you {
            margin-top: 40px;

        }

        .footer {
            text-align: center;
            padding: 5px 0;
            bottom: 0;
            left: 0;
            font-size: 12px;
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="org-logo">
            <img src="{{ url('api/org-logo/' . $data['org_logo']) }}" height="100px" width="100px" alt="">
        </div>
        <div class="company-details">
            <h2>{{ $data['company_name'] ?? 'Company Name' }}</h2>
            <h3>{{ $data['company_address'] ?? 'Company Address' }}</h3>
        </div>
    </div>

    <div class="container">
        <div>
            <b>
                <p>Dear {{ $data['people_name'] ?? 'Employee' }},</p>
            </b>
            <p>We appreciate your hard work and dedication.</p>
            <p>Please see the attached payslip generated for you on {{ $data['payslip_date'] ?? 'today' }}. The Net salary is <b>£{{ number_format($data['net_salary'] ?? 0, 2) }}</b>.</p>
            <p>Please review all details carefully to ensure accuracy. Don't hesitate to reach out if you have any questions. For any payroll-related inquiries, please contact our HR department at <a href="mailto:helpdesk@timely.com">helpdesk@timely.com</a>.</p>
            <p>We value your contributions to {{ $data['company_name'] ?? 'Company Name' }} and thank you for your continued commitment.</p>
            <!-- <hr>
            <div class="details">
                <table id="info">
                    <tr>
                        <td>


                            <div><span class="label">People Name:</span> <span class="value">{{ $data['people_name'] ?? 'Employee' }}</span></div>
                            <div><span class="label">Job Title:</span> <span class="value">{{ $data['job_title'] ?? 'Job Title' }}</span></div>
                            <div><span class="label">Date of Joining:</span> <span class="value">{{ $data['date_of_joining'] ?? 'Joining Date' }}</span></div>
                            <div><span class="label">Payslip Date:</span> <span class="value">{{ $data['payslip_date'] ?? 'today' }}</span></div>
                        </td>
                        <td>
                            <div><span class="label">Bank:</span> <span class="value">{{ $data['bank'] ?? 'Bank Name' }}</span></div>
                            <div><span class="label">Branch:</span> <span class="value">{{ $data['branch'] ?? 'Branch Name' }}</span></div>
                            <div><span class="label">A/c No.:</span> <span class="value">{{ $data['account_no'] ?? 'Account Number' }}</span></div>
                            <div><span class="label">NINO:</span> <span class="value">{{ $data['nino'] ?? 'NINO' }}</span></div>
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
                        <td class="align-right">£{{ number_format($data['gross_salary'] ?? 0, 2) }}</td>
                        <td>Employee Tax</td>
                        <td class="align-right">£{{ number_format($data['ee_tax'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Taxable Amount</td>
                        <td class="align-right">£{{ number_format($data['taxable_amount'] ?? 0, 2) }}</td>
                        <td>Employer Tax</td>
                        <td class="align-right">£{{ number_format($data['er_tax'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Expenses</td>
                        <td class="align-right">£{{ number_format($data['expenses'] ?? 0, 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="subtotal ">Total Earnings</td>
                        <td class="align-right subtotal">£{{ number_format($data['total_payment_amount'] ?? 0, 2) }}</td>
                        <td class="subtotal">Total Deductions</td>
                        <td class="align-right subtotal">£{{ number_format($data['total_tax_deduction'] ?? 0, 2) }}</td>
                    </tr>
                <tbody class="summary-section">
                    <tr>
                        <td colspan="2"></td>
                        <td class="align-right subtotal">Total Earnings</td>
                        <td class="align-right">£{{ number_format($data['total_payment_amount'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="align-right subtotal">Total Deductions</td>
                        <td class="align-right">£({{ number_format($data['total_tax_deduction'] ?? 0, 2) }})</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="align-right subtotal">Net Salary</td>
                        <td class="align-right subtotal">£{{ number_format($data['net_salary'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
                </tbody>
            </table> -->

            <div class="thank-you">
            <p>Thank you for your business!</p> 
        </div>

            <p>Best regards,</p>
            <p>Team {{ $data['company_name'] ?? 'Company Name' }}</p>
            <p><i>This payslip is for your records. Please retain it for future reference and tax purposes.</i></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $data['company_name'] }}. All rights reserved.</p>
        </div>
</body>

</html>