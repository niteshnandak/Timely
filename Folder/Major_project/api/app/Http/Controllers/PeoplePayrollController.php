<?php

namespace App\Http\Controllers;

use App\Services\PeoplePayrollService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PeoplePayrollController extends Controller
{
    protected $peoplePayrollService;

    // Function to Create new Instance of Expense Service
    private function getPeoplePayrollService()
    {
        if ($this->peoplePayrollService == null) {
            $this->peoplePayrollService = new PeoplePayrollService();
        }
        return $this->peoplePayrollService;
    }

    // Function to fetch all the payrolls for the people
    public function getPeoplePayrolls($people_id, Request $request) {
        try {

            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);

            $data = $this->getPeoplePayrollService()->fetchPeoplePayrolls($people_id, $skip, $take, $request);

            $searchedPayrollData = $data['searchedPayrollData'];
            $totalCount = $data['totalCount'];

            if (count($searchedPayrollData) === 0) {
                return response()->json(['message' => Config::get('message.error.payrolls_not_found')], 500);
            } else {
                return response()->json([
                    'payrollData' => $searchedPayrollData,
                    'total' => $totalCount,
                ], 200);
            }

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }

    // Function to fetch all payroll batch names of the people for dropdowns
    public function getPayrollBatchNames($people_id) {
        try {
            $payroll_batch_names = $this->getPeoplePayrollService()->getPayrollBatchNames($people_id);

            return response()->json($payroll_batch_names);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }

}
