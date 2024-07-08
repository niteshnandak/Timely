import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Route, Router, RouterLink, RouterOutlet } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../services/auth.service';
import { TextmatchPipe } from '../../../../Pipes/text-match/textmatch.pipe';
import { HeaderComponent } from '../header/header.component';
import { PopoverModule } from '@progress/kendo-angular-tooltip';
import { OnboardImageComponent } from "../onboard-image/onboard-image.component";

@Component({
    selector: 'app-reset-password',
    standalone: true,
    templateUrl: './reset-password.component.html',
    styleUrl: './reset-password.component.css',
    imports: [RouterOutlet,
        FormsModule,
        ReactiveFormsModule,
        CommonModule,
        RouterLink,
        TextmatchPipe,
        HeaderComponent,
        PopoverModule, OnboardImageComponent]
})
export class ResetPasswordComponent implements OnInit {

  title = "Reset Your Password"
  token:any;

  resetForm!: FormGroup

  constructor(private router:Router, private auth:AuthService,private toast:ToastrService, private route:ActivatedRoute) { }

  ngOnInit(): void {
    this.initializeFormGroup();
  }

  initializeFormGroup(){
    this.resetForm = new FormGroup({
      password: new FormControl(null,[Validators.required,Validators.pattern('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&].{8,}'),Validators.minLength(8)]),
      password_confirmation: new FormControl(null,[Validators.required]),
    })
    this.token = this.route.snapshot.params["token"];
    console.log(this.token);
    this.checkTokenValid(this.token);
  }

  // convenience getter for easy access to form fields
  get password(){
    return this.resetForm.get('password')
  }

  get password_confirmation(){
    return this.resetForm.get('password_confirmation')
  }

  onSubmit(){
    this.auth.saveResetPassword(this.token, this.resetForm.value).subscribe({
      next:(response)=>{
        console.log(response);
        this.resetForm.reset();
        this.toast.success(response.toaster_success, 'Updated');
      },
      error: (msg)=>{
        this.toast.error(msg.error.toaster_error,'Error');
      }
    })
  }

  checkTokenValid(token:any){
    this.auth.resetPasswordValidateUser(token).subscribe({
      next:(response:any)=>{
        console.log(response);
        this.toast.info(response["toaster_info"], 'Info');
      },
      error: (msg:any)=>{
        console.log(msg);
        this.router.navigate(['login']);
        this.toast.error(msg.error?.message, 'Error');
      }
    })
  }
}
