<?php

namespace App\Services;

use App\Models\PayrollBatch;
use App\Models\PayrollHistory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeoplePayrollService {

    // Fetch the payrolls for the peopleId
    public function fetchPeoplePayrolls($peopleId, $skip, $take, $request) {
        try {
            // // Start with a query builder instance
            $query = PayrollHistory::query();

            $query->where('people_id', $peopleId);

            $query->where('payroll_history.is_deleted', false)
                    ->orderBy('payroll_history.created_at', 'DESC');

            // Add the join with the payroll_batch table and select the necessary columns
            $query->leftJoin('payroll_batch as pb', 'pb.payroll_batch_id', '=', 'payroll_history.payroll_batch_id')
                ->select('payroll_history.*', 'pb.payroll_batch_name');

            // Filtered Data
            if($request->searchFormData) {

                // Retrieve form input search data
                $payroll_batch_name = $request->searchFormData['payroll_batch_name'];
                $status = $request->searchFormData['status'];

                // Apply filters based on individual inputs if they are provided

                if ($status == 'Yes') {
                    $query->where('is_rollback', 1);
                }

                if ($status == 'No') {
                    $query->where('is_rollback', 0);
                }

                if($payroll_batch_name) {
                    $query->where('pb.payroll_batch_name', $payroll_batch_name);
                }

            }

            // // Calculate the page number
            $page = ($skip / $take) + 1;
            $pagination = $query->paginate($take, ['*'], 'page', $page);

            $searchedData = $pagination->items();

            return [
                'searchedPayrollData' => $searchedData,
                'totalCount' => $pagination->total(), // $totalCount,
            ];

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // Function to fetch all payroll batch names of the people for dropdowns
    public function getPayrollBatchNames($people_id) {
        try{
            // $payroll_batch_names = PayrollBatch::select('payroll_batch_name')
            //                                         ->where('is_deleted', false)
            //                                         ->distinct()
            //                                         ->orderBy('payroll_batch_name')
            //                                         ->get();

            $payroll_batch_names = PayrollHistory::join('payroll_batch', 'payroll_history.payroll_batch_id', '=', 'payroll_batch.payroll_batch_id')
                                     ->where('payroll_history.people_id', $people_id)
                                     ->where('payroll_batch.is_deleted', false)
                                     ->distinct()
                                     ->pluck('payroll_batch.payroll_batch_name');

            // for combobox filtering adding key field
            $transformedPayrollBatchNames = $payroll_batch_names->map(function ($name) {
                return ['payroll_batch_name' => $name];
            });

            return $transformedPayrollBatchNames;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


}
