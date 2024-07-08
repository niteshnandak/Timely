<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Assignment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class CustomerService
{

    // to get the datas of the company
    public function getCustomerDatas($skip, $take, $company_id){

        // gettting all the cusrtomer datas of the company based on the company_id
        $customers = Customer::join('address', 'customer.address_id', '=', 'address.address_id')
        ->leftJoin('invoice', 'customer.customer_id', '=', 'invoice.customer_id')
        ->where('customer.company_id', $company_id)
        ->where('customer.is_deleted', 0)
        ->orderBy('customer.customer_id', 'desc')
        ->groupBy(
            'customer.customer_id',
            'customer.customer_name',
            'customer.email_address',
            'customer.phone_number',
            'customer.no_of_assignments',
            'address.city'
        )
        ->selectRaw(
            'customer.customer_id,
            customer.customer_name,
            customer.email_address,
            customer.phone_number,
            customer.no_of_assignments,
            address.city,
            COUNT(CASE WHEN invoice.payroll_status = "pending" and invoice.is_deleted = 0 THEN invoice.invoice_id ELSE NULL END) as invoice_count'
        );

        // counting the total number of customers
        $total = sizeof($customers->get()->toArray());

        $customers = $customers
                        ->skip($skip)
                        ->take($take)
                        ->get()
                        ->toArray();

        Log::info($customers);

        return ['customers' => $customers, 'total' => $total];


    }

    public function getCustomerStats($company_id){

        //week dates
        $week_startDate = Carbon::now()->subDays(7);
        $week_endDate = Carbon::now();

        //month dates
        $month_startDate = Carbon::now()->startOfMonth();
        $month_endDate = Carbon::now()->endOfMonth();

        $customer = Customer::where('company_id', $company_id)
        ->where('is_deleted', 0);


        //getting total customer count
        $customer_total = $customer->count('*');

        // getting new customers in past week count
        $customer_in_last_week = $customer
                        ->whereBetween('created_at', [
                            $week_startDate,
                            $week_endDate
                        ])->count('*');


        // getting new customers in past month count
        $customer_in_last_month = $customer
                        ->whereBetween('created_at', [
                            $month_startDate,
                            $month_endDate
                        ])->count('*');


        // getting all the customers who all are available in the company
        $customer_active_count = $customer
        ->count('*');

        //getting how many customers left the company
        $customer_inactive_count = $customer_total- $customer_active_count;

        // top 3 customers based on the assignments
        $top_customers = DB::table('assignment as a')
        ->select('c.customer_id', 'c.customer_name', DB::raw('COUNT(a.customer_id) as total_assignments'))
        ->join('customer as c', 'c.customer_id', '=', 'a.customer_id')
        ->where('c.company_id', $company_id)
        ->where('c.is_deleted', 0)
        ->where('a.is_deleted', 0)
        ->groupBy('c.customer_id', 'c.customer_id')
        ->orderByDesc('total_assignments')
        ->take(3)
        ->pluck('c.customer_name'); // Directly extract the company names


        // sending the retrieved datas to the controller
        $result = [
            'customer_total' => $customer_total,
            'customer_last_week' => $customer_in_last_week,
            'customer_last_month' => $customer_in_last_month,
            'customer_active' => $customer_active_count,
            'customer_inactive' => $customer_inactive_count,
            'top_customers' => $top_customers
        ];

        return $result;
    }

    // function to get the datas of customer selected for the edit
    public function fetchEditCustomerDatas($customer_id){
        $customer_datas = Customer::join('address', 'customer.address_id', '=', 'address.address_id')
        ->where('customer.customer_id', $customer_id)
        ->first()
        ->toArray();

        return $customer_datas;
    }

    // combining the add and edit function of the customer table
    public function postSaveCustomerData($newData, $customer_id=null){
        $authUser= Auth::user();
        $authUser_id = $authUser->user_id;
        $authUser_org_id = $authUser->organisation_id;

        // for updated by
        $newData['customerDetails']['updated_by'] = $authUser_id;
        $newData['customerAddressDetails']['updated_by'] = $authUser_id;


        //conditon for update or create with the availability of the customer_id
        if($customer_id){

            // updating the customer and getting the address_id to update the address of the customer
            $customer = Customer::where('customer_id', $customer_id)
            ->first();

            $address_id = $customer->address_id;

            $customer->update($newData['customerDetails']);

            $address = Address::where('address_id', $address_id)
            ->first()
            ->update($newData['customerAddressDetails'])
            ;

        }
        else{
            // add the address information in the address table
            $newData['customerAddressDetails']['created_by'] = $authUser_id;
            $addressTable = Address::create($newData['customerAddressDetails']);
            $addressId = $addressTable->address_id;

            // add missing information in the customerDetail Array
            $newData['customerDetails']['created_by'] = $authUser_id;
            $newData['customerDetails']['organisation_id'] = $authUser_org_id;
            $newData['customerDetails']['address_id'] = $addressId;
            $customerTable = Customer::create($newData['customerDetails']);

            return response()->json(['message'=>'success']);
        }


    }


    // code to retrieve the serached customer based on the filters
    public function searchCustomers($request, $company_id){

        // getting the filters from the front end request
        $customer_name = $request['customer_name'];
        $customer_email = $request['email_address'];
        $customer_phone_number = $request['phone_number'];

        // joining the data and sending the searched data to the front end
        $customer = Customer::join('address', 'address.address_id', '=', 'customer.address_id')
        ->where('customer.company_id', $company_id);

        if($customer_name){
            $customer->where('customer.customer_name','like',$customer_name. "%");
        }
        if($customer_email){
            $customer->where('customer.email_address','like',$customer_email. "%");
        }
        if($customer_phone_number){
            $customer->where('customer.phone_number','like',$customer_phone_number. "%");
        }

        // selecting only the required datas out of all the datas to retrieved
        $customers = $customer->select(
            'customer.customer_id',
            'customer.customer_name',
            'customer.email_address',
            'customer.phone_number',
            'customer.no_of_assignments',
            'address.city'
        );

        return [
            'customers'=>$customers->get()->toArray(),
            'total' =>$customers->count('*')
    ];
    }


    // check whether the mail and phone number are unique or not

    public function uniqueContact($mail_id, $phone_number, $customer_id = null){


        // getting the customers with the email id and phone number
        $unique_flag_mail = Customer::where('email_address',$mail_id);
        $unique_flag_phone = Customer::where('phone_number',$phone_number);

        // used to check whether the mail belongs to the user itself if from edit
        if($customer_id){
            $unique_flag_mail = $unique_flag_mail->where('customer_id',"!=",$customer_id);
            $unique_flag_phone = $unique_flag_phone->where('customer_id',"!=",$customer_id);
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


    // function to delete a customer
    public function deleteCustomer($customer_id){

        $authUser = Auth::user();
        $authUser_id = $authUser->user_id;

        // selecting the customer to delete and deleting the cutomer
        $customer = Customer::where('customer_id', $customer_id)
        ->first()
        ->update([
            'is_deleted' => true,
            'updated_by' => $authUser_id
        ]);

        return response()->json([
            'message' => 'success'
        ]);
    }
}
