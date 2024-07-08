import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { CompanyService } from '../../../../../services/company.service';
import { CustomersService } from '../../../../../services/customers.service';
import { Router } from '@angular/router';
import { state } from '@angular/animations';
import { ToastrService } from 'ngx-toastr';
import { CharOnlyDirective } from '../../../../../directive/char-only/char-only.directive';
import { CharWithSpaceDirective } from '../../../../../directive/char-with-space/char-with-space.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { OrganisationService } from '../../../../../services/organisation.service';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { SomeCharsDirective } from '../../../../../directive/some-chars-only/some-chars.directive';

@Component({
  selector: 'app-add-customer',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CharOnlyDirective,
    CharWithSpaceDirective,
    NumberOnlyDirective,
    SomeCharsDirective
  ],
  templateUrl: './add-customer.component.html',
  styleUrl: './add-customer.component.css'
})
export class AddCustomerComponent {
  addCustomerForm !:FormGroup;
  people_id:any;
  companyId:string | null = null;

  constructor(
    private formbuilder : FormBuilder,
    private route:ActivatedRoute ,
    private peopleService:PeopleService,
    private companyService: CompanyService,
    private customerService: CustomersService,
    private router: Router,
    private toastr: ToastrService,
    private auth: AuthService
  ){}

  ngOnInit(){

    // creating a form for adding the customer
    this.addCustomerForm = this.formbuilder.group({
      customerDetails: this.formbuilder.group({
        customer_name: ['', [Validators.required, Validators.maxLength(50),Validators.minLength(3)]],
        email_address: ['', [Validators.required, Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$')]],
        phone_number: ['', [Validators.required, Validators.minLength(10), Validators.maxLength(10)]],
        customer_vat_percentage: ['', [Validators.required]]
      }),

      customerAddressDetails: this.formbuilder.group({
        country: ['', [Validators.required, Validators.maxLength(50),Validators.minLength(3)]],
        pincode: ['', [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(6), Validators.maxLength(6)]],
        state: ['', [Validators.required, Validators.maxLength(50),Validators.minLength(3)]],
        city: ['', [Validators.required, Validators.maxLength(50),Validators.minLength(3)]],
        address_line_1: ['', [Validators.required, Validators.maxLength(100),Validators.minLength(5)]],
        address_line_2: ['', [Validators.maxLength(100)]],
      }),

    })


    // getting the company_id from the service
    this.companyId = this.companyService.getStoredCompanyId();
  }

  addCustomerSubmit(){

    // checking if the form is valid or not with validators
    if(this.addCustomerForm.valid){

      // sending the formData to the backend to add the customer.
      this.customerService
      .addCustomerData(
        this.companyId,
        this.addCustomerForm.value
      )
      .subscribe({
        next:(result:any)=>{
          this.toastr.success(result["message"], 'Customer Added');
        },
        error:(result:any)=>{
          this.toastr.error(result["error"]["message"]);
        }
      })

      this.customerService.searchBoxSizeChange();

      this.router.navigateByUrl('company/company-details/customers');
    }
    else{
      // if the form is not valid showing the error when clicked
      this.addCustomerForm.markAllAsTouched();
    }
  }
  resetForm() {
    this.addCustomerForm.reset();
  }

}
