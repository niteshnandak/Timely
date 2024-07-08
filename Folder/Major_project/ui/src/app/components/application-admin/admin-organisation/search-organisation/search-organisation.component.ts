import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import {
  ReactiveFormsModule,
  FormControl,
  FormGroup,
  Validators,
  FormBuilder,
} from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AdminOrganisationsService } from '../../../../services/admin-services/admin-organisations.service';
import { ToastrModule, ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-search-organisation',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ToastrModule],
  templateUrl: './search-organisation.component.html',
  styleUrl: './search-organisation.component.css',
})

// CLASS: SearchOrganisationComponent
export class SearchOrganisationComponent {
  searchOrgForm!: FormGroup;

  constructor(
    private router: Router,
    private fb: FormBuilder,
    private orgService: AdminOrganisationsService,
    private toastr: ToastrService
  ) {
    this.initForm();
  }

  //FORM INITIATE DURING CONSTRUCTOR INITALIZATION
  initForm() {
    this.searchOrgForm = this.fb.group({
      organisationName: [
        null,
        [
          Validators.required,
          Validators.pattern('^[a-zA-Z 0-9]+$'),
          Validators.maxLength(30),
        ],
      ],
      status: [null],
    });
  }

  //ATLEAST ONE FIELD IS REQUIRED TO SEARCH
  isAnyFieldFilled(): boolean {
    const formValues = this.searchOrgForm.value;
    const organisationName = formValues.organisationName;
    const status = formValues.status;
    const isOrganisationNameFilled =
      organisationName !== null && organisationName.trim() !== '';
    const isStatusFilled = status !== null && status !== 'null';

    //return
    return isOrganisationNameFilled || isStatusFilled;
  }

  //CHECK INPUT VALIDITY TO REDUCE HTML CODE
  isInputValid(formControlName: string): boolean | undefined {
    const inputControlName = this.searchOrgForm.get(formControlName);

    //return
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  //RESET THE CREATED FORM
  resetForm() {
    this.searchOrgForm.reset();
    this.onSubmit();
  }

  //store searched data
  searchedData: any;
  searchFlag: boolean = false;

  //SEARCH SUBMIT
  onSubmit() {
    const formData = this.searchOrgForm.value;
    this.searchFlag = true;
    //using service store the data as observables and subject as next
    this.orgService.searchFormDataSend(formData, this.searchFlag);
  }
}
