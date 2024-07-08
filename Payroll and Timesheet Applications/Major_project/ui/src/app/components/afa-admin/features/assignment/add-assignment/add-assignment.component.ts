import { Component } from '@angular/core';
import { AssignmentService } from '../../../../../services/assignment.service';
import { HttpClient } from '@angular/common/http';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { maxYearValidator } from '../../../../../validations/date-validator';
import { Router } from '@angular/router';
import { CustomersService } from '../../../../../services/customers.service';

@Component({
  selector: 'app-add-assignment',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './add-assignment.component.html',
})
export class AddAssignmentComponent {
  addAssignmentForm!: FormGroup ;
  people: any[] = []; // Array to store fetched people names
  customers: any[] = []; // Array to store fetched customer names
  user : any;
  organisation_id : any;
  companyId : any;
  isLoading!:boolean;

  constructor(private assignmentService : AssignmentService, 
    private http:HttpClient,
    private auth: AuthService,
    private toastr: ToastrService,
    private router: Router,
    private customerService: CustomersService) { }

  ngOnInit():void{
    this.initializeFormGroup();
    // Fetch people names based on company ID from session storage
    this.user = this.auth.getUser();
    this.organisation_id = this.user.organisation_id;
    this.companyId = sessionStorage.getItem('companyId');
    
    this.getPeople(this.companyId);
    this.getCustomers(this.companyId);
  }

  initializeFormGroup(){
    this.addAssignmentForm = new FormGroup({
    organisation_id: new FormControl(''),
    company_id: new FormControl(''),
    people_name: new FormControl('',[Validators.required]),
    customer_name: new FormControl('',[Validators.required]),
    start_date: new FormControl('', [Validators.required, maxYearValidator()]),
    end_date: new FormControl('', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/)]),
    role: new FormControl('', [Validators.required, Validators.pattern("^[a-zA-Z\\s]+$"),  Validators.maxLength(50), Validators.minLength(3)]),
    location: new FormControl('', [Validators.required, Validators.pattern("^[a-zA-Z\\s]+$"), Validators.maxLength(50), Validators.minLength(3)]),
    description: new FormControl('', [Validators.required, Validators.maxLength(2000), Validators.minLength(3)]),
    status: new FormControl('', [Validators.required]),
    type: new FormControl('', [Validators.required]),
  })
  }
  

  get peopleName(){
    return this.addAssignmentForm.get('people_name')
  }
  get customerName(){
    return this.addAssignmentForm.get('customer_name')
  }
  get startDate(){
    return this.addAssignmentForm.get('start_date')
  }
  get endDate(){
    return this.addAssignmentForm.get('end_date')
  }
  get role(){
    return this.addAssignmentForm.get('role')
  }
  get location(){
    return this.addAssignmentForm.get('location')
  }
  get description(){
    return this.addAssignmentForm.get('description')
  }
  get status(){
    return this.addAssignmentForm.get('status')
  }
  get type(){
    return this.addAssignmentForm.get('type')
  }

  getPeople(company_id:any){
    this.assignmentService.fetchPeopleNamesByCompanyId(company_id).subscribe({
      next :(response)=>{
        this.people = response.people;
      }
    })
  }
  
  getCustomers(company_id:any){
    this.assignmentService.fetchCustomersNamesByCompanyId(company_id).subscribe({
      next :(response)=>{
        this.customers = response.customers;
      }
    })
  }

  clearInvalidDate(event: any) {
    const assignmentDateControl = this.addAssignmentForm.get('start_date');
    
    if (assignmentDateControl) {
        this.handleInvalidDate(assignmentDateControl, event);
    }
  }

  handleInvalidDate(control: AbstractControl, event: any) {
    if (control && this.isYearInvalid(control.value)) {
        const date = new Date(control.value);
        date.setFullYear(new Date().getFullYear());
        event.target.value = date.toISOString().slice(0, 10);
        control.setValue(event.target.value);
        control.markAsPristine();
    }
  }

  isYearInvalid(dateValue: string): boolean {
    if (dateValue) {
        const selectedYear = new Date(dateValue).getFullYear();
        const currentYear = new Date().getFullYear();
        return selectedYear > currentYear;
    }
    return false;
  }

  patchFormValues() {
    this.addAssignmentForm.patchValue({
      organisation_id: this.organisation_id,
      company_id: this.companyId
    });
  }

  addAssignmentFormSubmit() {
    this.isLoading=true;
    
    this.patchFormValues();
    console.log(this.addAssignmentForm.value)

    this.assignmentService.addAssignment(this.addAssignmentForm.value, this.user).subscribe({
      next: (response)=>{
        console.log('Assignment saved', response);
        this.toastr.success(response["message"]);
        this.addAssignmentForm.reset();
        this.customerService.searchBoxSizeChange();
            this.router.navigate(['/company/company-details/assignments']);
      },error:(result)=>{
        this.toastr.warning(result["error"]["message"]);
      }
          
    });
  }
  
    addAssignmentResetForm(){
    this.addAssignmentForm.reset();
  }

}
