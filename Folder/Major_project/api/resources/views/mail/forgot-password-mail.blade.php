<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 20px;
        }

        .banner {
            background-color: #000;
            color: #fff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }


        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: normal;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;

        }

        .table td a {
            text-decoration: underline;
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="banner">Timely</div>
    <div class="container mt-4">
        <h3>Hello {{ $fullname }},</h3>
        <p>You are receiving this email because we received a request to reset your password for your account: "{{ $userName }}".</p>
        <p>
            Click on the link provided below to reset your password:
            {{-- <a href="{{$url}}">Reset Password</a> --}}
        </p>
        <table class="table">
            <tbody>
                <tr>
                    <th>Username</th>
                    <td>{{ $userName }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $email }}</td>
                </tr>
                <tr>
                    <th>Reset Password Link</th>
                    <td><a href="{{ $url }}" style="text-decoration: underline; color: #007bff;">Reset Password</a></td>
                </tr>

            </tbody>
        </table>
        <br>
        <p>If you did not make this request, please ignore this email. No further action is required.</p></p>
        <br>
        <p>Thank you!</p>
        <p>By Timely Team</p>

    </div>
</body>

</html>
