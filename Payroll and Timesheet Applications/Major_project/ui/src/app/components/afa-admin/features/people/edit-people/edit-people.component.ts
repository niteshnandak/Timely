import { CommonModule } from '@angular/common';
import { Component, OnChanges, OnInit, SimpleChange, SimpleChanges } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { Subscription } from 'rxjs';
import { CustomersService } from '../../../../../services/customers.service';
import { CharWithSpaceDirective } from '../../../../../directive/char-with-space/char-with-space.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../../loader/loader.component';
import { SomeCharsDirective } from '../../../../../directive/some-chars-only/some-chars.directive';
import { maxYearValidator } from '../../../../../validations/date-validator';

@Component({
  selector: 'app-edit-people',
  standalone: true,
  imports: [
          CommonModule,
          ReactiveFormsModule, 
          RouterLink, 
          CharWithSpaceDirective,
          NumberOnlyDirective,
          LoaderComponent,
          SomeCharsDirective
        ],
  templateUrl: './edit-people.component.html',
})
export class EditPeopleComponent {

  editPeopleForm !: FormGroup;
  people_id: any;

  peopleData: any;
  peopleAddressData: any;
  peopleEmploymentData: any;
  peopleBankData: any;
  companies: any;
  user:any;
  isLoading!:boolean;
  constructor(private formbuilder: FormBuilder,
    private route: ActivatedRoute,
    private peopleService: PeopleService,
    private router: Router,
    private customerService: CustomersService,
    private toastr: ToastrService,
    private auth:AuthService
  ) {

    route.params.subscribe(val => {
      this.people_id = this.route.snapshot.params['id'];
      this.getEditPeopleData(this.people_id);

      // this.getAllCompanies();
    });
  }

  ngOnInit() {

    this.user = this.auth.getUser();

    this.editPeopleForm = this.formbuilder.group({
      peopleDetails: this.formbuilder.group({
        people_name: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(3)]),
        job_title: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(2)]),
        birth_date: new FormControl(null, [Validators.required, maxYearValidator()]),
        gender: new FormControl(null, [Validators.required]),
        email_address: new FormControl(null, [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$"),Validators.maxLength(75)]),
        phone_number: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(10), Validators.maxLength(10)])
      }),

      peopleAddressDetails: this.formbuilder.group({
        address_line_1: new FormControl(null, [Validators.required,Validators.maxLength(50),Validators.minLength(3)]),
        address_line_2: new FormControl(null,[Validators.maxLength(50),Validators.minLength(3)]),
        city: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(3)]),
        state: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(3)]),
        country: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(3)]),
        pincode: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(6), Validators.maxLength(6)]),
      }),

      peopleEmploymentDetails: this.formbuilder.group({
        company_name: new FormControl(null, [Validators.required]),
        joining_date: new FormControl(null, [Validators.required]),
        pay_frequency: new FormControl(null, [Validators.required]),
        nino_number: new FormControl(null, [Validators.required, Validators.pattern("^(?!BG|GB|NK|KN|TN|NT|ZZ)[ABCEGHJKLMNOPRSTWXYZ]{2}[0-9]{6}[ABCD]$"), Validators.minLength(9), Validators.maxLength(9)]),
      }),

      peopleBankDetails: this.formbuilder.group({
        bank_name: new FormControl(null, [Validators.required, Validators.maxLength(50)]),
        account_number: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(10), Validators.maxLength(10)]),
        bank_branch: new FormControl(null, [Validators.required, Validators.maxLength(50)]),
        bank_ifsc_code: new FormControl(null, [Validators.required, Validators.maxLength(6),Validators.minLength(6),Validators.pattern("^[A-Z0-9 ]*$")]),
      })

    })

    this.people_id = this.route.snapshot.params['id'];
    // this.getEditPeopleData(this.people_id);

    this.getAllCompanies();
  }




  editPeopleFormSubmit() {
    this.isLoading = true;
    this.peopleService.saveEditPeopleData(this.people_id, this.editPeopleForm.value,this.user).subscribe({
      next: (response) => {

        this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
          this.router.navigate(['/people']);
        });
        this.toastr.success(response["toaster_success"], 'Updated');
      },
      error: (msg)=>{
        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })

   this.peopleService.closePeopleEditState();

  }

  getEditPeopleData(people_id: any) {
    this.isLoading =true;
    this.peopleService.getEditPeopleData(people_id).subscribe({

      next: (response) => {

        this.peopleData = response.people_data[0];
        // console.log(this.editPeopleData['people_name']);
        this.editPeopleForm.get('peopleDetails')?.patchValue({
          people_name: this.peopleData['people_name'],
          job_title: this.peopleData['job_title'],
          gender: this.peopleData['gender'],
          birth_date: this.peopleData['birth_date'],
          email_address: this.peopleData['email_address'],
          phone_number: this.peopleData['phone_number']
        });
        // console.log( response.people_address_details[0]);
        this.peopleAddressData = response.people_address_details[0];
        this.editPeopleForm.get('peopleAddressDetails')?.patchValue({
          address_line_1: this.peopleAddressData['address_line_1'],
          address_line_2: this.peopleAddressData['address_line_2'],
          city: this.peopleAddressData['city'],
          state: this.peopleAddressData['state'],
          country: this.peopleAddressData['country'],
          pincode: this.peopleAddressData['pincode'],

        });

        // console.log( response.people_employment_details[0]);
        this.peopleEmploymentData = response.people_employment_details[0];
        this.editPeopleForm.get('peopleEmploymentDetails')?.patchValue({
          company_name: this.peopleEmploymentData['company_id'],
          joining_date: this.peopleEmploymentData['joining_date'],
          pay_frequency: this.peopleEmploymentData['pay_frequency'],
          nino_number: this.peopleEmploymentData['nino_number'],
        });


        this.peopleBankData = response.people_bank_details[0];
        this.editPeopleForm.get('peopleBankDetails')?.patchValue({
          bank_name: this.peopleBankData['bank_name'],
          account_number: this.peopleBankData['account_number'],
          bank_branch: this.peopleBankData['bank_branch'],
          bank_ifsc_code: this.peopleBankData['bank_ifsc_code'],
        });

        this.isLoading=false;
        // this.toastr.info(response["toaster_success"], 'Update');
        // this.editPeopleForm.get('peopleDetails')?.get('people_name')?.patchValue(this.editPeopleData['people_name']);
      },
      error: (msg)=>{
        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })

  }

  clearInvalidDate(event: any) {
    const peopleDateControl = this.editPeopleForm.get('start_date');
    
    if (peopleDateControl) {
        this.handleInvalidDate(peopleDateControl, event);
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

  get people_name() {
    return this.editPeopleForm.get('peopleDetails')?.get('people_name');
  }
  get job_title() {
    return this.editPeopleForm.get('peopleDetails')?.get('job_title');
  }
  get gender() {
    return this.editPeopleForm.get('peopleDetails')?.get('gender');
  }
  get birth_date() {
    return this.editPeopleForm.get('peopleDetails')?.get('birth_date');
  }
  get email_address() {
    return this.editPeopleForm.get('peopleDetails')?.get('email_address');
  }

  get phone_number() {
    return this.editPeopleForm.get('peopleDetails')?.get('phone_number');
  }

  get address_line_1() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('address_line_1');
  }
  get address_line_2() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('address_line_2');
  }
  get city() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('city');
  }
  get state() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('state');
  }
  get country() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('country');
  }
  get pincode() {
    return this.editPeopleForm.get('peopleAddressDetails')?.get('pincode');
  }

  get company_name() {
    return this.editPeopleForm.get('peopleEmploymentDetails')?.get('company_name');
  }
  get joining_date() {
    return this.editPeopleForm.get('peopleEmploymentDetails')?.get('joining_date');
  }
  get nino_number() {
    return this.editPeopleForm.get('peopleEmploymentDetails')?.get('nino_number');
  }
  get pay_frequency() {
    return this.editPeopleForm.get('peopleEmploymentDetails')?.get('pay_frequency');
  }

  get bank_name() {
    return this.editPeopleForm.get('peopleBankDetails')?.get('bank_name');
  }
  get account_number() {
    return this.editPeopleForm.get('peopleBankDetails')?.get('account_number');
  }
  get bank_branch() {
    return this.editPeopleForm.get('peopleBankDetails')?.get('bank_branch');
  }
  get bank_ifsc_code() {
    return this.editPeopleForm.get('peopleBankDetails')?.get('bank_ifsc_code');
  }

  closeEdit(){
    this.peopleService.closePeopleEditState();
    this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
      this.router.navigate(['/people']);
    });


  }

  getAllCompanies() {
    this.peopleService.getCompanies().subscribe({
      next: (response) => {
        this.companies = response.companies
      }
    })
  }
}
