<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Company;
use App\Services\CompanyService;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class CompanyController extends Controller
{
    private $companyService;

    // Constructor to initialize CompanyService
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    // Function to show a list of companies with pagination
    public function show(Request $request)
    {
        try {
            $skip = $request->query('skip', 0);  // Get the number of records to skip
            $take = $request->query('take', 10); // Get the number of records to take
            $org_id = $request->query('organisation_id'); // Get the organization ID
            $order = 'desc'; // Default order

            $result = $this->companyService->getCompanies($skip, $take, $org_id, $order);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Error fetching companies: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_fetching')], 500);
}
    }

    // Function to get details of a specific company
    // public function getCompany($id)
    // {
    //     try {
    //         $company = $this->companyService->getCompany($id);

    //         if ($company) {
    //             return response()->json($company);
    //         } else {
    //             return response()->json(['error' => 'Company not found'], 404);
    //         }
    //     } catch (Exception $e) {
    //         Log::error('Error fetching company: ' . $e->getMessage());
    //         return response()->json(['error' => Config::get('message.error.company_error_fetching')], 500);
    //     }
    // }
    public function getCompany(Request $request)
{
    try {
        $id = $request->input('company_id');
        $company = $this->companyService->getCompany($id);

        if ($company) {
            return response()->json($company);
        } else {
            return response()->json(['error' => 'Company not found'], 404);
        }
    } catch (Exception $e) {
        Log::error('Error fetching company: ' . $e->getMessage());
        return response()->json(['error' => Config::get('message.error.company_error_fetching')], 500);
    }
}


    // Function to update a company's details
    public function updateCompany(Request $request, $id)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'data.company_name' => 'required|string|min:3|max:50',
                'data.email_address' => [
                    'required',
                    'string',
                    'max:75',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'data.phone_number' => 'required|string|regex:/^[0-9]{10}$/',
                'data.company_description' => 'nullable|string',
                'data.address_line_1' => 'required|string|min:3|max:50',
                'data.address_line_2' => 'nullable|string|max:50',
                'data.city' => 'required|string|min:3|max:50',
                'data.state' => 'required|string|min:3|max:50',
                'data.country' => 'required|string|min:3|max:50',
                'data.pincode' => 'required|string|regex:/^[0-9]{6}$/',
                'data.vat_percent' => 'required|string|in:0%,5%,20%'
            ]);

            // $unique_flag = $this->companyService
            // ->uniqueContact(
            //     $validated['data']['email_address'],
            //     $validated['data']['phone_number'],
            //     $id
            // );

            // if(!$unique_flag['flag']){
            //     return response()->json([
            //         'message' => $unique_flag['message']
            //     ], 400);
            // }


            $result = $this->companyService->updateCompany($validated['data'], $id);

            if (isset($result['error'])) {
                return response()->json($result, 404);
            } else {
                return response()->json([
                    'data' => $result,
                    'toaster_success' => Config::get('message.success.company_update_success'),
                    'toaster_error' => Config::get('message.error.company_error_updating')
            ]);
            }
        } catch (Exception $e) {
            Log::error('Error updating company: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_updating')], 500);
        }
    }

    // Function to upload a company logo image
    public function imageUpload(Request $request, $id)
    {
        try {
            $uploadedFile = $request->file('image'); // Get the uploaded file
            $originalFileName = $uploadedFile->getClientOriginalName(); // Get the original file name

            // Store the uploaded file in the public/images directory
            $path = $uploadedFile->storeAs('images', $originalFileName, 'public');

            // Update the company record with the path of the uploaded logo
            DB::table('company')
                ->where('company_id', $id)
                ->update(['company_logo_path' => $originalFileName]);

            return response()->json(['toaster_success' => Config::get('message.success.company_image_upload_success')]);

            return response(['Logo Uploaded Successfully']);
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_uploading_image')], 500);
        }
    }

    // Function to add a new company
    public function addCompany(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'company_name' => 'required|string|min:3|max:50',
                'email_address' => [
                    'required',
                    'string',
                    'max:75',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
                'company_description' => 'nullable|string',
                'address_line_1' => 'required|string|min:3|max:50',
                'address_line_2' => 'nullable|string|max:50',
                'city' => 'required|string|min:3|max:50',
                'state' => 'required|string|min:3|max:50',
                'country' => 'required|string|min:3|max:50',
                'pincode' => 'required|string|regex:/^[0-9]{6}$/',
                'vat_percent' => 'nullable|string|max:10',
            ]);
            // $unique_flag = $this->companyService
            // ->uniqueContact(
            //     $validated['data']['phone_number'],
            //     $validated['data']['phone_number']
            // );

            // if(!$unique_flag['flag']){
            //     return response()->json([
            //         'message' => $unique_flag['message']
            //     ], 400);
            // }

            $result = $this->companyService->addCompany($validated);

           return response()->json([
                'data' => $result,
                'toaster_success' => Config::get('message.success.company_add_success')
            ]);
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_adding')], 500);
        }
    }

    // Function to delete a company
    public function deleteCompany($id)
    {
        try {
            $result = $this->companyService->deleteCompany($id);

            if (isset($result['error'])) {
                return response()->json($result, 404);
            } else {
                return response()->json([
                    'data' => $result,
                    'toaster_success' =>  Config::get('message.success.company_delete_success')
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_deleting')], 500);
        }
    }

    // Function to update the status of a company
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'status' => 'required|boolean'
            ]);

            $result = $this->companyService->updateStatus($id, $validated['status']);

            if (isset($result['message']) && $result['message'] === 'Company not found') {
                return response()->json($result, 404);
            } else {
                return response()->json([
                    'data' => $result,
                    'toaster_success' => Config::get('message.success.company_status_success'),
                    'toaster_success1' => Config::get('message.success.company_status_success1')]);
            }
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_updating_status')], 500);
        }
    }

    // Function to get statistics of all companies
    public function getCompanyStats()
    {
        try {
            $stats = $this->companyService->getCompanyStats();

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_fetching_stats')], 500);
        }
    }

    // Function to search for companies based on certain criteria
    public function searchCompany(Request $request)
    {
        try {
            $authUser = Auth::user();
            $authUser_org_id = $authUser['organisation_id'];

            Log::info('Search Company Request:', $request->all());

            // Build the query with necessary joins and conditions
            $companies = $this->companyService->searchCompany($request, $authUser_org_id);
            // Return the results as a JSON response
            return response()->json(['result' => $companies], 200);
        } catch (Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json(['error' => Config::get('message.error.company_error_searching')], 500);
        }
    }
}
