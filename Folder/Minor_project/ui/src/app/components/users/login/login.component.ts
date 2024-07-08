import { Component, EventEmitter, OnInit, Output} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { HeaderComponent } from '../header/header.component';
import { HttpClient } from '@angular/common/http';
import { AuthService } from '../../../services/auth.service';
import { TokenService } from '../../../services/token.service';
import { AuthGuardService } from '../../../services/auth-guard.service';
import { OnboardImageComponent } from '../onboard-image/onboard-image.component';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [RouterOutlet,
            FormsModule,
            ReactiveFormsModule,
            CommonModule,
            RouterLink,
            HeaderComponent,
            OnboardImageComponent
],
  templateUrl: './login.component.html',
})
export class LoginComponent implements OnInit{

  title = "Login"
  user ?: any;
  token ?: any;

  loginForm!: FormGroup ;

  constructor(private router: Router, private auth: AuthService, private toast:ToastrService, private http:HttpClient, private tokenService:TokenService, private authGuardService:AuthGuardService) { }

  ngOnInit():void{
    this.initializeFormGroup();
  }

  initializeFormGroup(){
    this.loginForm = new FormGroup({
      email: new FormControl(null, [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]),
      password: new FormControl(null, [Validators.required]),
    })
  }

  // convenience getter for easy access to form fields
  get email(){
    return this.loginForm.get('email')
  }
  get password(){
    return this.loginForm.get('password')
  }

  login(){
    return this.auth.login(this.loginForm.value).subscribe({
      next:(res)=>{
        if(res.status === 200){
          this.user = res.user;
          this.token = res.token;
          this.toast.success(res.toaster_success, 'Success');
          this.tokenService.setUserAndToken(this.user, this.token);
          this.router.navigateByUrl('timesheet');
        }
    },
      error: (msg)=>{
      this.toast.error(msg.error.toaster_error,'Error');
    }
    })
  }

}
