import { Component, Output, EventEmitter } from '@angular/core';
import { CommonModule, Location } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import {
  ReactiveFormsModule,
  FormGroup,
  FormControl,
  Validators,
} from '@angular/forms';
import { RegistrationService } from '../auth-services/registration.service';
import { ToastrService } from 'ngx-toastr';
import { Router } from '@angular/router';
import { LoaderComponent } from '../../components/loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-registration',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, HttpClientModule,LoaderComponent],
  providers: [RegistrationService],
  templateUrl: './registration.component.html',
  styleUrl: './registration.component.css',
})
export class RegistrationComponent {
  @Output() loginPage = new EventEmitter();
  registerOrgForm!: FormGroup;
  public isLoading !: boolean;


  constructor(
    private regService: RegistrationService,
    private toastr: ToastrService,
    private location: Location,
    private router:Router,
    private title: Title
  ) {
    this.title.setTitle('Register');
  }
  ngOnInit(): void {
    this.registerOrgForm = new FormGroup({
      firstName: new FormControl(null, [
        Validators.required,
        Validators.pattern('^[a-zA-Z]+$'),
        Validators.maxLength(30),
      ]),
      surName: new FormControl(null, [
        Validators.required,
        Validators.pattern('^[a-zA-Z]+$'),
        Validators.maxLength(30),
      ]),
      userName: new FormControl(null, [
        Validators.required,
        Validators.pattern('^[a-zA-Z0-9._]{3,50}$'),
        Validators.maxLength(30),
      ]),
      organisationName: new FormControl(null, [
        Validators.required,
        Validators.pattern('^[a-zA-Z0-9 .-_&]+$'),
        Validators.maxLength(30),
      ]),
      email: new FormControl(null, [
        Validators.required,
        Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$'),
      ]),
    });
  }

  //FUNCTION TO CHECK FOR THE INPUT VALIDITY TO REDUCE THE HTML CODE
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.registerOrgForm.get(formcontrolName);
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  // FUNTION TO REGISTER THE ORGANISATION
  registerOrganisation() {
    this.isLoading=true;
    this.regService.registerUser(this.registerOrgForm.value).subscribe(

      //response
      (response: any) => {
        this.registerOrgForm.reset();
        this.isLoading=false;
        this.toastr.success(response.message, 'Registered successfully');
      },

      //error
      (error: any) => {
        console.log(error.error.message);
        this.isLoading=false;

        if (error.error.errors.email && error.error.errors.userName) {
          this.toastr.error(
            'The Email & username has already been taken.',
            'Error'
          );
        }
         else if (error.error.errors.email) {
          this.toastr.error(error.error.errors.email, 'Error');
        } 
         
        else if (error.error.errors.userName) {
          this.toastr.error(error.error.errors.userName, 'Error');
        } else {
          this.toastr.error(error.error.message, 'Error');
        }
      }
    );
  }

  // FUNTION TO ROUTE TO LOGIN PAGE
  isLogin() {
    this.router.navigateByUrl('/login');
  }
}
