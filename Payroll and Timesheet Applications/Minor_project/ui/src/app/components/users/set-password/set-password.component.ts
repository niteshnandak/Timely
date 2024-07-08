import { Component, NgModule } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TextmatchPipe } from '../../../../Pipes/text-match/textmatch.pipe';
import { HeaderComponent } from "../header/header.component";
import { OnboardImageComponent } from "../onboard-image/onboard-image.component";
import { UserRegisterService } from '../../../services/UserRegister/user-register.service';
import { ActivatedRoute } from '@angular/router';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { PopoverModule } from '@progress/kendo-angular-tooltip';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-set-password',
  standalone: true,
  templateUrl: './set-password.component.html',
  imports: [ReactiveFormsModule,
    TextmatchPipe,
    HeaderComponent,
    OnboardImageComponent,
    PopoverModule,
    CommonModule
  ]
})

export class SetPasswordComponent {

  title = "Set Up Your Password";
  setPasswordForm!: FormGroup;
  token: any;

  constructor(private userService: UserRegisterService,
    private route: ActivatedRoute,
    private router: Router,
    private toastr: ToastrService) {

  }

  ngOnInit() {
    this.setPasswordForm = new FormGroup({
      password : new FormControl(null, [
    Validators.required,
    Validators.minLength(8),
    Validators.pattern(/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/)
  ]),
      password_confirmation: new FormControl(null, [Validators.required])
    })

    this.token = this.route.snapshot.params["token"];
    this.checkTokenValid(this.token);
  }
  get password() {
    return this.setPasswordForm.get('password')
  }

  get password_confirmation() {
    return this.setPasswordForm.get('password_confirmation')
  }

  setPassword() {

    this.userService.saveUserPassword(this.token, this.setPasswordForm.value).subscribe({
      next: (response) => {
        this.toastr.success(response["toaster_success"], 'Success');
        this.router.navigate(['login']);
      },
      error: (msg) => {
        this.router.navigate(['login']);
        console.log(msg);
        this.toastr.error(msg['error']["toaster_error"], 'Error');

      }
    })
  }


  checkTokenValid(token: any) {
    this.userService.setPasswordPage(token).subscribe({
      next: (response) => {
        this.toastr.info(response["toaster_info"], 'Info');
        setTimeout(() => {
          this.toastr.clear();
        }, 1500);
      },
      error: (msg) => {
        this.router.navigate(['login']);
        console.log(msg);
        this.toastr.error(msg.error?.message, 'Error');
        setTimeout(() => {
          this.toastr.clear();
        }, 1500);

      }
    })

  }

  passwordFormatValid() {
  if(this.password?.dirty && !this.password?.valid){

  }
    
  }
}

