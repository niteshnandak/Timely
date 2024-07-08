import { Component } from '@angular/core';
import { RegistrationComponent } from '../registration/registration.component';
import { CommonModule, Location } from '@angular/common';
import { LoginComponent } from '../login/login.component';
import { ForgotPasswordComponent } from '../forgot-password/forgot-password.component';
import { SavePasswordComponent } from '../save-password/save-password.component';
import { Router, ActivatedRoute } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { ResetPasswordComponent } from '../reset-password/reset-password.component';
@Component({
  selector: 'app-authmanage',
  standalone: true,
  imports: [
    RegistrationComponent,
    CommonModule,
    LoginComponent,
    ForgotPasswordComponent,
    SavePasswordComponent,
    ResetPasswordComponent,
  ],
  templateUrl: './authmanage.component.html',
  styleUrl: './authmanage.component.css',
})
export class AuthmanageComponent {
  //update the flags based on the template
  isRegistrationPage!: boolean;
  isSavePasswordPage!: boolean;
  isResetPasswordPage!: boolean;
  isLoginPage!: boolean;
  isForgotPasswordPage!: boolean;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private location: Location,
    private toastr: ToastrService,
  ) {}

  ngOnInit(): void {
    this.updatePageFlags();
  }

  //FUNCTION TO UPDATE THE FLAGES ON INITIALIZATION
  private updatePageFlags(): void {
    const url = this.router.url;
    if (url.startsWith('//') || url.startsWith('/login')) {
      this.isLoginPage = true;
      this.isRegistrationPage = false;
      this.isSavePasswordPage = false;
      this.isForgotPasswordPage = false;
      this.isResetPasswordPage = false;
    }
     else if (url.startsWith('/set-password/')) {
      this.isSavePasswordPage = true;
      this.isRegistrationPage = false;
      this.isForgotPasswordPage = false;
      this.isLoginPage = false;
      this.isResetPasswordPage = false;
    }
     else if (url.startsWith('/registration')) {
      this.isSavePasswordPage = false;
      this.isRegistrationPage = true;
      this.isForgotPasswordPage = false;
      this.isLoginPage = false;
      this.isResetPasswordPage = false;
    }
     else if (url.startsWith('/forgot-password')) {
      this.isSavePasswordPage = false;
      this.isRegistrationPage = false;
      this.isForgotPasswordPage = true;
      this.isLoginPage = false;
      this.isResetPasswordPage = false;
    }
     else if (url.startsWith('/reset-password/')) {
      this.isResetPasswordPage = true;
      this.isSavePasswordPage = false;
      this.isRegistrationPage = false;
      this.isForgotPasswordPage = false;
      this.isLoginPage = false;
    } else {
      console.log(`Unexpected URL: ${url}`);
    }
  }

  // FUNTION TO ROUTE TO LOGIN PAGE
  routeToLogin(data: any) {
    this.isLoginPage = true;
    this.isRegistrationPage = false;
    this.isForgotPasswordPage = false;
    this.isSavePasswordPage = false;
    this.isResetPasswordPage = false;
  }

  // FUNTION TO ROUTE TO REGISTRATION PAGE
  routeToRegister(data: any) {
    this.isRegistrationPage = true;
    this.isLoginPage = false;
    this.isForgotPasswordPage = false;
    this.isSavePasswordPage = false;
    this.isResetPasswordPage = false;
  }

  // FUNTION ROUTE TO FORGOT PASSWORD PAGE
  routeToForgotPassword(data: any) {
    this.isForgotPasswordPage = true;
    this.isRegistrationPage = false;
    this.isLoginPage = false;
    this.isSavePasswordPage = false;
    this.isResetPasswordPage = false;
  }
}
