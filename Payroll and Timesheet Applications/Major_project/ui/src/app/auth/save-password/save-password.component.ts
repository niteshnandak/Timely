import { CommonModule, Location } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { Component, Output, EventEmitter } from '@angular/core';
import { IsmatchPipe } from '../../../Pipes/ismatch.pipe';
import {
  ReactiveFormsModule,
  FormGroup,
  FormControl,
  Validators,
} from '@angular/forms';
import { RegistrationService } from '../auth-services/registration.service';
import { Route, Router, ActivatedRoute } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { LoaderComponent } from '../../components/loader/loader.component';

@Component({
  selector: 'app-save-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, HttpClientModule, IsmatchPipe,LoaderComponent],
  providers: [RegistrationService],
  templateUrl: './save-password.component.html',
  styleUrl: './save-password.component.css',
})

// CLASS: SavePasswordComponent
export class SavePasswordComponent {
  token: any;
  enterPasswordForm!: FormGroup;
  isPasswordAlreadySet: boolean = false;
  public isLoading !: boolean;

  constructor(
    private route: ActivatedRoute,
    private regService: RegistrationService,
    private router: Router,
    private location: Location,
    private toastr: ToastrService
  ) {}

  ngOnInit(): void {
    this.enterPasswordForm = new FormGroup({
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
    this.checkUserToken(this.token);
  }
  checkUserToken(token: any) {
    this.regService.validateToken(token).subscribe({
      next: (response) => {
        console.log(response);
        this.isPasswordAlreadySet = false;
      },
      error: (msg) => {
        this.isPasswordAlreadySet = true;
      },
    });
  }

  submitPassword() {
    this.isLoading=true;

    this.regService
      .savePassword(this.token, this.enterPasswordForm.value)
      .subscribe({
        next: (response) => {
          this.isLoading=false;

          this.toastr.success(
            'Please Log in to proceed.',
            'Password set Successfull'
          );
          this.router.navigate(['login']);
        },
        error: (msg) => {
          this.isLoading=false;

          this.toastr.error(
            'Something went wrong try after sometime',
            'Failed'
          );
        },
      });
  }
  
  isLogin() {
    this.router.navigate(['login']);
  }
}
