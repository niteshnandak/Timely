import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { AdminAfausersService } from '../../../../services/admin-services/admin-afausers.service';
import { ToastrService } from 'ngx-toastr';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../loader/loader.component';

@Component({
  selector: 'app-edit-afausers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LoaderComponent],
  templateUrl: './edit-afausers.component.html',
  styleUrl: './edit-afausers.component.css'
})

//CLASS: EditAfausersComponent
export class EditAfausersComponent {
  adminEditUserForm!: FormGroup;
  orgId!: number;
  userId!: number;

  isLoading!: boolean;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private adminAfaService: AdminAfausersService,
    private toastr: ToastrService,
    private router: Router,
    private route: ActivatedRoute,
    private auth: AuthService
    // private window: Window,
    // private location: Location
  ) {

    route.params.subscribe(val => {
      this.userId = this.route.snapshot.params['userId'];
      this.getEditUserData(this.orgId, this.userId);

    });

  }

  //INITIATE FORMGROUP AND GET PATH VALUES BY PARAMS
  ngOnInit(): void {
    // this.orgId = this.route.snapshot.params['orgId'];
    this.orgId = this.adminAfaService.getOrgId();

    this.adminEditUserForm = this.fb.group({
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

    // get id as params from the routeLink
    this.userId = this.route.snapshot.params['userId'];
    this.getEditUserData(this.orgId, this.userId);

  }

  //FUNCTION TO GET AND PATH BY USER ID
  getEditUserData(org_id: any, user_id: any) {
    this.adminAfaService.getAfaUserDetail(org_id, user_id).subscribe(
      (response: any) => {
        const data = response.afa_data;

        const formData = {
          firstName: data.firstname,
          surName: data.surname,
          userName: data.username,
          email: data.email,
        };

        this.adminEditUserForm.patchValue(formData);

      },
      (error) => {
        console.log(error.error.error);
      }
    );
  }

  //CHECK FOR THE INPUT VALIDITY
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.adminEditUserForm.get(formcontrolName);
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  //CLOSE EDIT
  closeEdit() {
    this.adminAfaService.addBoxSizeChange();
    this.router.navigate([`app-admin/organisation/${this.orgId}/afausers`]);
    // this.adminEditUserForm.reset();
  }


  //FUNCTION TO SUBMIT THE EDITED FORM GROUP
  admin: any;
  onSubmit() {
    this.admin = this.auth.getUser();

    this.isLoading = true;

    if (this.adminEditUserForm.invalid) {
      this.toastr.error('Try again later', 'Failed');
    }
    const formData = this.adminEditUserForm.value;

    this.adminAfaService.updateAfaUserDetails(this.orgId, this.userId, formData, this.admin.user_id).subscribe(
      (response: any) => {
        console.log(response);
        this.toastr.success('AFA User updated successfully', 'Created');

        this.adminAfaService.addBoxSizeChange();
        this.isLoading = false;
        this.router.navigate([`app-admin/organisation/${this.orgId}/afausers`]);
      },
      (error) => {
        this.isLoading = false;

        console.log(error);
      }
    );

  }

}
