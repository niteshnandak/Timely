<?php
 
namespace App\Http\Controllers;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    protected $assignmentService = null;

    public function showAssignments(Request $request)
    {
        try{
            $skip = $request->query('skip',0);
            $take = $request->query('take',10);
            $company_id = $request->query('company_id');
            $order = 'desc';
            $show_assignments = $this->getAssignmentService()->showAssignments($skip, $take, $company_id, $order);
            $assignments= $show_assignments['assignments'];
            $total = $show_assignments['total'];

            return response()->json([
                'assignments' => $assignments,
                'total' => $total
            ]);

        }catch(\Exception $e){  

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } 
    }
 
    public function getPeopleByCompanyId(Request $request)
    {
        try{
            $company_id = $request->companyId;
            $get_people_by_company_id = $this->getAssignmentService()->getPeopleByCompanyId($company_id);
            $people = $get_people_by_company_id['people'];
    
            return response()->json([
                'people' => $people
            ]);

        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }       
    }
 
    public function getCustomersByCompanyId(Request $request)
    {
        try{
            $company_id = $request->companyId;
            $get_customers_by_company_id = $this->getAssignmentService()->getCustomersByCompanyId($company_id);
            $customers = $get_customers_by_company_id['customers'];

            return response()->json([
                'customers' => $customers
            ]);

        }catch(\Exception $e){  
            
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } 
    }
 
    public function createAssignments(Request $request)
    {
        try{
            $data = $request->data;
            $user = $request->user;

            $request->validate([
                'data.organisation_id' => 'required',
                'data.people_name' => 'required',
                'data.customer_name' => 'required',
                'data.company_id' => 'required',
                'data.start_date' => 'required',
                'data.end_date' => 'required',
                'data.role' => 'required',
                'data.location' => 'required',
                'data.description' => 'required',
                'data.status' => 'required',
                'data.type' => 'required',
            ]);

            $create_assignments = $this->getAssignmentService()->createAssignments($data, $user);

            $message = $create_assignments['message'];
            $flag = $create_assignments['flag'];

            if($flag){
                return response()->json([
                    'message' => $message,
                    'flag' => $flag
                ], 400);
            }

            $assignment = $create_assignments['assignment'];

            return response()->json([
                'message' => $message,
                'assignment' => $assignment,
                'flag' => $flag
            ]);
        }catch(\Exception $e){

            return response()->json([
                'message' => 'Failed to create assignment'
            ], 400);
        }      
    }

    public function searchAssignment(Request $request)
    {
        try{
            $company_id = $request->query('company_id');
            $searchDatas = $request->all()['data'];
            $search_assignment = $this->getAssignmentService()->searchAssignment($company_id, $searchDatas);
            $result = $search_assignment['result'];
            $total = $search_assignment['total'];

            return response()->json([
                'result' => $result,
                'total' => $total,
                'search Data '=>$searchDatas
            ]);

        }catch(\Exception $e){

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } 
    }

    public function editAssignment(Request $request)
    {
        try{
            $assignment_id = $request->assignment_id;
            $edit_assignment = $this->getAssignmentService()->editAssignment($assignment_id);
            $assignment = $edit_assignment['assignment'];
    
            return response()->json([
                'assignment' => $assignment,
            ]);
        }catch(\Exception $e){

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function assignmentEditSave(Request $request)
    {
        try {
            $assignment_id = $request->assignment_id;
            $assignment_data = $request->assignment_data;
            $user = $request->user;
            $assignment_edit_save = $this->getAssignmentService()->assignmentEditSave($assignment_id, $assignment_data, $user);
            if ($assignment_edit_save) {
                $message = $assignment_edit_save['message'];
                $flag = $assignment_edit_save['flag'];
                if($flag){
                    return response()->json([
                        'message' => $message,
                        'flag' => $flag
                    ], 400);
                }else{
                    return response()->json([
                        'message' => $message,
                        'flag' => $flag
                    ], 200);  
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to edit assignment'
            ]);
        }
        
    }

    public function deleteAssignment(Request $request)
    {
        try{
            $assignment_id = $request->assignment_id;
            $delete_assignment = $this->getAssignmentService()->deleteAssignment($assignment_id);
            $message = $delete_assignment['message'];

            return response()->json([
                'message' => $message,
            ]);

        }catch(\Exception $e){

            return response()->json([
                'message' => 'Failed to delete assignment'
            ], 400);
        } 
    }

    public function getAssignmentStats(Request $request, $company_id)
    {
        try{
            $assignment_stats = $this->getAssignmentService()->getAssignmentStats($company_id);
        
            return response()->json($assignment_stats);
        }catch(\Exception $e){

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } 
    }

    private function getAssignmentService(){
        if ($this->assignmentService == null) {
            $this->assignmentService = new AssignmentService();
        }
        return $this->assignmentService;
    }
    
}