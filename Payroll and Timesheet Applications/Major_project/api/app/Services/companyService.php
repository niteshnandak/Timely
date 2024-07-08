<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Assignment;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    // Retrieve a list of companies with pagination and sorting options
    public function getCompanies($skip, $take, $org_id, $order = 'desc')
    {
        try {
            $user = Auth::user(); // Get the authenticated user

            // Query to fetch companies based on organization ID, including address details, and order them
            $companiesQuery = Company::where('is_deleted', 0)
            ->where('organisation_id', $org_id)
            ->with('address')
            ->orderBy('company_id', $order);

            $total = $companiesQuery->count(); // Count total companies
            $companies = $companiesQuery->skip($skip)->take($take)->get(); // Get companies with pagination

            // Format the companies data
            $formattedCompanies = $companies->map(function ($company) {

                $customerCount = Customer::where('company_id',$company->company_id)->count();
                $assignmentCount = Assignment::where('company_id',$company->company_id)->count();
                return [
                    'company_id' => $company->company_id,
                    'organisation_id' => $company->organisation_id,
                    'company_name' => $company->company_name,
                    'email_address' => $company->email_address,
                    'address_id' => $company->address_id,
                    'phone_number' => $company->phone_number,
                    'vat_percent' => $company->vat_percent,
                    'company_description' => $company->company_description,
                    'company_logo_path' => $company->company_logo_path,
                    'status' => $company->status,
                    'is_deleted' => $company->is_deleted,
                    'customer_count' => $customerCount,
                    'assignment_count' => $assignmentCount,
                    'created_at' => $company->created_at,
                    'created_by' => $company->created_by,
                    'updated_at' => $company->updated_at,
                    'updated_by' => $company->updated_by,
                    'city' => $company->address ? $company->address->city : null,
                    'address_line_1' => $company->address ? $company->address->address_line_1 : null,
                    'address_line_2' => $company->address ? $company->address->address_line_2 : null,
                    'pincode' => $company->address ? $company->address->pincode : null,
                    'state' => $company->address ? $company->address->state : null,
                    'country' => $company->address ? $company->address->country : null,
                ];
            });

            // Return the formatted companies, authenticated user, and total count
            return [
                'companies' => $formattedCompanies,
                'user' => $user,
                'total' => $total,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching companies: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_fetching')];
        }
    }

    // Retrieve a single company by its ID
    // public function getCompany($id)
    // {
    //     try {
    //         Log::info('Retrieving company with ID: ' . $id);

    //         // Fetch the company by ID including its address details
    //         $company = Company::where('is_deleted', 0)->where('company_id', $id)->with('address')->first();

    //         return $company;
    //     } catch (Exception $e) {
    //         Log::error('Error fetching company: ' . $e->getMessage());
    //         return ['error' => __('messages.error.company_error_fetching')];
    //     }
    // }

    public function getCompany($id)
    {
        try {
            Log::info('Retrieving company with ID: ' . $id);

            $company = Company::where('is_deleted', 0)
                        ->where('company_id', $id)
                        ->with('address')
                        ->first();

            return $company;
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_fetching')];
        }
    }

    // Update the details of an existing company
    public function updateCompany($validated, $id)
    {
        try {
            // $authUser = Auth::user(); // Get the authenticated user
            // $authUserId = $authUser->user_id; // Get the authenticated user's ID

            // Find the company and its associated address by ID
            $company = Company::where('company_id', $id)->first();
            $address = Address::where('address_id', $company->address_id)->first();

            if (!$company) {
                return ['error' => 'Company not found'];
            }

            // Update company details
            $company->update([
                'company_name' => $validated['company_name'],
                'email_address' => $validated['email_address'],
                'phone_number' => $validated['phone_number'],
                'vat_percent' => $validated['vat_percent'],
                'company_description' => $validated['company_description'],
                // 'updated_by' => $authUserId,
            ]);

            // Update address details
            $address->update([
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'country' => $validated['country'],
                'pincode' => $validated['pincode'],
            ]);

            return ['message' => 'Company updated successfully'];
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_updating')];
        }
    }

    // Add a new company to the database
    public function addCompany($validated)
    {
        try {
            $authUser = Auth::user(); // Get the authenticated user
            $authUserId = $authUser->user_id; // Get the authenticat    ed user's ID
            $authUserOrgId = $authUser->organisation_id; // Get the authenticated user's organization ID

            // Create a new address record
            $address = Address::create([
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'country' => $validated['country'],
                'pincode' => $validated['pincode'],
            ]);

            // Create a new company record
            $company = Company::create([
                'organisation_id' => $authUserOrgId,
                'company_name' => $validated['company_name'],
                'email_address' => $validated['email_address'],
                'phone_number' => $validated['phone_number'],
                'company_description' => $validated['company_description'],
                'address_id' => $address->address_id,
                'vat_percent' => $validated['vat_percent'],
                'status' => '1',
                // 'created_by' => $authUserId,
            ]);

            return ['message' => 'Company created successfully', 'company' => $company, 'company_id' => $company->company_id];
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_adding')];
        }
    }

    // Mark a company as deleted by updating its is_deleted flag
    public function deleteCompany($id)
    {
        try {
            $company = Company::where('company_id', $id)->first();

            if (!$company) {
                return ['error' => 'Company not found'];
            }

            Log::info('Company before update: ' . $company);

            // Update the company's is_deleted flag to 1
            $updated = $company->update([
                'is_deleted' => 1,
                'updated_by' => Auth::id(),
            ]);

            Log::info('Update result: ' . ($updated ? 'Success' : 'Failed'));

            return ['message' => 'Company marked as deleted successfully'];
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_deleting')];
        }
    }

    // Placeholder for search company functionality
    public function searchCompany($request, $authUser_org_id)
    {
        try {
            // Implement search functionality here
            $query = Company::join('address', 'company.address_id', '=', 'address.address_id')
                ->where('company.organisation_id', $authUser_org_id)
                ->where('company.is_deleted', 0);

            // Add search conditions based on request parameters
            if ($request->has('company_name') && !empty($request->company_name)) {
                $query->where('company_name', 'like', '%' . $request->company_name . '%');
            }
            if ($request->has('email_address') && !empty($request->email_address)) {
                $query->where('email_address', 'like', '%' . $request->email_address . '%');
            }
            if ($request->has('status') && $request->status !== null) {
                $query->where('company.status', $request->status);
            }

            // Execute the query and get the results
            $companies = $query->get();

            return $companies;


        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_searching')];
        }
    }

    // Update the status of a company
    public function updateStatus($id, $status)
    {
        try {
            $company = Company::find($id);
            if ($company) {
                $company->status = $status; // Update the company's status
                $company->save(); // Save the changes
                return ['message' => 'Status updated successfully'];
            } else {
                return ['message' => 'Company not found'];
            }
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_updating_status')];
        }
    }

    // check whether the contacts is unique for the company
    public function uniqueContact($mail_id, $phone_number, $company_id = null){


        // getting the customers with the email id and phone number
        $unique_flag_mail = Company::where('email_address',$mail_id);
        $unique_flag_phone = Company::where('phone_number',$phone_number);

        // used to check whether the mail belongs to the user itself if from edit
        if($company_id){
            $unique_flag_mail = $unique_flag_mail->where('company_id',"!=",$company_id);
            $unique_flag_phone = $unique_flag_phone->where('company_id',"!=",$company_id);
        }

        // getting the datas
        $unique_flag_mail = $unique_flag_mail->get()->toArray();
        $unique_flag_phone = $unique_flag_phone->get()->toArray();

        // if the datas are not uniqu sending a error
        if(!empty($unique_flag_mail) || !empty($unique_flag_phone)){
            return [
                'data' => [$unique_flag_mail, $unique_flag_phone],
                'message' => 'Mail id or Phone number is already taken',
                'flag' => false
            ];
        }
        return [
            'message' => 'Success',
            'flag' => true
        ];
    }



    // Retrieve statistical data about companies
    public function getCompanyStats()
    {
        try {
            $authUser = Auth::user(); // Get the authenticated user
            $authUserOrgId = $authUser->organisation_id;

            $totalCompanies = Company::where('is_deleted', 0)
            ->where('organisation_id', $authUserOrgId)
            ->count(); // Count total non-deleted companies
            $newCompaniesLastWeek = Company::where('created_at', '>=', now()->subWeek())
            ->where('is_deleted',0)
            ->where('organisation_id', $authUserOrgId)
            ->count(); // Count companies created in the last week
            $newCompaniesLastMonth = Company::where('created_at', '>=', now()->subMonth())
            ->where('is_deleted',0)
            ->where('organisation_id', $authUserOrgId)
            ->count(); // Count companies created in the last month
            $activeCompanies = Company::where('status', 1)
            ->where('is_deleted', 0)
            ->where('organisation_id', $authUserOrgId)
            ->count(); // Count active companies

            // Get top 3 companies with the highest number of assignments

                $topCompanies = DB::table('assignment as a')
                ->select('c.company_id', 'c.company_name', DB::raw('COUNT(a.company_id) as total_assignments'))
                ->join('company as c', 'c.company_id', '=', 'a.company_id')
                ->where('c.is_deleted', 0)
                ->where('a.is_deleted', 0)
                ->where('c.organisation_id', $authUserOrgId)    
                ->groupBy('c.company_id', 'c.company_name')
                ->orderByDesc('total_assignments')
                ->take(3)
                ->pluck('c.company_name'); // Directly extract the company names

                $companies = $topCompanies->toArray(); // Convert the result to an array if needed

            return [
                'total_companies' => $totalCompanies,
                'new_companies_last_week' => $newCompaniesLastWeek,
                'new_companies_last_month' => $newCompaniesLastMonth,
                'active_companies' => $activeCompanies,
                'top_companies' => $companies
            ];
        } catch (Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return ['error' => __('messages.error.company_error_fetching_stats')];
        }
    }
}
