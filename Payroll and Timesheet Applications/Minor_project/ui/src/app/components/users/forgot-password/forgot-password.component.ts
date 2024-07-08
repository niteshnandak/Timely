import { Component, OnInit} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../services/auth.service';
import { HeaderComponent } from '../header/header.component';
import { LoaderComponent } from '../../loader/loader.component';


@Component({
  selector: 'app-forgot-password',
  standalone: true,
  imports: [RouterOutlet,
            FormsModule,
            ReactiveFormsModule,
            CommonModule,
            RouterLink,
            HeaderComponent,
            LoaderComponent],
  templateUrl: './forgot-password.component.html',
  styleUrl: './forgot-password.component.css'
})
export class ForgotPasswordComponent implements OnInit{

  title = "Reset Your Password";
  isLoading:boolean=false;

  forgotForm!: FormGroup

  constructor(private router:Router, private toast:ToastrService, private auth:AuthService) { }

  ngOnInit(): void {
    this.initializeFormGroup()
  }

  initializeFormGroup(){
    this.forgotForm = new FormGroup({
      email: new FormControl(null,[Validators.required,Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]),
    })
  }

  // convenience getter for easy access to form fields
  get email(){
    return this.forgotForm.get('email')
  }

  onSubmit(){
    this.isLoading=true;
    this.auth.forgotPassword(this.forgotForm.value).subscribe({
      next:(response)=>{
        console.log(response.toaster_success);
        this.forgotForm.reset();
        this.toast.success(response.toaster_success,'Success');
        this.isLoading=false;
      },
      error:(msg)=>{
        this.toast.error(msg.error.toaster_error,'Error');
        this.isLoading=false;
      }
    })
  }
}
