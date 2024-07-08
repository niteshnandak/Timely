import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { AssignmentService } from '../../../../../services/assignment.service';
import { CompanyService } from '../../../../../services/company.service';
import { CustomersService } from '../../../../../services/customers.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-search-assignment',
  standalone: true,
  imports: [ReactiveFormsModule,CommonModule],
  templateUrl: './search-assignment.component.html',
  styleUrl: './search-assignment.component.css'
})
export class SearchAssignmentComponent {

  searchAssignmentForm!: FormGroup ;
  company_id:any;
  isLoading!:boolean;

  constructor(
    private assignmentService : AssignmentService, 
    private http:HttpClient,
    private auth: AuthService,
    private companyService: CompanyService,
    private customerService: CustomersService
  ) { }

  ngOnInit():void{
    this.initializeFormGroup();

    this.company_id = this.companyService.getStoredCompanyId();
  }

  initializeFormGroup(){
    this.searchAssignmentForm = new FormGroup({
    people_name: new FormControl(''),
    customer_name: new FormControl(''),
    start_date: new FormControl(''),
    end_date: new FormControl(''),
    status: new FormControl(''),
  })
  }

  get peopleName(){
    return this.searchAssignmentForm.get('people_name')
  }
  get customerName(){
    return this.searchAssignmentForm.get('customer_name')
  }
  get startDate(){
    return this.searchAssignmentForm.get('start_date')
  }
  get endDate(){
    return this.searchAssignmentForm.get('end_date')
  }
  get status(){
    return this.searchAssignmentForm.get('status')
  }

  searchAssignmentFormSubmit(){
    this.isLoading=true;
    this.assignmentService
    .searchAssignment(this.company_id, this.searchAssignmentForm.value)
    .subscribe((result:any)=>{
      this.customerService.searchDataChange(result);
    })
  }

  searchAssignmentResetForm(){
    this.searchAssignmentForm.reset();
    this.assignmentService.changeAssignmentSearchState();
  }
}
