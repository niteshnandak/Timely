import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { UserRegisterService } from '../../../services/UserRegister/user-register.service';
import { Router, RouterLink } from '@angular/router';
import { HeaderComponent } from "../header/header.component";
import { ToastrService } from 'ngx-toastr';
import { OnboardImageComponent } from "../onboard-image/onboard-image.component";
import { NumberOnlyDirective } from '../../../directive/number-only/number-only.directive';
import { CharOnlyDirective } from '../../../directive/char-only/char-only.directive';
import { LoaderComponent } from "../../loader/loader.component";

@Component({
  selector: 'app-register',
  standalone: true,
  templateUrl: './register.component.html',
  imports: [ReactiveFormsModule,
    RouterLink,
    HeaderComponent,
    OnboardImageComponent,
    NumberOnlyDirective,
    CharOnlyDirective, LoaderComponent]
})

export class RegisterComponent implements OnInit {
  title = "Registration";
  registrationForm!: FormGroup;
  user: any;
  isLoading: boolean = false;
  constructor(
    private userService: UserRegisterService,
    private toastr: ToastrService,
    private router: Router
  ) { }
  ngOnInit() {
    this.registrationForm = new FormGroup({
      first_name: new FormControl(null, [Validators.required, Validators.pattern("^[a-zA-Z]+$"),Validators.maxLength(30)]),
      last_name: new FormControl(null, [Validators.required, Validators.pattern("^[a-zA-Z]+$"),Validators.maxLength(30)]),
      email: new FormControl(null, [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]),
      phone_number: new FormControl(null, [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(10), Validators.maxLength(10)])
    })
  }

  get first_name() {
    return this.registrationForm.get('first_name');
  }
  get last_name() {
    return this.registrationForm.get('last_name');
  }
  get email() {
    return this.registrationForm.get('email');
  }
  get phone_number() {
    return this.registrationForm.get('phone_number');
  }

  OnRegisterSubmit() {
    this.isLoading = true
    this.userService.registerUser(this.registrationForm.value).subscribe({

      next: (response) => {
        this.router.navigate(['login'])
        this.toastr.success(response["toaster_success"], 'Registered');
        this.isLoading = false;
      },
      error: (msg) => {
        if(msg.error.errors.email && msg.error.errors.phone_number){
          this.toastr.error('Both Email Address  and Phone Number Taken', 'Error');
        }else if(msg.error.errors.email){
          this.toastr.error(msg.error.errors.email, 'Error');
        }else if(msg.error.errors.phone_number){
          this.toastr.error(msg.error.errors.phone_number, 'Error');
        }else{
          this.toastr.error(msg["toaster_error"], 'Error');
        }
        console.log(msg.error.errors)
        
        this.isLoading = false;
      }
    })
  }
}
