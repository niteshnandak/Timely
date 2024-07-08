import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { AssignmentService } from '../../../../../services/assignment.service';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { ToastrService } from 'ngx-toastr';
import { maxYearValidator } from '../../../../../validations/date-validator';
import { CustomersService } from '../../../../../services/customers.service';
import { LoaderComponent } from '../../../../loader/loader.component';


@Component({
  selector: 'app-edit-assignment',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, LoaderComponent],
  templateUrl: './edit-assignment.component.html',
  styleUrl: './edit-assignment.component.css'
})
export class EditAssignmentComponent {
  editAssignmentForm!: FormGroup ;
  people: any[] = []; // Array to store fetched people names
  customers: any[] = []; // Array to store fetched customer names
  companyId : any;
  assignment_id: any;
  assignmentData: any;
  // personName: any;
  // custName: any;
  user: any;
  isLoading!:boolean;
  peopleNameDisabled : boolean = true;

  constructor(private assignmentService : AssignmentService, 
    private http:HttpClient,
    private route:ActivatedRoute,
    private auth: AuthService,
    private router:Router,
    private toastr:ToastrService,
    private customerService:CustomersService,
    ) {
      route.params.subscribe(val => {
        this.assignment_id = this.route.snapshot.params['id'];
        this.getEditAssignmentData(this.assignment_id);
        // this.getPeople(this.companyId);
        // this.getCustomers(this.companyId);
      });
     }

    ngOnInit():void{
      this.initializeFormGroup();
      this.user = this.auth.getUser();
      this.companyId = sessionStorage.getItem('companyId');
      this.assignment_id = this.route.snapshot.params['id'];
      this.getEditAssignmentData(this.assignment_id);
      this.user = this.auth.getUser();

      this.getPeople(this.companyId);
      this.getCustomers(this.companyId);
    }

    initializeFormGroup(){
      this.editAssignmentForm = new FormGroup({
      people_name: new FormControl('',[Validators.required]),
      customer_name: new FormControl('',[Validators.required]),
      start_date: new FormControl('', [Validators.required, maxYearValidator()]),
      end_date: new FormControl('', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/)]),
      role: new FormControl('', [Validators.required, Validators.pattern("^[a-zA-Z\\s]+$"), Validators.maxLength(50), Validators.minLength(3)]),
      location: new FormControl('', [Validators.required, Validators.pattern("^[a-zA-Z\\s]+$"), Validators.maxLength(50), Validators.minLength(3)]),
      description: new FormControl('', [Validators.required, Validators.maxLength(2000), Validators.minLength(3)]),
      status: new FormControl('', [Validators.required]),
      type: new FormControl('', [Validators.required]),
    })
    }
    
  
    get peopleName(){
      return this.editAssignmentForm.get('people_name')
    }
    get customerName(){
      return this.editAssignmentForm.get('customer_name')
    }
    get startDate(){
      return this.editAssignmentForm.get('start_date')
    }
    get endDate(){
      return this.editAssignmentForm.get('end_date')
    }
    get role(){
      return this.editAssignmentForm.get('role')
    }
    get location(){
      return this.editAssignmentForm.get('location')
    }
    get description(){
      return this.editAssignmentForm.get('description')
    }
    get status(){
      return this.editAssignmentForm.get('status')
    }
    get type(){
      return this.editAssignmentForm.get('type')
    }

    editAssignmentFormSubmit(){
      this.isLoading=true;
      console.log(this.editAssignmentForm.value);
      this.assignmentService.saveEditAssignmentData(this.assignment_id, this.editAssignmentForm.value, this.user).subscribe({
        next: (response) => {
            this.router.navigateByUrl('/',{skipLocationChange: true}).then(()=>{
              this.router.navigate(['/company/company-details/assignments']);
            })
            console.log(response);
            const res = response;
            this.toastr.success(res["message"]); 
        },
          error:(result:any)=>{
          this.toastr.warning(result["error"]["message"]);
        }
      })
      this.customerService.searchBoxSizeChange();
      this.isLoading=false;
    }
    
    getEditAssignmentData(assignment_id:any){
      this.isLoading =true;
      this.assignmentService.getEditAssignmentData(assignment_id).subscribe({
        next: (response) => {
          this.assignmentData = response.assignment;
          // this.personName = response.people_name;
          // this.custName = response.customer_name;
          console.log(this.assignmentData);
          this.editAssignmentForm?.patchValue({
            people_name: this.assignmentData['people_id'],
            customer_name: this.assignmentData['customer_id'],
            // people_name: this.personName['people_name'],
            // customer_name: this.custName['customer_name'],
            start_date: this.assignmentData['start_date'],
            end_date: this.assignmentData['end_date'],
            role: this.assignmentData['role'],
            location: this.assignmentData['location'],
            description: this.assignmentData['description'],
            status: this.assignmentData['status'],
            type: this.assignmentData['type'],
          });
          this.isLoading=false
        }
      })
    }

    getPeople(company_id:any){
      this.assignmentService.fetchPeopleNamesByCompanyId(company_id).subscribe({
        next :(response)=>{
          this.people = response.people;
        }
      })
    }

    getCustomers(company_id:any) {
      this.assignmentService.fetchCustomersNamesByCompanyId(company_id).subscribe({
        next :(response)=>{
          this.customers = response.customers;
        }
      })
    }

    closeEdit(){
      // minimizing the box if the edit part is aborted
      this.customerService.searchBoxSizeChange();
  
      this.router.navigateByUrl('company/company-details/assignments');
    }
}
