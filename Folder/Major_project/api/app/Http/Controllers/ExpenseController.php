<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use Exception;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // $employmentDetails = $expense->peopleEmploymentDetail;

    protected $expenseService;

    // Function to Create new Instance of Expense Service
    private function getExpenseService()
    {
        if ($this->expenseService == null) {
            $this->expenseService = new ExpenseService();
        }
        return $this->expenseService;
    }

    // Function to fetch expenses in company for grid
    public function getExpenses($company_id, Request $request) {

        try {
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);

            $data = $this->getExpenseService()->fetchExpenses($company_id, $skip, $take, $request);

            $searchedData = $data['searchedData'];
            $totalCount = $data['totalCount'];
            $companyName = $data['companyName'];

            if (count($searchedData) === 0) {
                return response()->json(['message' => Config::get('message.error.no_expense_found')], 200);
            } else {
                return response()->json([
                    'searchedUserData' => $searchedData,
                    'total' => $totalCount,
                    'companyName' => $companyName,
                ], 200);
            }

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }

    }

    // Function to fetch all expenses for people grid in People Hub
    public function getPeopleExpenses($people_id, Request $request) {
        try {
            $skip = $request->query('skip', 0);
            $take = $request->query('take', 10);

            $data = $this->getExpenseService()->fetchPeopleExpenses($people_id, $skip, $take, $request);

            $searchedData = $data['searchedData'];
            $totalCount = $data['totalCount'];

            if (count($searchedData) === 0) {
                return response()->json(['message' => Config::get('message.error.no_expense_found')], 500);
            }
            else {
                return response()->json([
                    'searchedUserData' => $searchedData,
                    'total' => $totalCount,
                ], 200);
            }

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }

    }

    // Function to fetch the name of the people in people Hub
    public function getPeopleName($people_id) {
        try{
            $data = $this->getExpenseService()->fetchPersonName($people_id);

            $peopleName = $data['people_name'];

            return response()->json(['peopleName' => $peopleName], 200);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }


    // Function to delete the expense
    public function deleteExpense($company_id, $expense_id) {

        try {
            $expense = $this->getExpenseService()->getExpense($expense_id);
            $this->getExpenseService()->deleteExpense($expense);

            return response()->json(['message' => Config::get('message.success.expense_delete_success')], 200);

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.expense_delete_error')], 500);
        }
    }


    // Function to approve the expense to approved status
    public function approveExpense($company_id, $expense_id) {
        try {
            $expense = $this->getExpenseService()->getExpense($expense_id);
            $this->getExpenseService()->approveExpense($expense);

            return response()->json(['message' => Config::get('message.success.expense_approved_success')], 200);

        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.expense_approved_error')], 500);
        }
    }


    // Function to add new expenses
    public function addExpense($company_id, Request $request) {
        try {
            // return response()->json([$request->all()]);
            // Validate the request
            $validatedData = $request->validate([
                'formData.people_name' => 'required|exists:people,people_id',
                'formData.expense_date' => 'required|date',
                'formData.lineItems' => 'required|array|min:1',
                'formData.lineItems.*.expense_type' => 'required|exists:expense_types,expense_type_id',
                'formData.lineItems.*.amount' => 'required|numeric|min:0.01'
            ]);

            $this->getExpenseService()->addExpense($validatedData, $company_id);

            return response()->json(['message' => Config::get('message.success.expense_added_success')], 200);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.expense_added_error')], 400);
        }
    }


    // Function to fetch all expense types for dropdowns
    public function getExpenseTypes() {
        try {
            $expense_types = $this->getExpenseService()->getExpenseTypes();

            return response()->json($expense_types);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }

    // Function to fetch all people names of a company for dropdowns
    public function getPeopleNames($company_id) {
        try {
            $people_names = $this->getExpenseService()->getPeopleNames($company_id);

            return response()->json($people_names);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }

    // Function to fetch expense data for editing
    public function getEditExpenseData($expense_id) {
        try{
            $expense = $this->getExpenseService()->getExpense($expense_id);

            return response()->json($expense);
        }
        catch(Exception $e){
            return response()->json(['message' => Config::get('message.error.exception_error')], 500);
        }
    }

    // Function to update the edited data of an expense
    public function updateExpense($expense_id, Request $request) {
        try{
            $expense = $this->getExpenseService()->getExpense($expense_id);

            $this->getExpenseService()->updateExpense($expense, $request);

            return response()->json(['message' => Config::get('message.success.expense_updated_success'), 200], 200);
        }
        catch(Exception $e) {
            return response()->json(['message' => Config::get('message.error.expense_updated_error')], 500);
        }
    }

}
