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
        <h3>Hello {{ $fullname }} from {{ $organisationName }},</h3>
        <p>This email is to welcome you to our platform! To complete your account setup, you'll need to set your password.</p>
        <p>
            Check your registered details you can update any incorrect details later in your profile.
            {{-- <a href="{{$url}}">Set Password</a> --}}
        </p>
        <table class="table">
            <tbody>
                <tr>
                    <th>First Name</th>
                    <td>{{ $firstName }}</td>
                </tr>
                <tr>
                    <th>Surname</th>
                    <td>{{ $surName }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $email }}</td>
                </tr>
                <tr>
                    <th>Set Password Link</th>
                    <td><a href="{{ $url }}" style="text-decoration: underline; color: #007bff;">Set Password</a></td>
                </tr>

            </tbody>
        </table>
        <br>
        <p>Thank you!</p>
        <p>By Timely Team</p>
    </div>
</body>

</html>
