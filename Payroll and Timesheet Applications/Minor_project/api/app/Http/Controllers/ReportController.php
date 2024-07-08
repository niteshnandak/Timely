<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FilteredDataExport;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    //

    public function showReports(Request $request) {
        // $validator = $request->validate([
        //     // 'timesheet_id' => 'nullable|exists:invoice,timesheet_id',
        //     'worker_id' => 'nullable|exists:invoice,worker_id',
        //     'organisation' => 'nullable|exists:invoice,organisation',
        //     'date_from' => 'nullable|date',
        //     'date_to' => 'nullable|date|after_or_equal:date_from',
        // ]);

        // Retrieve form input data
        // $timesheetId = $request->input('timesheet_id');
        $workerId = $request->input('worker_id');
        $organisation = $request->input('organisation');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Start with a query builder instance
        $query = InvoiceDetail::query();

        // Apply filters based on individual inputs if they are provided
        // if ($timesheetId) {
        //     $query->where('timesheet_id', $timesheetId);
        // }

        if ($workerId) {
            $query->where('worker_id', $workerId);
        }

        if ($organisation) {
            $query->where('organisation', $organisation);
        }

        // Apply date range filter if both dates are provided
        if ($dateFrom && $dateTo) {
            // If both $dateFrom and $dateTo are given
            $query->whereBetween('invoice_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            // If only $dateFrom is given
            $query->where('invoice_date', '>=', $dateFrom);
                  //->where('invoice_date', '<=', now()->format('Y-m-d')); // Current date
        } elseif ($dateTo) {
            // If only $dateTo is given
            $query->where('invoice_date', '<=', $dateTo);
        }

        // Retrieve the filtered data
        $filteredData = $query->get();

        return response()->json($filteredData);

    }

    public function generateReports(Request $request){
        // fetch the filtered data
        $filteredData = $request->all();

        // Export filtered data to Excel using Laravel Excel
        return Excel::download(new FilteredDataExport($filteredData), 'report_data.xlsx');

    }
}
