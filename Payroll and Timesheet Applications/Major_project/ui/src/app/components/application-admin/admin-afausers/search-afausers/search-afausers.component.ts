import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { AdminAfausersService } from '../../../../services/admin-services/admin-afausers.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-search-afausers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './search-afausers.component.html',
  styleUrl: './search-afausers.component.css'
})

// CLASS: SearchAfausersComponent
export class SearchAfausersComponent {

  adminSearchUserForm!: FormGroup;
  orgId!: number;

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private adminAfaService: AdminAfausersService,
    private toastr: ToastrService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    // this.orgId = this.route.snapshot.params['orgId'];
    this.orgId = this.adminAfaService.getOrgId();
    // console.log(this.orgId);

    // Initializing the Search form Data
    this.adminSearchUserForm = this.fb.group({
      firstName: [
        null,
        [
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
          Validators.minLength(3),
        ],
      ],
      surName: [
        null,
        [
          Validators.pattern('^[a-zA-Z]+$'),
          Validators.maxLength(30),
          Validators.minLength(3),
        ],
      ],
      userName: [
        null,
        [
          Validators.pattern('^[a-zA-Z0-9._]{3,50}$'),
          Validators.maxLength(30),
          Validators.minLength(3),
        ],
      ],
      email: [
        null,
        [
          Validators.email
        ]
      ],
      status: [null],
      activity: [null],
      lastActive: [
        null,
        [
          Validators.pattern(/^\d{4}-\d{2}-\d{2}$/)
        ]
      ],
    });
  }

  //CHECK INPUT VALIDITY TO REDUCE HTML CODE
  isInputValid(formControlName: string): boolean | undefined {
    const inputControlName = this.adminSearchUserForm.get(formControlName);
    return (
      inputControlName?.invalid &&
      (inputControlName.dirty || inputControlName.touched)
    );
  }

  //ATLEAST ONE FIELD IS REQUIRED TO SEARCH
  isAnyFieldFilled(): boolean {
    const formValues = this.adminSearchUserForm.value;
    return Object.values(formValues).some(value => value !== null && value !== '');
  }

  //RESET THE CREATED FORM
  resetForm() {
    this.adminSearchUserForm.reset();
    // this.adminAfaService.addBoxSizeChange();
  }

  searchedUserData: any;
  searchFlag: boolean = false;
  //SEARCH SUBMIT
  onSubmit() {
    const formData = this.adminSearchUserForm.value;

    this.searchFlag = true;
    this.adminAfaService.searchFormDataSend(formData, this.searchFlag);

  }


}
