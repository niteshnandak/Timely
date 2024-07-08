<?php

namespace App\Services;

use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\PeopleEmploymentDetail;

class ExpenseService {

    // FUNCTION TO FETCH ALL COMPANY'S EXPENSES
    public function fetchExpenses($companyId, $skip, $take, $request) {
        try {
            $companyName = Company::where('company_id', $companyId)->pluck('company_name')->first();
            // Start with a query builder instance
            $query = Expense::query();

            $query->where('company_id', $companyId)
            ->where('is_deleted', false)
            ->orderBy('created_at', 'DESC');


            // Filtered Data
            if($request->searchFormData) {

                // Retrieve form input search data
                $people_id = $request->searchFormData['people_name'];
                $expense_type_id = $request->searchFormData['expense_type'];
                $expense_date_from = $request->searchFormData['expense_date_from'];
                $expense_date_to = $request->searchFormData['expense_date_to'];
                $status = $request->searchFormData['status'];

                // Apply filters based on individual inputs if they are provided

                if ($people_id) {
                    $query->where('people_id', $people_id);
                }

                if ($expense_type_id) {
                    $query->where('expense_type_id', $expense_type_id);
                }

                // if ($expense_date) {
                //     $query->where('expense_date', $expense_date);
                // }

                // Apply date range filter if both dates are provided
                if ($expense_date_from && $expense_date_to) {
                    // If both $dateFrom and $dateTo are given
                    $query->whereBetween('expense_date', [$expense_date_from, $expense_date_to]);
                } elseif ($expense_date_from) {
                    // If only $dateFrom is given
                    $query->where('expense_date', '>=', $expense_date_from);
                } elseif ($expense_date_to) {
                    // If only $dateTo is given
                    $query->where('expense_date', '<=', $expense_date_to);
                }

                if ($status) {
                    $query->where('status', $status);
                }

            }

            // Eager load the related models
            $query->with(['expenseType', 'peopleEmploymentDetail']);

            // Calculate the page number
            $page = ($skip / $take) + 1;
            $pagination = $query->paginate($take, ['*'], 'page', $page);

            // Retrieve the paginated data
            $searchedData = $pagination->items();

            // Query to get the count of data
            // $totalCount = $query->count();

            // // Query to get the data
            // $searchedData = $query->skip($skip)
            // ->take($take)
            // ->get();

            // Finding required fields
            $result = collect($searchedData)->map(function ($data) {
                // $employmentDetails = $data->peopleEmploymentDetail;
                // $expenseType = $data->expenseType;

                return [
                    'expense_id' => $data->expense_id,
                    'expense_number' => $data->expense_number,
                    // 'people_name' => $employmentDetails ? $employmentDetails->people_name : null,
                    // 'expense_type' => $expenseType ? $expenseType->expense_type : null,
                    'expense_type' => $data->expenseType->expense_type ?? null,
                    'people_name' => $data->peopleEmploymentDetail->people_name ?? null,
                    'amount' => $data->amount,
                    'status' => $data->status,
                    'expense_date' => $data->expense_date,
                ];
            });

            return [
                'searchedData' => $result,
                'totalCount' => $pagination->total(), //$totalCount,
                'companyName' => $companyName,
            ];

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


    // FUNCTION TO FETCH ALL PEOPLE'S EXPENSES FOR PEOPLE HUB
    public function fetchPeopleExpenses($peopleId, $skip, $take, $request) {
        try {
            // Start with a query builder instance
            $query = Expense::query();

            $query->where('people_id', $peopleId)
            ->whereIn('status', ['approved', 'processed'])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'DESC');

            // Filtered Data
            if($request->searchFormData) {

                // Retrieve form input search data
                $expense_type_id = $request->searchFormData['expense_type'];
                $expense_date_from = $request->searchFormData['expense_date_from'];
                $expense_date_to = $request->searchFormData['expense_date_to'];
                $status = $request->searchFormData['status'];

                // Apply filters based on individual inputs if they are provided

                if ($expense_type_id) {
                    $query->where('expense_type_id', $expense_type_id);
                }

                // if ($expense_date) {
                //     $query->where('expense_date', $expense_date);
                // }

                // Apply date range filter if both dates are provided
                if ($expense_date_from && $expense_date_to) {
                    // If both $dateFrom and $dateTo are given
                    $query->whereBetween('expense_date', [$expense_date_from, $expense_date_to]);
                } elseif ($expense_date_from) {
                    // If only $dateFrom is given
                    $query->where('expense_date', '>=', $expense_date_from);
                } elseif ($expense_date_to) {
                    // If only $dateTo is given
                    $query->where('expense_date', '<=', $expense_date_to);
                }

                if ($status) {
                    $query->where('status', $status);
                }

            }

            // Eager load the related models
            $query->with(['expenseType']);

            // Calculate the page number
            $page = ($skip / $take) + 1;
            $pagination = $query->paginate($take, ['*'], 'page', $page);

            // Retrieve the paginated data
            $searchedData = $pagination->items();

            // Finding required fields
            $result = collect($searchedData)->map(function ($data) {
                // $expenseType = $data->expenseType;

                return [
                    'expense_id' => $data->expense_id,
                    'expense_number' => $data->expense_number,
                    'expense_type' => $data->expenseType->expense_type ?? null,
                    'amount' => $data->amount,
                    'status' => $data->status,
                    'expense_date' => $data->expense_date,
                ];
            });

            return [
                'searchedData' => $result,
                'totalCount' => $pagination->total(), // $totalCount,
            ];

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO FETCH PEOPLE NAME
    public function fetchPersonName($people_id) {
        try{
            $peopleName = PeopleEmploymentDetail::where('people_id', $people_id)
            ->where('is_deleted', false)
            ->select('people_name')
            ->first();

            return $peopleName;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


    // FUNCTION TO FETCH EXPENSE TYPE
    public function getExpense($expense_id) {

        try {
            $expense = Expense::where('expense_id', $expense_id)
            ->where('is_deleted', false)
            ->first();

            return $expense;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO DELETE THE EXPENSE
    public function deleteExpense($expense) {
        try{

            $authUser = Auth::user();

            $expense->is_deleted = true;
            $expense->updated_by = $authUser->user_id;
            $expense->save();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO APPROVE THE EXPENSE
    public function approveExpense($expense) {
        try{
            $authUser = Auth::user();

            $expense->status = 'approved';
            $expense->updated_by = $authUser->user_id;
            $expense->save();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO GET ALL EXPENSE TYPES FOR DROPDOWN
    public function getExpenseTypes() {
        try {
            $expense_types = ExpenseType::select('expense_type_id', 'expense_type')->get();

            return $expense_types;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTON TO GET ALL PEOPLE NAMES OF THAT COMPANY FOR DROPDOWNS
    public function getPeopleNames($company_id) {
        try{
            $people_names = PeopleEmploymentDetail::select('people_id', 'people_name')
                                                    ->where('company_id', $company_id)
                                                    ->where('is_deleted', false)
                                                    ->get();

            return $people_names;
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO ADD A EXPENSE
    public function addExpense($validatedData, $company_id) {
        try {
            $authUser = Auth::user();

            $peopleId = $validatedData['formData']['people_name'];
            $expenseDate = $validatedData['formData']['expense_date'];
            $lineItems = array_reverse($validatedData['formData']['lineItems']); // Reverse the line items

            // Loop through each expense line item and save it
            foreach ($lineItems as $item) {
                $expense = new Expense;

                $expense->people_id = $peopleId;
                $expense->expense_type_id = $item['expense_type'];
                $expense->expense_date = $expenseDate;
                $expense->amount = $item['amount'];

                $expense->company_id = $company_id;
                $expense->organisation_id = $authUser->organisation_id;
                $expense->status = 'draft';
                $expense->created_by = $authUser->user_id;
                $expense->updated_by = $authUser->user_id;

                $expense->save();
            }

            // $expense = new Expense;

            // $expense->people_id = $request->formData['people_name']; // id is in value
            // $expense->expense_type_id = $request->formData['expense_type'];
            // $expense->expense_date = $request->formData['expense_date'];
            // $expense->amount = $request->formData['amount'];

            // $expense->company_id = $company_id;
            // $expense->organisation_id = $authUser->organisation_id;
            // $expense->status = 'draft';
            // $expense->created_by = $authUser->user_id;
            // $expense->updated_by = $authUser->user_id;

            // $expense->save();

        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // FUNCTION TO UPDATE A EXPENSE
    public function updateExpense($expense, $request) {
        try {

            $authUser = Auth::user();

            $expense->people_id = $request->formData['people_name'];
            $expense->expense_type_id = $request->formData['expense_type'];
            $expense->expense_date = $request->formData['expense_date'];
            $expense->amount = $request->formData['amount'];

            $expense->updated_by = $authUser->user_id;

            $expense->save();

        }
        catch(Exception $e){
            return $e->getMessage();
        }
    }


}
