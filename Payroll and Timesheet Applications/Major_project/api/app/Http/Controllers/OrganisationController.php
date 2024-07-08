<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrganisationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;



class OrganisationController extends Controller
{
    protected $orgService;

    //Call to the getDashboardData in OrgService
    public function index(Request $request){

        try {
            $user_id = $request->id ? (int) $request->id : 9;
            $data = $this->getOrgService()->getDashboardData($user_id);
            return response()->json($data);

        } catch (\Exception $e) {

            Log::error("error at OrgController/index", $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    //Organisation Initial Informationto fill in the Org Settings form
    public function orgInitialInfo(Request $request){

        try{

            $org_id = $request->org_id ? (int) $request->org_id : 0;
            $data = $this->getOrgService()->getOrganisationData($org_id);
            return response()->json($data);

        } catch (\Exception $e){
            Log::error("error at OrgController/orgInitialInfo", $e->getMessage());
            return response()->json('An Error occured when processing your request');
        }
    }

    //Function to store edited organisation detail.
    public function editOrgInfo(Request $request, $id){

        $request->validate([
            'data.name' => 'required|string',
            'data.email_address' => 'required|email',
            'data.contact_number' => 'required|string',
            'data.address_line_1' => 'required|string',
            'data.city' => 'required|string',
            'data.state' => 'required|string'
        ]);

        $data = $request->input('data');

        $email = $data['email_address'];
        $contact = $data['contact_number'];
        $user_id = $data['user_id'];

        $uniqueDetailsFlags = $this->getOrgService()->uniqueContact($email, $contact, $user_id);

        $duplicates = [];
        foreach($uniqueDetailsFlags as $flag => $value){
            if($value){
                array_push($duplicates, $flag);
            }
        }
        $duplicates = implode(', ',$duplicates);

        if(!empty($duplicates)){
            return response()->json([
                'message' => $duplicates.' already taken',
                'flags' => explode(', ', $duplicates)
            ]);
        }

        $responseData = $this->getOrgService()->editOrganisationDetials($data, $id);

        //$responseData is returned as boolean, condition to return Response.
        if($responseData){
            return response()->json(['message'=>Config::get('message.success.organisation_edit_success')], 200);
        } else {
            return response()->json(['message' => Config::get('message.error.organisation_edit_error')]);
        }
    }

    // Function for Viewing the Logo
    public function showLogo($filename){

        $response = $this->getOrgService()->showLogoImage($filename);
        return $response;
    }


    // Function to upload the logo for the Organisation
    public function uploadOrgLogo(Request $request){

        //Validate the file sent with the Request
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        try{
            $org_id = $request->input('org_id');
            $uploadedFile = $request->file('file');
            $data = $this->getOrgService()->uploadLogo($org_id, $uploadedFile);

            return $data;

        } catch (\Exception $e) {
            Log::error("Exception at orgController/upload: ", $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }


    // Function to Create new Instance of Organisation Service
    private function getOrgService()
    {
        if ($this->orgService == null) {
            $this->orgService = new OrganisationService();
        }
        return $this->orgService;
    }
}
