<!DOCTYPE html>
<html>

<head>
    <title>Test Email</title>
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
        <h3>Hello {{ $data['user_data']['userName'] }},</h3>
        <p>Congrats on registering! Please set your password to log in.</p>
        <p>Check your registered details you can update any incorrect details later in your profile.</p>

        <table class="table">
            <tbody>
                <tr>
                    <th>First Name</th>
                    <td>{{ $data['user_data']['firstName'] }}</td>
                </tr>
                <tr>
                    <th>Surname</th>
                    <td>{{ $data['user_data']['surName'] }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $data['user_data']['email'] }}</td>
                </tr>
                <tr>
                    <th>Set Password</th>
                    <td><a href="{{ $data['url'] }}" style="text-decoration: underline; color: #007bff;">Set Password</a></td>
                </tr>

            </tbody>
        </table>
        <br>
        <p>Thank you!</p>
        <p>By Timely Team.</p>
    </div>
</body>

</html>
