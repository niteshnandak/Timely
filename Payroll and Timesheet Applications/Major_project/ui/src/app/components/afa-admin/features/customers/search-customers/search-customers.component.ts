import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { CompanyService } from '../../../../../services/company.service';
import { CustomersService } from '../../../../../services/customers.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-search-customers',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule
  ],
  templateUrl: './search-customers.component.html',
  styleUrl: './search-customers.component.css'
})
export class SearchCustomersComponent {
  searchCustomerForm !:FormGroup;
  people_id:any;

  addCustomer:any;

  companyId:string | null = null;
  organisationId:string | null = null;

  customerData:any;



  constructor(
    private formbuilder : FormBuilder,
    private route:ActivatedRoute ,
    private peopleService:PeopleService,
    private companyService: CompanyService,
    private customerService: CustomersService,
    private router: Router,
  ){}

  ngOnInit(){

    // creating the search customer form
    this.searchCustomerForm = this.formbuilder.group({
        customer_name:[''],
        email_address:[''],
        phone_number:[''],
    })
  }

  searchCustomerSubmit(){

    // getting the company id
    this.companyId = this.companyService.getStoredCompanyId();


    // findin the customers with serach and the company_id for filter
    this.customerService
    .searchCustomers(
      this.companyId,
      this.searchCustomerForm.value
    )
    .subscribe((result:any)=>{
      this.customerData = result;
      console.log(this.customerData);
      this.customerService.searchDataChange(this.customerData);
    })
  }

  resetForm() {
    this.searchCustomerForm.reset();
  }
}
