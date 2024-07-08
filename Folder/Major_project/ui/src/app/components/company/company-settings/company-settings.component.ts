import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink, RouterOutlet } from '@angular/router';
import { CompanyService } from '../../../services/company.service';
import { ToastrService } from 'ngx-toastr';
import { NumberOnlyDirective } from '../../../directive/number-only/number-only.directive';
import { SomeCharsDirective } from '../../../directive/some-chars-only/some-chars.directive';
import { LoaderComponent } from '../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-company-settings',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule, RouterLink, NumberOnlyDirective, SomeCharsDirective,LoaderComponent, RouterOutlet ],
  templateUrl: './company-settings.component.html'
})
export class CompanySettingsComponent {
  companySettingsForm: FormGroup;
  companyId: string | null = null;
  fileChosen: string = 'No file chosen';
  selectedFile: any;
  public serverUrl: string = 'http://127.0.0.1:8000/api/org-logo/';
  public imageUrl: string = '';
  public company_logo_path !: string ;
  formData: FormData = new FormData();
  public isLoading:boolean = true;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private companyService: CompanyService,
    private toast: ToastrService,
    private title: Title

  ) {
    this.companySettingsForm = this.fb.group({
      company_name: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      email_address: ['', [Validators.required, Validators.pattern('^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$'),  Validators.maxLength(75)]],
      phone_number: ['', [Validators.required, Validators.pattern('^[0-9]{10}$'), Validators.minLength(10), Validators.maxLength(10)]],
      vat_percent: ['0%', Validators.required],
      address_line_1: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      address_line_2: ['', [Validators.maxLength(50)]],
      city: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      state: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      country: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      pincode: ['', [Validators.required, Validators.pattern('^[0-9]{6}$'),Validators.minLength(6), Validators.maxLength(6)]],
      company_description: ['', Validators.required]
    });
    this.title.setTitle('Company Settings');
  }

  ngOnInit(): void {
    this.companyId = this.companyService.getStoredCompanyId();
    if (this.companyId) {
      this.loadCompanyData(this.companyId);
      console.log(this.companyId)
    } else {
      console.error('Company ID not found in session');
      this.router.navigate(['/dashboard']);
    }
  }

  private loadCompanyData(companyId: string): void {
    this.companyService.getCompanyId(companyId).subscribe(
      data => {
        this.companySettingsForm.patchValue({
          company_name: data.company_name,
          email_address: data.email_address,
          phone_number: data.phone_number,
          vat_percent: data.vat_percent,
          company_description: data.company_description,
          address_line_1: data.address?.address_line_1,
          address_line_2: data.address?.address_line_2,
          city: data.address?.city,
          state: data.address?.state,
          country: data.address?.country,
          pincode: data.address?.pincode
        });
        this.company_logo_path = `${this.serverUrl}${data.company_logo_path}`;
        this.isLoading = false;
      },
      error => {
        console.error('Error fetching company data', error);
        this.router.navigate(['/dashboard']);
      }
    );
  }


  onSubmit(): void {
    if (this.companySettingsForm.valid && this.companyId) {
      this.companyService.updateCompany(this.companyId, this.companySettingsForm.value).subscribe(
        response => {
          this.toast.success(response['toaster_success']);
          this.router.navigateByUrl('/company/company-details');
        },
        error => {
          console.error('Error updating company', error);
          this.toast.error(error['toaster_error']);
        }
      );
    }
  }

  onFileChange(event: any): void {
    const file = event.target.files[0];
    this.selectedFile = file;
    this.formData.append('image', this.selectedFile, this.selectedFile.name);

    if (file && file.type.startsWith('image')) {
      this.fileChosen = file.name;
      this.company_logo_path = URL.createObjectURL(file);
      this.companySettingsForm.patchValue({
        company_logo: this.imageUrl
      });
    } else {
      this.fileChosen = 'No file chosen';
      this.company_logo_path = '';
      this.clearFile(event);
      this.toast.error("File format not valid");
    }
  }

  private clearFile(event: any){
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
      this.selectedFile = event.target.files[0];
    }
  }

  // onImageUpload() {
  //   if (this.selectedFile) {
  //     this.companyService.uploadCompanyImage(this.formData, this.companyId).subscribe((response: any) => {
  //       console.log(response);
  //       this.toast.success(response['toaster_success']);

  //     });
  //     this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
  //       this.router.navigate(['/company/company-details/company-settings']);
  //     });
  //   }
  // }


onImageUpload(event: any): void {
  if(!this.selectedFile){
    this.toast.info("Select an image to Upload");
  }
    if (this.selectedFile && this.companyId) {
      this.isLoading = true;
      this.companyService.uploadCompanyImage(this.formData, this.companyId).subscribe(
        (response: any) => {
          this.isLoading = false;
          this.fileChosen = 'No image Chosen';
          this.toast.success(response['toaster_success']);
        },
        error => {
          this.isLoading = false;
          console.error('Error uploading image', error);
          this.toast.error(error['toaster_error']);
        }
      );
    }
  }

  onCancel(): void {
    this.companySettingsForm.reset();
  }

  private reloadPage(): void {
    this.router.navigateByUrl('/', { skipLocationChange: true }).then(() => {
      this.router.navigate(['/company/company-details/company-settings']);
    });
  }

}
