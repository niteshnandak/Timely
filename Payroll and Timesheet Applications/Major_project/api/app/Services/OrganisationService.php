<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organisation;
use App\Models\Assignment;
use App\Models\User;
use App\Models\Company;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class OrganisationService {

  /**
   * Function to get Dashboard data required by the dashboard
   */
  public function getDashboardData(int $user_id){
    try {

      //Fetch Organisation ID by User Id
      $org_id = User::where('user_id', $user_id)->value('organisation_id');

      // Find the User by ID and set User Name
      $user = User::find($user_id);
      if ($user) {
          $user = $user->toArray();
          $user_name = $user['firstname'] . ' ' . $user['surname'];
      } else {
          $user_name = "User Name";
      }

      // Find the Organisation by ID and set Organisation Name
      $organisation = Organisation::find($org_id);
      if ($organisation) {
          $organisation = $organisation->toArray();
          $org_name = $organisation['name'];
          $org_logo = $organisation['org_logo'];
      } else {
          $org_name = "Organisation Name";
          $org_logo = null;
      }

      //Get the Count of Assignments for the Organisation
      $assignments = Assignment::where('organisation_id', $org_id)
                            ->where('is_deleted',0)
                            ->get();
      $total_assignments_per_org = count($assignments);

      //Get the count of Companies for the Organisation
      $companies = Company::where('organisation_id', $org_id)
                          ->where('is_deleted',0)
                          ->get();
      $total_companies_per_org = count($companies);

      //Call to the Private Functions to fetch Line chart and stack chart data
      $lineChartData = $this->getLineChartData($org_id);
      $stackChartData = $this->getStackChartData($org_id);

      //Combine all the variables and send it to the controller

      $returnItems = [
        'lineChartData' => $lineChartData,
        'stackChartData' => $stackChartData,
        'total_companies' => $total_companies_per_org,
        'total_assignments' => $total_assignments_per_org,
        'org_id' => $org_id,
        'org_name' => $org_name,
        'user_name' => $user_name,
        'org_logo' => $org_logo
      ];
      return $returnItems;

  } catch (\Exception $e) {
      Log::error('Error in getDashboardData: ' . $e->getMessage());
      throw $e;
  }
}

  private function getLineChartData(int $org_id)
  {

    //Fill months in the assignment data like [1=>0, 2=>0,....12=>0]
    //array-format:  [month_no => no.of assignments]
    $assignmentData = array_fill(1, 12, 0);
    $currentYear = date("Y"); // Current year

    /*
    * Retrieve the monthly count of assignments for a specific organization in the current year.
    *
    * Actions:
    * 1. Selects the month from 'start_date' and counts assignments.
    * 2. Filters by organization ID ($org_id) and the current year.
    * 3. Groups results by month.
    * 4. Executes the query and retrieves the results.
    *
    * This helps track the number of assignments started each month within the current year.
    */
    $assignments = Assignment::select(
        DB::raw('MONTH(start_date) as month'),
        DB::raw('COUNT(assignment_id) as assignments')
    )
        ->where('organisation_id', $org_id)
        ->where(DB::raw('YEAR(start_date)'), '=', $currentYear)
        ->where('is_deleted',0)
        ->groupBy(DB::raw('MONTH(start_date)'))
        ->get();

    // Set the no.of assignments in $assignmentData
    foreach ($assignments as $assignment) {
        $month = $assignment->month;
        $noOfAssignments = $assignment->assignments;
        $assignmentData[$month] = $noOfAssignments;
    }

    return array_values($assignmentData);
  }


  //Function to get Stack Chart data
  public function getStackChartData(int $org_id)
  {
    /*
    * Retrieve the count of assignments by status (pending, ongoing, completed)
    * for each company within a specific organization.
    *
    * Actions:
    * 1. Selects company names and counts assignments by status using CASE for zero counts.
    * 2. Joins 'assignment' (a) with 'company' (c) on 'company_id'.
    * 3. Filters by organization ID ($org_id) and is_deleted = 0.
    * 4. Groups by company name.
    * 5. Limits results to top 5 companies.
    */

    $companyAssignmentCounts = DB::table('assignment AS a')
        ->select(
            'c.company_name',
            DB::raw('SUM(CASE WHEN a.status = "pending" THEN 1 ELSE 0 END) AS pending'),
            DB::raw('SUM(CASE WHEN a.status = "ongoing" THEN 1 ELSE 0 END) AS ongoing'),
            DB::raw('SUM(CASE WHEN a.status = "completed" THEN 1 ELSE 0 END) AS completed')
        )
        ->join('company AS c', 'a.company_id', '=', 'c.company_id')
        ->where('c.organisation_id', $org_id)
        ->where('a.is_deleted',0)
        ->orderBy(DB::raw('SUM(a.assignment_id)'), 'desc')
        ->groupBy('c.company_name')
        ->limit(5)
        ->get();

    //Variables to set data for stack chart
    $companies = [];
    $statusData = [
      'completed' => [],
      'ongoing' => [],
      'pending' => []
    ];

    // Set the Params in the variables defined above
    foreach ($companyAssignmentCounts as $count) {
      $companies[] = $count->company_name;
      $statusData['completed'][] = $count->completed;
      $statusData['ongoing'][] = $count->ongoing;
      $statusData['pending'][] = $count->pending;
    }


    //Data for stacks of each bar
    $statusConfigs = [
      'completed' => [
          'label' => 'Completed',
          'backgroundColor' => 'rgb(102, 205, 102)'
      ],
      'ongoing' => [
          'label' => 'Ongoing',
          'backgroundColor' => 'rgb(255, 179, 128)'
      ],
      'pending' => [
          'label' => 'Pending',
          'backgroundColor' => 'rgb(255, 182, 193)'
      ]
    ];

    // Building the datasets array dynamically with constant barThickness
    $datasets = [];
    foreach ($statusConfigs as $status => $config) {
        $datasets[] = [
            'label' => $config['label'],
            'data' => $statusData[$status],
            'backgroundColor' => $config['backgroundColor'],
            'barThickness' => 25
        ];
    }

    $stackChartData = [
      'labels' => $companies,
      'datasets' => $datasets
    ];

    return $stackChartData;
  }

  //To fetch Organisation Details to be filled in the Org Settings Form
  public function getOrganisationData(int $org_id){
    try{
      $organisationWithAddress = DB::table('organisation')
        ->join('address', 'organisation.address_id', '=', 'address.address_id')
        ->where('organisation.organisation_id', $org_id)
        ->first();

      return $organisationWithAddress;

    } catch (\Exception $e) {
      Log::error('Error in orgInitialInfo: ' . $e->getMessage());
      throw $e;
    }
  }

  //After Submitting the Organisation Settings Form, Updating the data in Organisation -
  // - and Address tables
  public function editOrganisationDetials($data, $id){

    //Get the Authenticated User's ID
    $authUser = Auth::user();
    $authUserId = $authUser->user_id;
    $authUserOrgId = $authUser->organisation_id;

    try{
      $organisation = Organisation::find($id);
      $address_id = $organisation->address_id;
      if($organisation){
          $organisation->name = $data['name'];
          $organisation->email_address = $data['email_address'];
          $organisation->contact_number = $data['contact_number'];
          $organisation->description = $data['description'];
          $organisation->updated_by = $authUserId;
          $organisation->updated_at = now();
          $organisation->save();
      }

      $address = Address::where('address_id', $address_id)->first();
      if($address){
          $address->address_line_1 = $data['address_line_1'];
          $address->address_line_2 = $data['address_line_2'];
          $address->city = $data['city'];
          $address->state = $data['state'];
          $address->updated_by = $authUserId;
          $address->updated_at = now();
          $address->save();
      }
      return true;
    } catch (\Exception $e){
      Log::error("Error in orgService/editOrganisationDetials:", $e->getMessage());
      return false;
    }
  }


  //To create a route for the image to view the Image hosted at a Localhost route
  public function showLogoImage($filename){
    try{
      //Fetch the image name and define the path

      $path = 'images/' . $filename;
      if (!Storage::disk('public')->exists($path)) {
          return response()->json(['message' => 'File not found.'], 404);
      }

      // Set file path and mimeType
      $file = Storage::disk('public')->get($path);
      $mimeType = Storage::disk('public')->mimeType($path);

      // Send a new Response() object with $file as content
      return new Response($file, 200, ['Content-Type' => $mimeType]);

    } catch (\Exception $e){
      Log::error("Error in orgService/showLogoImage:", $e->getMessage());
    }
  }


  //Uploading the Logo from the Organisation Settings page
  public function uploadLogo($org_id, $uploadedFile){
    try{

      $originalFileName = $uploadedFile->getClientOriginalName();

      //Store the image in storage/public/images
      $path = $uploadedFile->storeAs('images', $originalFileName, 'public');

      //Set the File Name in DB in org_logo column
      $organisation = Organisation::where('organisation_id', $org_id)
          ->update(['org_logo' => $originalFileName]);

      return response(['message' => Config::get('message.success.org_logo_success')]);

    } catch (\Exception $e){
      Log::error("Exception at orgService/uploadLogo:",$e->getMessage());
    }
  }

  public function uniqueContact($email, $contact, $user_id){

    $org_id = User::where('user_id',$user_id)->value('organisation_id');

    $email_exists = Organisation::where('organisation_id','!=', $org_id)
                            ->where('email_address', $email)
                            ->exists();

    $contact_exists = Organisation::where('organisation_id', '!=', $org_id)
                            ->where('contact_number',$contact)
                            ->exists();
    $duplicate_flags = [
        'Email Address' => $email_exists,
        'Contact Number' => $contact_exists
    ];

    return $duplicate_flags;

  }
}
