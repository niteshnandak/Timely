import { CommonModule, Location } from '@angular/common';
import { Component, EventEmitter, Output } from '@angular/core';
import {
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { emailOrUsernameValidator } from '../validators/email-or-username.validator';
import { AuthService } from '../auth-services/auth.service';
import { Router,RouterLink,ActivatedRoute } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../components/loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, LoaderComponent],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
})
export class LoginComponent {
  @Output() registerPage = new EventEmitter();
  @Output() forgotPasswordPage = new EventEmitter();

  isLoading!: boolean;

  loginForm!: FormGroup;

  constructor(
    private auth: AuthService,
    private router: Router,
    private location: Location,
    private toastr: ToastrService,
    private title: Title
  ) {
    this.title.setTitle('Login');
  }

  ngOnInit(): void {
    this.loginForm = new FormGroup({
      identifier: new FormControl(null, [
        Validators.required,
        emailOrUsernameValidator(),
        Validators.maxLength(30),
      ]),
      password: new FormControl(null, [Validators.required]),
    });
  }

  //FUNCTION TO CHECK FOR THE INPUT VALIDITY TO REDUCE THE HTML CODE
  isInputValid(formControlName: string): boolean | undefined {
    const inputControlName = this.loginForm.get(formControlName);
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  // FUNTION TO LOGIN
  login() {
    const { identifier, password } = this.loginForm.value;

    this.isLoading = true;

    if (this.loginForm.valid) {
      // console.log(this.loginForm.value);
      this.auth.login(identifier, password).subscribe(
        (response: any) => {
          // console.log(response);
          this.auth.setToken(response.access_token);
          this.auth.setRole(response.role);
          this.auth.setUser(response.user);
          if (response.role === 'admin') {
            this.toastr.success('Admin Logged in Successfully');
            this.router.navigate(['/app-admin/organisation']);
          }
          if (response.role === 'user') {
            this.toastr.success('User Logged in Successfully');
            this.router.navigate(['/dashboard']);
          }

          this.isLoading = false;
        },
        (error) => {
          this.isLoading = false;

          this.toastr.error(error.error.message);
          // console.log(error.error.message);
        }
      );
    }
  }

  // FUNCTION TO ROUTE TO REGISTER PAGE
  isRegisterPage: boolean = false;
  toRegister() {
    this.router.navigateByUrl('/registration');
  }

  // FUNCTION TO ROUTE TO FORGOT PASSWORD PAGE
  isForgotPasswordPage: boolean = false;
  toForgotPassword() {
    this.router.navigateByUrl('/forgot-password');
  }
}
