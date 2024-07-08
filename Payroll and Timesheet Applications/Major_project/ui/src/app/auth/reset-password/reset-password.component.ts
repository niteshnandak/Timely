import { Component } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../auth-services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { CommonModule } from '@angular/common';
import { IsmatchPipe } from '../../../Pipes/ismatch.pipe';
import { LoaderComponent } from '../../components/loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, IsmatchPipe, LoaderComponent],
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.css'
})
export class ResetPasswordComponent {

  isLoading!: boolean;

  resetPasswordForm!: FormGroup;

  constructor(
    private auth: AuthService,
    private router: Router,
    private route: ActivatedRoute,
    private toastr: ToastrService,
    private title: Title
  ) {
    this.title.setTitle('Reset Password');
  }

  token: any;
  ngOnInit(): void {
    this.resetPasswordForm = new FormGroup({
      password: new FormControl('', [
        Validators.required,
        Validators.minLength(8),
        Validators.pattern(
          /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/
        ),
      ]),
      confirmPassword: new FormControl('', [Validators.required]),
    });

    this.token = this.route.snapshot.params['token'];
    console.log(this.token);
    this.checkTokenValid(this.token);
  }

  //FUNCTION TO CHECK FOR THE INPUT VALIDITY TO REDUCE THE HTML CODE
  checkTokenValid(token: any) {
    this.auth.resetPasswordValidateUser(token).subscribe(
      (response: any) => {
        console.log(response);
        this.toastr.info(response.message);
      },
      (error) => {
        // console.log(error.error);
        this.toastr.info(error.error.message);
        this.router.navigate(['home']);
      }
    )
  }

  // FUNCTION TO RESET THE PASSWORD
  resetPassword(){
    this.isLoading = true;

    this.auth.resetPassword(this.token, this.resetPasswordForm.value).subscribe(
      (response: any) => {
        console.log(response);
        this.resetPasswordForm.reset();
        this.toastr.success(response.message);
        this.router.navigate(['home']);

        this.isLoading = false;
      },
      (error) => {
        this.isLoading = false;

        this.toastr.error(error.error.message);
      }
    )
  }


}
