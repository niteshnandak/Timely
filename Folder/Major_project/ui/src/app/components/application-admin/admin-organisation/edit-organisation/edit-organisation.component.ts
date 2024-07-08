import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AdminOrganisationsService } from '../../../../services/admin-services/admin-organisations.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../loader/loader.component';

@Component({
  selector: 'app-edit-organisation',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, HttpClientModule,LoaderComponent],
  templateUrl: './edit-organisation.component.html',
  styleUrls: ['./edit-organisation.component.css'],
})

//CLASS: EditOrganisationComponent
export class EditOrganisationComponent implements OnInit {
  editAdminOrgForm!: FormGroup;
  orgId: any;
  public isLoading!: boolean;

  constructor(
    private fb: FormBuilder,
    private orgService: AdminOrganisationsService,
    private toastr: ToastrService,
    private router: Router,
    private route: ActivatedRoute,
    private auth: AuthService
  ) {}

  //INITIATE FORMGROUP AND GET PATH VALUES BY PARAMS
  ngOnInit(): void {
    //formgroup
    this.editAdminOrgForm = this.fb.group({
      organisationName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z0-9 .-_&]+$'),
          Validators.maxLength(30),
        ],
      ],
      emailAddress: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$'),
        ],
      ],
      description: [null, [Validators.maxLength(250)]],
      contactNumber: [
        null,
        [Validators.required, Validators.pattern('^[0-9]{10}$')],
      ],
    });

    // get id as params from the routeLink
    this.orgId = this.route.snapshot.params['id'];
    this.getEditOrgData(this.orgId);
  }

  //FUNCTION TO GET AND PATH BY ORG ID
  getEditOrgData(org_id: any) {
    this.isLoading=true;
    this.orgService.getOrgDetails(org_id).subscribe(

      //response
      (res: any) => {
        const data = res['org_data'];
        const formData = {
          organisationName: data.name,
          emailAddress: data.email_address,
          description: data.description,
          contactNumber: data.contact_number,
        };
        this.editAdminOrgForm.patchValue(formData);
        this.isLoading=false;

      },

      //error
      (error: any) => {
        this.isLoading=false;

        console.log(error);
      }
    );
  }

  //CHECK FOR THE INPUT VALIDITY
  isInputValid(formcontrolName: string): boolean | undefined {
    const inputControlName = this.editAdminOrgForm.get(formcontrolName);

    //return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  //CLOSE EDIT MOVE TO ORGANISATION
  closeEdit() {
    this.orgService.addBoxSizeChange();
    this.router.navigate([`app-admin/organisation`]);
  }
  admin: any;

  //FUNCTION TO SUBMIT THE EDITED FORM GROUP
  onSubmit() {
    this.admin = this.auth.getUser();
    const formData = this.editAdminOrgForm.value;
    formData.adminId = this.admin.user_id;
    this.orgService.updateOrgDetails(this.orgId, formData).subscribe(

      //response
      (res: any) => {
        console.log(res);
        this.toastr.success(res.message, 'Updated');
        this.orgService.addBoxSizeChange();
        this.router.navigateByUrl('/app-admin/organisation');
      },

      //error
      (error: any) => {
        //error message based on the constraint
        if (error.error.errors.email && error.error.errors.contactNumber) {
          this.toastr.error(
            'The email & contact number has already been taken.',
            'Error'
          );
        } else if (error.error.errors.email) {
          this.toastr.error(error.error.errors.email, 'Error');
        } else if (error.error.errors.contactNumber) {
          this.toastr.error(error.error.errors.contactNumber, 'Error');
        } else {
          this.toastr.error('Something went wrong try after sometime', 'Error');
        }
        console.log(error);
      }
    );
  }
}
