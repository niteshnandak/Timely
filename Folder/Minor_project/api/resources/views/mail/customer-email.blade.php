<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            
        }

        .invoice-info {
            font-size: 14px;
        }

        .label {
            font-weight: bold;
        }

        .display-4 {
            font-size: 3rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div>
            <b>
                <p>Dear {{ $data['invoice']->organisation }},</p>
            </b>
            <p>Please see the attached invoice with due date {{ date('Y-m-d', strtotime('+1 week')) }} generated for
                {{ $data['invoice']->worker_name }} with total amount of <b>&#163;{{ $data['invoice']->total_amount + $data['invoice']->total_amount*0.18 }}</b>.</p>
            <p></p>
            <p>Please don't hesitate to reach out if you have any questions.</p>


        </div>
        <hr>
        <div class="invoice-info">
            <div class="header">
                <h1 class="display-4">INVOICE DETAILS</h1>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4>Invoice Information</h4>
                    <p>
                        <span class="label">Invoice No.:</span>
                        <span class="value">{{ 'INV_0' . $data['invoice']->id }}</span>
                    </p>
                    <p>
                        <span class="label">Due Date:</span>
                        <span class="value">{{ date('Y-m-d', strtotime('+1 week')) }}</span>
                    </p>

                </div>
                <div class="col-md-6">
                    <h4>Worker Information</h4>
                    <p>
                        <span class="label">Organisation:</span>
                        <span class="value">{{ $data['invoice']->organisation }}</span>
                    </p>

                    <p>
                        <span class="label">Worker Name:</span>
                        <span class="value">{{ $data['invoice']->worker_name }}</span>
                    </p>
                </div>
            </div>
        </div>
        <hr>

        <p>Best regards,</p>
        <p>Timely Team</p>

    </div>
</body>

</html>
