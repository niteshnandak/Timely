import { Component, EventEmitter, Output } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { emailOrUsernameValidator } from '../validators/email-or-username.validator';
import { CommonModule,Location } from '@angular/common';
import { AuthService } from '../auth-services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { Router } from '@angular/router';
import { LoaderComponent } from '../../components/loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-forgot-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LoaderComponent],
  templateUrl: './forgot-password.component.html',
  styleUrl: './forgot-password.component.css'
})
export class ForgotPasswordComponent {

  @Output() loginPage = new EventEmitter();

  forgotPasswordForm!: FormGroup;

  isLoading!: boolean;

  constructor(
    private location:Location,
    private auth: AuthService,
    private toastr: ToastrService,
    private router:Router,
    private title: Title
  ) {
    this.title.setTitle('Forgot Password');
  }

  ngOnInit(): void {
    this.forgotPasswordForm = new FormGroup({
      identifier: new FormControl(null, [
        Validators.required,
        Validators.email,
        // emailOrUsernameValidator(),
        Validators.maxLength(30),
      ]),
    });
  }

  //FUNCTION TO CHECK FOR THE INPUT VALIDITY TO REDUCE THE HTML CODE
  isInputValid(formControlName: string): boolean | undefined {
    const inputControlName = this.forgotPasswordForm.get(formControlName);
    return inputControlName?.invalid && (inputControlName.dirty || inputControlName.touched);
  }

  // FUNCTION CALLED UPON FORGOT PASSWORD SUBMIT
  forgotPasswordSubmit() {
    const identifier = this.forgotPasswordForm.value;
    // console.log(identifier);
    this.isLoading = true;

    this.auth.forgotPassword(identifier).subscribe(
      (response: any) => {
        console.log(response.message);
        this.forgotPasswordForm.reset();
        this.toastr.success(response.message);

        this.isLoading = false;
      },
      (error) => {
        this.isLoading = false;

        console.log(error.error.message);
        if(error.error.message == 'User does not exist'
        || error.error.message == 'Account not activated, check your mail'
        || error.error.message == 'Account is deactivated') {
          this.toastr.error(error.error.message);
        }
        else {
          this.toastr.error('Reset Link already sent, check your mail');
        }

      }
    );
  }


  // FUNCTION TO ROUTE TO LOGIN PAGE
  isLoginPage: boolean = false;
  toLogin() {
    this.router.navigateByUrl('/login');
  }

}
