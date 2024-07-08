import { Component, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { OrganisationService } from '../../../../services/organisation.service';
import { AdminOrganisationsService } from '../../../../services/admin-services/admin-organisations.service';
import { ToastrService } from 'ngx-toastr';
import { Router, RouterLinkActive, RouterLink } from '@angular/router';
import { AuthService } from '../../../../auth/auth-services/auth.service';

@Component({
  selector: 'app-add-organisation',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, HttpClientModule],
  templateUrl: './add-organisation.component.html',
  styleUrls: ['./add-organisation.component.css'],
})

//CLASS :AddOrganisationComponent
export class AddOrganisationComponent implements OnInit {
  adminOrgForm!: FormGroup;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private orgService: AdminOrganisationsService,
    private toastr: ToastrService,
    private router: Router,
    private auth: AuthService
  ) {}

  ngOnInit(): void {
    //form group initialization
    this.adminOrgForm = this.fb.group({
      organisationName: [
        '',
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z 0-9.-_&]+$'),
          Validators.maxLength(30),
        ],
      ],
      status: [null, [Validators.required]],

      firstName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
        ],
      ],
      surName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
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
          Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$'),
        ],
      ],
    });
  }

  //FUNCTION TO RESET THE INPUTS
  resetForm() {
    this.adminOrgForm.reset();
  }

  //FUNCTION TO CHECK THE VALIDITY OF THE INPUT
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.adminOrgForm.get(formcontrolName);

    //return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  admin: any;

  //ADD FUNCTIONALITY
  onSubmit() {
    this.admin = this.auth.getUser();
    const formData = this.adminOrgForm.value;
    formData.adminId = this.admin.user_id;

    this.orgService.createOrg(formData).subscribe(

      //response
      (res: any) => {
        this.toastr.success(res.message, 'Created');
        this.orgService.addBoxSizeChange();

        this.router.navigateByUrl('/app-admin/organisation');
        console.log(res);
      },

      //error
      (error: any) => {
        //check for the error and prompt based on it.
        if (error.error.errors.email && error.error.errors.userName) {
          this.toastr.error(
            'The email & username has already been taken.',
            'Error'
          );
        } 
        else if (error.error.errors.email) {
          this.toastr.error(error.error.errors.email, 'Error');
        }
        else if (error.error.errors.organisationName) {
          this.toastr.error(error.error.errors.organisationName, 'Error');
        }
         else if (error.error.errors.userName) {
          this.toastr.error(error.error.errors.userName, 'Error');
        } else {
          this.toastr.error('Something went wrong try after sometime', 'Error');
        }
        console.log(error);
      }
    );
  }
}
