  import { Component, EventEmitter, Output } from '@angular/core';
  import { AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
  import { Router } from '@angular/router';
  import { CompanyService } from '../../../services/company.service';
import { CommonModule } from '@angular/common';
import { NumberOnlyDirective } from '../../../directive/number-only/number-only.directive';
import { CharWithSpaceDirective } from '../../../directive/char-with-space/char-with-space.directive';
import { CustomersService } from '../../../services/customers.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../auth/auth-services/auth.service';
import { SomeCharsDirective } from '../../../directive/some-chars-only/some-chars.directive';
import { LoaderComponent } from '../../loader/loader.component';

  @Component({
    selector: 'app-add-company',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule,CharWithSpaceDirective,
      NumberOnlyDirective,SomeCharsDirective, LoaderComponent],
    templateUrl: './add-company.component.html'
  })
  export class AddCompanyComponent {
  @Output() statusUpdated = new EventEmitter<void>

    addCompanyForm: FormGroup;
    fileChosen:any;
    selectedFile:any;
    formData:FormData = new FormData();
    companyId:any;
    organisationId:any
    public isLoading !: boolean;

  
    constructor(
      private fb: FormBuilder,
      private router: Router,
      private companyService: CompanyService,
      private customerService: CustomersService,
      private toast: ToastrService,
      private authService: AuthService
    ) {
      this.addCompanyForm = this.fb.group({
        companyDetails: this.fb.group({
          company_name: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
          email_address: ['', [Validators.required, Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$'), Validators.maxLength(75)]],
          phone_number: ['', [Validators.required, this.numericValidator(), Validators.maxLength(10), Validators.minLength(10)]],
          vat_percent: ['', Validators.required],
          company_description: ['', Validators.required]
        }),
        companyAddressDetails: this.fb.group({
          address_line_1: ['', [Validators.required, Validators.maxLength(100), Validators.minLength(5)]],
          address_line_2: ['', Validators.maxLength(100)],
          city: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
          state: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
          country: ['', [Validators.required, Validators.maxLength(50), Validators.minLength(3)]],
          pincode: ['', [Validators.required, this.numericValidator(), Validators.maxLength(6), Validators.minLength(6)]]
        })  
      });
    }
  
    ngOnInit(): void {
      this.organisationId = this.authService.getUser()['organisation_id'];
      if (this.addCompanyForm.valid) {
        console.log(this.addCompanyForm.value);
      }
    }
  
    numericValidator(): ValidatorFn {
      return (control: AbstractControl): ValidationErrors | null => {
        const isValid = /^[0-9]*$/.test(control.value);
        return isValid ? null : { numeric: true };
      };
    }
  
    addCompanyFormSubmit(): void {
      if (this.addCompanyForm.valid) {
        const companyData = {
          ...this.addCompanyForm.value.companyDetails,
          ...this.addCompanyForm.value.companyAddressDetails,
          organisation_id: this.organisationId
        };
  
        this.companyService.addCompany(companyData).subscribe({
          next: (result: any) => {
            this.companyId = result.data.company_id;
            if (this.selectedFile) {
              this.companyService.uploadCompanyImage(this.formData, this.companyId).subscribe({
                next: (result: any) => {
                  console.log(result);
                },
                error: (result: any) => {
                  console.log(result);
                },
              });
            }
            this.customerService.searchBoxSizeChange();
            this.statusUpdated.emit();

            this.router.navigate(['/companies']);
            this.toast.success(result['toaster_success']);

          },
          error: (result: any) => {
            console.log(result);
          }
        });
      } else {
        console.error('Form is not valid');
      }
    }

    onFileChange(event:any){
      const file =event.target.files[0];
      this.selectedFile = file;
      console.log("Hello World",this.selectedFile);
      this.formData.append('image',this.selectedFile, this.selectedFile.name);
  
      if (file) {
        this.fileChosen = file.name;
      } else {
        this.fileChosen = 'No file chosen';
      }
    }

    resetForm() {
      this.addCompanyForm.reset();
    }
  }