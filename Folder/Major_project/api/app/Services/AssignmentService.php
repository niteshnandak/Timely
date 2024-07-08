<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Company;
use App\Models\Customer;
use App\Models\People;
use App\Models\PeopleEmploymentDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssignmentService{

    public function showAssignments($skip, $take, $company_id, $order)
    {
        try{
            // Fetch all assignments
            $assgn = Assignment::where('is_deleted', 0)
            ->where('company_id',$company_id)
            ->get();

            $assignments = Assignment::where('is_deleted', 0)
                ->where('company_id',$company_id)
                ->orderBy('assignment_id', $order)
                ->skip($skip)
                ->take($take)
                ->get();

            // return response()->json([
            //     'test' => $assignments
            // ]);
            $totalAssignments = count($assgn);

            // Append people_name, customer_name, and assignment_number to each assignment
            $assignments = $assignments->map(function ($assignment) {
                $people = People::select('people_name')->where('people_id', $assignment->people_id)->first();
                $customer = Customer::select('customer_name')->where('customer_id', $assignment->customer_id)->first();

                $assignment->people_name = $people ? $people->people_name : null;
                $assignment->customer_name = $customer ? $customer->customer_name : null;
                $assignment->assignment_number = 'ASS' . $assignment->assignment_id;

                return $assignment;

            });
    
            return ['assignments' => $assignments, 'total'=>$totalAssignments];
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }    
        
    }

    public function getPeopleByCompanyId($company_id)
    {
        try{
            // Fetch people names based on the company ID
            $people = PeopleEmploymentDetail::where('company_id', $company_id)
            ->where('is_deleted', 0)
            ->get(['people_id', 'people_name']);
    
            return ['people'=> $people]; 
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }  
       
    }
    public function getCustomersByCompanyId($company_id)
    {
        try{
            // Fetch customer names based on the company ID
            $customers = Customer::where('company_id', $company_id)
            ->where('is_deleted', 0)
            ->get(['customer_id', 'customer_name']);
            
            return ['customers' => $customers ];
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }  
       
    }

    public function createAssignments($data, $user)
    {
        try{
            // Check if the new assignment dates overlap with existing assignments for the same person
            $overlappingAssignment = $this->checkOverlappingAssignment($data['people_name'], $data['start_date'], $data['end_date'], $data['customer_name'], $data['company_id']);
            if ($overlappingAssignment) {
                return ['message' => "Assignment dates overlap with an existing assignment", 'flag' => $overlappingAssignment ];
            }

            $assignment = Assignment::create([
                'organisation_id' => $data['organisation_id'],
                'people_id'=> $data['people_name'],
                'customer_id'=> $data['customer_name'],
                'company_id' => $data['company_id'],
                'start_date'=> $data['start_date'],
                'end_date'=> $data['end_date'],
                'role'=> $data['role'],
                'location'=> $data['location'],
                'description'=> $data['description'],
                'status'=> $data['status'],
                'type'=> $data['type'],
                'is_deleted' => 0,
                'created_at' => now(),
                'created_by' => $user['user_id']
            ]);

            return ['message' => 'Assignment Created Successfully', 'assignment' => $assignment, 'flag' => $overlappingAssignment ];
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } 
    }

    public function searchAssignment($company_id, $searchDatas)
    {
        try{
            $assignment = Assignment::join('people','people.people_id',"=","assignment.people_id")
            ->join('customer','customer.customer_id',"=","assignment.customer_id")
            ->where('customer.company_id', $company_id)
            ->where('assignment.is_deleted', 0)
            ->select(['people.people_name', 'customer.customer_name', 'assignment.status', 'assignment.role', 'assignment.location', 'assignment.start_date', 'assignment.end_date', 'assignment.type']);


            if($searchDatas['customer_name']){
                $assignment->where('customer.customer_name', 'like', '%' . $searchDatas['customer_name'] . '%');
            }

            if($searchDatas['people_name']){
                $assignment->where('people.people_name', 'like', '%' . $searchDatas['people_name'] . '%');
            }

            if($searchDatas['start_date']){
                $assignment->where('assignment.start_date', 'like', '%' . $searchDatas['start_date'] . '%');
            }

            if($searchDatas['end_date']){
                $assignment->where('assignment.end_date', 'like', '%' . $searchDatas['end_date'] . '%');
            }

            if($searchDatas['status']){
                $assignment->where('assignment.status','=', $searchDatas['status']);
            }

            $result = $assignment->get()->toArray();
            $total = $assignment->count();

            return [ 'result' => $result, 'total' => $total ]; 
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }  
    }

    public function editAssignment($assignment_id)
    {
        try{
            $assignment = Assignment::find($assignment_id);
            // $people = People::select('people_name')->where('people_id', $assignment->people_id)->first();
            // $customer = Customer::select('customer_name')->where('customer_id', $assignment->customer_id)->first();
            
            return ['assignment' => $assignment];
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }    
    }

    public function assignmentEditSave($assignment_id, $assignment_data, $user)
    {
        try{
            if(Assignment::find($assignment_id)){
                return $this->updateAssignment($assignment_id, $assignment_data, $user);
            } 
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }     
    }

    public function updateAssignment($assignment_id, $assignment_data, $user)
    {
        try{
            // Check if the updated assignment dates overlap with existing assignments for the same person
            $overlappingAssignment = $this->checkOverlappingAssignment($assignment_data['people_name'], $assignment_data['start_date'], $assignment_data['end_date'], $assignment_data['customer_name'], $assignment_id);
            
            if(!$overlappingAssignment){
                Assignment::where('assignment_id', $assignment_id)->update([
                    'people_id' => $assignment_data['people_name'],
                    'customer_id'=> $assignment_data['customer_name'],
                    'start_date'=> $assignment_data['start_date'],
                    'end_date'=> $assignment_data['end_date'],
                    'role'=> $assignment_data['role'],
                    'location'=> $assignment_data['location'],
                    'description'=> $assignment_data['description'],
                    'status'=> $assignment_data['status'],
                    'type'=> $assignment_data['type'],
                    'updated_at' => now(),
                    'updated_by' =>  $user['user_id']
                ]);
                return ['message' => "Assignment Edited Successfully", 'flag' => $overlappingAssignment];
            }else{
                return ['message' => "Assignment dates overlap with an existing assignment", 'flag' => $overlappingAssignment , 'assignment' => Assignment::find($assignment_id)];
            }
            }catch(\Exception $e){
                return false;
            } 
    }

    private function checkOverlappingAssignment($people_id, $start_date, $end_date, $customer_id, $assignment_id=null) 
    {
        try{
            $dateCollisionCheckStart = Assignment::where([
                'is_deleted' => 0,
                'people_id' => $people_id,
                'customer_id' => $customer_id
            ])->whereBetween('start_date', [$start_date, $end_date]);

            $dateCollisionCheckEnd = Assignment::where([
                'is_deleted' => 0,
                'people_id' => $people_id,
                'customer_id'=> $customer_id
            ])->whereBetween('end_date', [$start_date, $end_date]);

            $dateCollisionCheckBetween = Assignment::where([
                'is_deleted' => 0,
                'people_id' => $people_id,
                'customer_id' => $customer_id
            ])->where('start_date', '<=' ,$start_date)
              ->where('end_date', '>=' ,$end_date);

            if($assignment_id){
                $dateCollisionCheckStart = $dateCollisionCheckStart->where('assignment_id', '!=' ,$assignment_id);
                $dateCollisionCheckEnd = $dateCollisionCheckEnd->where('assignment_id', '!=' ,$assignment_id);
                $dateCollisionCheckBetween = $dateCollisionCheckBetween->where('assignment_id', '!=' ,$assignment_id);
            }

            $dateCollisionCheckEnd = $dateCollisionCheckEnd->first();
            $dateCollisionCheckStart = $dateCollisionCheckStart->first();
            $dateCollisionCheckBetween = $dateCollisionCheckBetween->first();


            if ($dateCollisionCheckEnd || $dateCollisionCheckStart || $dateCollisionCheckBetween) {
                return true;
            }

            return false;
        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }   
    }
            

    public function deleteAssignment($assignment_id)
    {
       try{
            $assignment = Assignment::find($assignment_id);
            $assignment->is_deleted = 1;
            $assignment->save();

            return ['message'=>'Assignment Deleted Succesfully'];
        }catch(\Exception $e){  

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }  
    }

    public function getAssignmentStats($company_id)
    {
        try{

            $company = Company::where('company_id', $company_id)->first();
            $company_name = $company->company_name;

            //week
            $week_startDate = Carbon::now()->subDays(7);
            $week_endDate = Carbon::now();
    
            //month
            $month_startDate = Carbon::now()->startOfMonth();
            $month_endDate = Carbon::now()->endOfMonth();
    

            $assignment = Assignment::where('company_id', $company_id)->where('is_deleted', 0);
            // Log::debug($assignment);

            $assignment_total = $assignment->count('*');
    
            $assignment_in_last_week = $assignment
                            ->whereBetween('created_at', [
                                $week_startDate,
                                $week_endDate
                            ])->count('*');
    
            $assignment_in_last_month = $assignment
                            ->whereBetween('created_at', [
                                $month_startDate,
                                $month_endDate
                            ])->count('*');
    
            $assignment_completed = Assignment::where('company_id', $company_id)
                ->where('status', 'Completed')
                ->where('is_deleted', 0)
                ->get();

            Log::debug($assignment_completed);
    
            $count_assignments_completed = count($assignment_completed);
                    
            $assignment_ongoing = Assignment::where('company_id', $company_id)
                ->where('status', 'Ongoing')
                ->where('is_deleted', 0)
                ->get();
    
            $count_assignments_ongoing = count($assignment_ongoing);
                    
            $result = [
                'company_name' => $company_name,
                'assignment_total' => $assignment_total,
                'assignment_last_week' => $assignment_in_last_week,
                'assignment_last_month' => $assignment_in_last_month,
                'assignment_completed' => $count_assignments_completed,
                'assignment_ongoing' => $count_assignments_ongoing
            ];
    
            return $result;
        }catch(\Exception $e){  

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }   
    }
}