<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    // function to send all data to the front end

    protected $customer_service = null;

    public function showCustomerDatas(Request $request){

        try{
            // Updating the skip and take to get the number of datas for the front end and the customer_id
            $skip = $request->query('skip',0);
            $take = $request->query('take',10);
            $company_id = $request->query('company_id');
            $order = 'desc';


            //sending the datas to the service of the customer to get the datas to send to the front end
            $customers_data = $this->getCustomerService()->getCustomerDatas($skip, $take, $company_id);

            // getting the active customer datas and count of the active customers
            $customers_active = $customers_data['customers'];
            $total = $customers_data['total'];


            // sending the required datas to the front end
            return response()->json([
                'customerActive' => $customers_active,
                'total' => $total
            ], 200);

        } catch(\Exception $e){


            // checking for the error and sending error message to the front end
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // to get the customer stats like the total and new customers
    public function getCutomerStats(Request $request){
        $company_id = $request->query('company_id');
        $customer_stats = $this->getCustomerService()->getCustomerStats($company_id);

        return response()->json($customer_stats);
    }



    // to add the customer to the customer table
    public function addCustomer(Request $request){

        try{

            // validating the incoming data before adding the customer to the table
            DB::beginTransaction();
            $validatedData = $request->validate([
                'customer_data.customerDetails.customer_name' => 'required|string|max:50|min:3',
                'customer_data.customerDetails.email_address' => 'required|email',
                'customer_data.customerDetails.phone_number' => 'required|digits:10',
                'customer_data.customerDetails.customer_vat_percentage' => 'required',
                'customer_data.customerAddressDetails.country' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.pincode' => 'required|digits:6',
                'customer_data.customerAddressDetails.state' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.city' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.address_line_1' => 'required|string|max:100|min:5',
                'customer_data.customerAddressDetails.address_line_2' => 'nullable|string|max:100',
            ]);
            // $this->getCustomerService()->addCustomerData($request);

            $newData = $request->all()['customer_data'];
            $newData['customerDetails']['company_id'] = $request->query('company_id');

            $email_address = $newData['customerDetails']["email_address"];

            $phone_number = $newData['customerDetails']["phone_number"];

            $customer_contact_unique = $this->getCustomerService()->uniqueContact($email_address, $phone_number);

            if(!$customer_contact_unique['flag']){
                return response()->json([
                    'message' => $customer_contact_unique['message']
                ], 400);
            }

            $customer_edit_status = $this->getCustomerService()->postSaveCustomerData($newData);
            DB::commit();

            return response()->json([
                'message' => 'Successfully'
            ], 200);

        }catch(\Exception $e){

            // checking for the errors and handling the errors
            DB::rollBack();
            dd('Hello World');
            return response()->json([
                'message' => 'Failed',
            ], 400);
        }
    }


    // fetch data for edit

    public function fetchEditCustomerData(Request $request){
        try{
            $customer_id = $request->query('customer_id');

            $customer_edit_datas = $this->getCustomerService()->fetchEditCustomerDatas($customer_id);

            return response()->json([
                'customer_data' => $customer_edit_datas,
            ], 200);

        }catch(\Exception $e){

            // capturing the error and sending to the front end
            return response()->json([
                'message' => "error",
            ], 400);
        }
    }

    //edit customers data

    public function editCustomerData(Request $request){

        try{
            DB::beginTransaction();
            // Validate the request data
            $validatedData = $request->validate([
                'customer_data.customerDetails.customer_name' => 'required|string|max:50|min:3',
                'customer_data.customerDetails.email_address' => 'required|email',
                'customer_data.customerDetails.phone_number' => 'required|digits:10',
                'customer_data.customerDetails.customer_vat_percentage' => 'required',
                'customer_data.customerAddressDetails.country' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.pincode' => 'required|digits:6',
                'customer_data.customerAddressDetails.state' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.city' => 'required|string|max:50|min:3',
                'customer_data.customerAddressDetails.address_line_1' => 'required|string|max:100|min:5',
                'customer_data.customerAddressDetails.address_line_2' => 'nullable|string|max:100',
            ]);

            $customer_id = $request->query('customer_id');

            $newData = $request->all()['customer_data'];

            $email_address = $newData['customerDetails']["email_address"];

            $phone_number = $newData['customerDetails']["phone_number"];

            $customer_contact_unique = $this->getCustomerService()->uniqueContact($email_address, $phone_number, $customer_id);

            if(!$customer_contact_unique['flag']){
                return response()->json([
                    'message' => $customer_contact_unique['message']
                ], 400);
            }

            // sending the datas to update to the service of the customer
            $customer_edit_status = $this->getCustomerService()->postSaveCustomerData($newData, $customer_id);
            DB::commit();

            return response()->json([
                'message' => 'Successfully'
            ], 200);

        }catch(\Exception $e){
            DB::rollBack();

            // catching the Exception and sending to the front end
            Log::info($e->getMessage());
            return response()->json([
                'message' => 'Error in Input'
            ], 400);
        }

    }


    //search Customers function

    public function searchCustomer(Request $request){
        try{

            // getting the filters and retrieving the customers based on the search filter from the front end
            $company_id = $request->query('company_id');
            $search_data =$request->all()['customer_data'];
            $customer_datas = $this->getCustomerService()->searchCustomers($search_data, $company_id);

            return response()->json([
                "result" => $customer_datas['customers'],
                "count" => $customer_datas['total']
            ]);

        }catch(\Exception $e){

            // sending the error message
            return response()->json([
                "message" => "error"
            ]);
        }

    }

    //delete operation for the customer page

    public function deleteCustomer(Request $request){
        try{

            // getting customer_id and deleting the customer based on that
            $customer_id = $request->query('customer_id');

            $message = $this->getCustomerService()->deleteCustomer($customer_id);

            return response()->json([
                'message' => 'Successfully'
            ], 200);


        } catch(\Exception $e) {
            // sending the error message
            return response()->json([
                'message'=>'Error'
            ], 400);
        }
    }


    // Function to retrieve the service
    public function getCustomerService(){
        if($this->customer_service == null){
            $this->customer_service = new CustomerService();
        }
        return $this->customer_service;
    }


}
