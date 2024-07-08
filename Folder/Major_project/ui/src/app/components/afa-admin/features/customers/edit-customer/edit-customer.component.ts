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
import { CharWithSpaceDirective } from '../../../../../directive/char-with-space/char-with-space.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { CharOnlyDirective } from '../../../../../directive/char-only/char-only.directive';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../../loader/loader.component';
import { SomeCharsDirective } from '../../../../../directive/some-chars-only/some-chars.directive';

@Component({
  selector: 'app-edit-customer',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    CharWithSpaceDirective,
    NumberOnlyDirective,
    CharOnlyDirective,
    LoaderComponent,
    SomeCharsDirective
  ],
  templateUrl: './edit-customer.component.html',
  styleUrl: './edit-customer.component.css'
})
export class EditCustomerComponent {

  // required variables to edit the customer datas
  editCustomerForm !:FormGroup;
  people_id:any;
  companyId:string | null = null;
  organisationId:string | null = null;
  customer_id:any;
  customer_datas:any;
  isLoading = true;

  constructor(
    private formbuilder : FormBuilder,
    private route:ActivatedRoute ,
    private peopleService:PeopleService,
    private companyService: CompanyService,
    private customerService: CustomersService,
    private router: Router,
    private toastr: ToastrService,
    private authService: AuthService,
  ){
    route.params.subscribe(val => {
      this.isLoading = true;
      this.customer_id = this.route.snapshot.params['id'];
      this.getCustomerData(this.customer_id);
    });
  }

  ngOnInit(){

    // getting the customer_id to edit the customer datas
    this.customer_id = this.route.snapshot.params['id'];

    // retrieving the customer from the backend which need to be edited
    this.getCustomerData(this.customer_id);

    // creating the customer form
    this.editCustomerForm = this.formbuilder.group({
      customerDetails: this.formbuilder.group({
        customer_name: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
        email_address: ['', [Validators.required, Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$')]],
        phone_number: ['', [Validators.required, Validators.maxLength(10), Validators.minLength(10)]],
        customer_vat_percentage: ['', [Validators.required]]
      }),
      customerAddressDetails: this.formbuilder.group({
        country: ['', [Validators.required]],
        pincode: ['', [Validators.required, Validators.maxLength(6), Validators.minLength(6)]],
        state: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
        city: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
        address_line_1: ['', [Validators.required, Validators.maxLength(100), Validators.minLength(5)]],
        address_line_2: ['', [Validators.maxLength(100)]]
      })

    })

  }

  editCustomerSubmit(){

    // checking if the form is valid or not
    if(this.editCustomerForm.valid){

      // sending the customer datas to the backend to update the customer datas
      console.log('Hello World',this.editCustomerForm.value);

      console.log(this.customer_id);
      this.customerService
      .editCustomerData(
        this.customer_id,
        this.editCustomerForm.value
      )
      .subscribe({
        next:(result:any)=>{
          this.toastr.success(result["message"], 'Customer Edited');
        },
        error:(result:any)=>{
          console.log(result);
          this.toastr.error(result["error"]["message"]);
        }
      })

      // triggering the event to change th edit box size once the edit part is done
      this.customerService.searchBoxSizeChange();

      // navigating back to the native page
      this.router.navigateByUrl('company/company-details/customers');
    }
    else{
      this.editCustomerForm.markAllAsTouched();
    }
  }

  closeEdit(){

    // minimizing the box if the edit part is aborted
    this.customerService.searchBoxSizeChange();

    this.router.navigateByUrl('company/company-details/customers');
  }

  getCustomerData(customer_id:any){

    // getting the custoemr data are showing them in the edit fields before chaning the datas
    this.customerService.fetchEditCustomer(customer_id).subscribe((result:any)=>{

      result = result['customer_data'];

      console.log(result['customer_name'])
      this.editCustomerForm.get('customerDetails')?.patchValue({
        customer_name: result['customer_name'],
        email_address: result['email_address'],
        phone_number: result['phone_number'],
        customer_vat_percentage: result['customer_vat_percentage']
      });
      this.editCustomerForm.get('customerAddressDetails')?.patchValue({
        address_line_1: result['address_line_1'],
        address_line_2: result['address_line_2'],
        city: result['city'],
        state: result['state'],
        country: result['country'],
        pincode: result['pincode'],

      });
      this.isLoading = false;
    })
  }

}
