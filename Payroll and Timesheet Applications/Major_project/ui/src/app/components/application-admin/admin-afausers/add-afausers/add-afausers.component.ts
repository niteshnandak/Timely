import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { AdminAfausersService } from '../../../../services/admin-services/admin-afausers.service';
import { CommonModule, Location } from '@angular/common';
import { AuthService } from '../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../loader/loader.component';

@Component({
  selector: 'app-add-afausers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LoaderComponent],
  templateUrl: './add-afausers.component.html',
  styleUrl: './add-afausers.component.css'
})

//CLASS: AddAfausersComponent
export class AddAfausersComponent {
  adminCreateUserForm!: FormGroup;
  orgId!: number;

  isLoading!: boolean;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private adminAfaService: AdminAfausersService,
    private toastr: ToastrService,
    private router: Router,
    private route: ActivatedRoute,
    private auth: AuthService
  ) {}

  //Add form group initialization
  ngOnInit(): void {
    // this.orgId = this.route.snapshot.params['orgId'];
    this.orgId = this.adminAfaService.getOrgId();
    // console.log(this.orgId);

    this.adminCreateUserForm = this.fb.group({
      firstName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
          Validators.minLength(3),
        ],
      ],
      surName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
          Validators.minLength(3),
        ],
      ],
      userName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z0-9._]{3,50}$'),
          Validators.maxLength(30),
        ],
      ],
      email: [
        null,
        [
          Validators.required,
          Validators.email
        ]
      ],
    });
  }

  //FUNCTION TO CHECK THE VALIDITY OF THE INPUT
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.adminCreateUserForm.get(formcontrolName);
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  //FUNCTION TO RESET THE INPUTS
  resetForm() {
    this.adminCreateUserForm.reset();
  }


  admin: any;
  //ADD FUNCTIONALITY
  onSubmit() {
    this.admin = this.auth.getUser();

    const formData = this.adminCreateUserForm.value;

    this.isLoading = true;

    this.adminAfaService.createAfaUser(formData, this.orgId, this.admin.user_id).subscribe(
      (response: any) => {
        console.log(response);
        this.toastr.success(response.message);
        this.toastr.success(response.success);
        this.adminAfaService.addBoxSizeChange();
        this.router.navigate([`app-admin/organisation/${this.orgId}/afausers`]);

        this.isLoading = false;
      },
      (error) => {
        console.log(error.error.errors['data.userName']);
        if (error.error.errors['data.email'] && error.error.errors['data.userName']) {
          this.toastr.error(
            'The email & username has already been taken.',
            'Error'
          );
        } else if (error.error.errors['data.email']) {
          this.toastr.error('The email has already been taken.', 'Error');
        } else if (error.error.errors['data.userName']) {
          this.toastr.error('The username has already been taken.', 'Error');
        } else {
          this.toastr.error('Something went wrong try after sometime', 'Error');
        }
        console.log(error);
        this.isLoading = false;
      }
    );


  }


}
