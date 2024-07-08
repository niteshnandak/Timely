import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { PeopleService } from '../../../../../services/people.service';
import { HttpClient } from '@angular/common/http';
import { CustomersService } from '../../../../../services/customers.service';
import { CharWithSpaceDirective } from '../../../../../directive/char-with-space/char-with-space.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { maxYearValidator } from '../../../../../validations/date-validator';

@Component({
  selector: 'app-add-people',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink,CharWithSpaceDirective,NumberOnlyDirective],
  templateUrl: './add-people.component.html',
})
export class AddPeopleComponent {

  addPeopleForm!: FormGroup ;

  people_id:any;

  peopleData:any;
  peopleAddressData:any;

  user:any;
  isLoading!:boolean;

  
  constructor(private formbuilder : FormBuilder,
    private route:ActivatedRoute ,
    private peopleService:PeopleService,
    private router:Router,
    private customerService: CustomersService,
    private toastr:ToastrService,
    private auth:AuthService
  ){}
  ngOnInit(){
    this.user = this.auth.getUser();
    console.log(this.user);
    this.addPeopleForm = this.formbuilder.group({
      peopleDetails: this.formbuilder.group({
        people_name: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(3)]),
        job_title: new FormControl(null, [Validators.required, Validators.maxLength(50),Validators.minLength(2)]),
        birth_date: new FormControl(null, [Validators.required, maxYearValidator()]),
        gender: new FormControl(null, [Validators.required]),
        email_address: new FormControl(null, [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$"), Validators.maxLength(75)]),
        phone_number: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(10), Validators.maxLength(10)])
      }),

      peopleAddressDetails: this.formbuilder.group({
        address_line_1: new FormControl(null, [Validators.required,Validators.maxLength(50)]),
        address_line_2: new FormControl(null,Validators.maxLength(50)),
        city: new FormControl(null, [Validators.required, Validators.maxLength(50)]),
        state: new FormControl(null, [Validators.required, Validators.maxLength(50)]),
        country: new FormControl(null, [Validators.required, Validators.maxLength(50)]),
        pincode: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(6), Validators.maxLength(6)]),
      }),
    })
    }
    

  addPeopleFormSubmit(){
    
    this.isLoading=true;
    console.log(this.addPeopleForm.value);
    this.peopleService.addPeopleData(this.addPeopleForm.value,this.user).subscribe({
      next:(response)=>{
        // this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
        //   this.router.navigate(['/people']);
        // });
        this.toastr.success(response["toaster_success"], 'Created');
        // this.router.navigateByUrl('/people');
      
      },
      error:(msg)=>{
        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })
    this.peopleService.closePeopleEditState();
  }

  get people_name(){
    return this.addPeopleForm.get('peopleDetails')?.get('people_name');
   }
   get job_title(){
     return this.addPeopleForm.get('peopleDetails')?.get('job_title');
    }
  get gender(){
    return this.addPeopleForm.get('peopleDetails')?.get('gender');
    }
  get birth_date(){
    return this.addPeopleForm.get('peopleDetails')?.get('birth_date');
    }
  get email_address(){
    return this.addPeopleForm.get('peopleDetails')?.get('email_address');
  }

  get phone_number(){
    return this.addPeopleForm.get('peopleDetails')?.get('phone_number');
  }

  get address_line_1(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('address_line_1');
  }
  get address_line_2(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('address_line_2');
  }
  get city(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('city');
  }
  get state(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('state');
  }
  get country(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('country');
  }
  get pincode(){
    return this.addPeopleForm.get('peopleAddressDetails')?.get('pincode');
  }

  clearInvalidDate(event: any) {
    const peopleDateControl = this.addPeopleForm.get('start_date');
    
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

  resetForm() {
    this.addPeopleForm.reset();
  }

}
