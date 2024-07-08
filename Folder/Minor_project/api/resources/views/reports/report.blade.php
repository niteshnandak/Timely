<table border="1">
    <thead>
        <tr>
            {{-- <th style="background-color: #f2f2f2; font-weight: bold;">Timesheet Id</th> --}}
            {{-- <th style="background-color: #f2f2f2; font-weight: bold;">Invoice Id</th> --}}
            <th style="background-color: #f2f2f2; font-weight: bold;">Worker Id</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Worker Name</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Invoice Date</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Hourly Pay</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Hours Worked</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Total Amount</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Organisation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($filteredData as $data)
        <tr>
            {{-- <td>{{ $data->timesheet_id }}</td> --}}
            {{-- <td>{{ $data->invoice_id }}</td> --}}
            <td>{{ $data['worker_id'] }}</td>
            <td>{{ $data['worker_name'] }}</td>
            <td>{{ $data['invoice_date'] }}</td>
            <td>{{ $data['hourly_pay'] }}</td>
            <td>{{ $data['hours_worked'] }}</td>
            <td>{{ $data['total_amount'] }}</td>
            <td>{{ $data['organisation'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
