import { CommonModule, DatePipe } from '@angular/common';
import { Component, ElementRef, ViewChild } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { GridModule, PageChangeEvent } from '@progress/kendo-angular-grid';
import { PayrollService } from '../../../../../services/payroll.service';
import { CompanyService } from '../../../../../services/company.service';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
// import { saveAs } from 'file-saver';
import { LoaderComponent } from '../../../../loader/loader.component';
import { ToastrService } from 'ngx-toastr';
import { maxYearValidator } from '../../../../../validations/date-validator';
import { SomeCharsDirective } from '../../../../../directive/some-chars-only/some-chars.directive';
import { Modal } from 'bootstrap';
import { finalize } from 'rxjs';



@Component({
  selector: 'app-payroll-batch-grid',
  standalone: true,
  imports: [GridModule, RouterLink, ReactiveFormsModule, CommonModule, LoaderComponent, RouterLink, SomeCharsDirective,DatePipe],
  templateUrl: './payroll-batch-grid.component.html'
})
export class PayrollBatchGridComponent {
  public gridData: any = {
    data: [],
    total: 0,
  };
  public totalRecords: number = 0;
  public loading: boolean = false;
  public skip: number = 0;
  public pageSize: number = 10;
  public companyName: string = '';
  showForm = false;
  payrollForm: FormGroup;
  payrollSearchForm!:FormGroup;
  public isLoading!: boolean
  @ViewChild('deletePayrollBatchModal') deleteModal!: ElementRef;
  private modal: Modal | null = null;
  private payrollBatchIdToDelete: number = 0;
  searchFormData:any;
  isSubmitting = false;

  constructor(
    private fb: FormBuilder,
    private  payrollService: PayrollService,
    private companyService: CompanyService,
    private authService: AuthService,
    private router: Router,
    private toastr: ToastrService) {
      this.payrollForm = this.fb.group({
        payroll_batch_name: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
        payroll_batch_date: ['', [Validators.required, this.dateValidator.bind(this)]]
      });

      this.payrollSearchForm = this.fb.group({
        payroll_batch_number: [null],
        payroll_batch_name: [null],
        payroll_batch_start_date: [null],
        payroll_batch_end_date: [null],
        payroll_batch_status: [null],
        
      });
  }

  ngOnInit(): void {
    this.loadPayrollBatches();
    this.loadCompanyName();
  }

  dateValidator(control: AbstractControl): ValidationErrors | null {
    const inputDate = new Date(control.value);
    const currentDate = new Date();
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 1); // Allow dates up to one month in the future

    const minDate = new Date();
    minDate.setFullYear(minDate.getFullYear() - 1); // Allow dates up to one year in the past

    if (isNaN(inputDate.getTime())) {
      return { 'invalidDate': true };
    }

    if (inputDate > maxDate) {
      return { 'futureDateTooFar': true };
    }

    if (inputDate < minDate) {
      return { 'pastDateTooFar': true };
    }

    return null;
  }

  handleDatePickerOpen(): void {
    const payrollBatchDateControl = this.payrollForm.get('payroll_batch_date');
    if (payrollBatchDateControl?.errors) {
      const currentDate = this.formatDate(new Date());
      payrollBatchDateControl.setValue(currentDate);
    }
  }

  formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);

    return `${year}-${month}-${day}`;
  }


  loadPayrollBatches(): void {
    this.loading = true;
    const companyId = this.companyService.getStoredCompanyId();
    const organisationId = this.authService.getUser()['organisation_id'];
    console.log(this.searchFormData);
    try {
      this.payrollService.getPayrollBatches(this.skip, this.pageSize, organisationId, companyId,this.searchFormData).subscribe({
        next: response => {
          this.gridData = {
            data: response.data,
            total: response.total,
          };
          this.loading = false;
        },
        error: error => {
          this.loading = false;
          this.toastr.error(error.error.toaster_error );
        }
      });
    } catch (error:any) {
      this.loading = false;
      this.toastr.error(error.error.toaster_error );
    }
  }

  loadCompanyName(): void {
    const companyId = this.companyService.getStoredCompanyId();
    if (companyId) {
      this.companyService.getCompanyId(companyId).subscribe({
        next: (response) => {
          this.companyName = response.company_name;
        },
        error: (error) => {
          console.error('Error loading company name');
        }
      });
    }
  }

  onPageChange(event: PageChangeEvent): void {
    console.log('Page change event:', event);
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadPayrollBatches();
  }


  toggleForm(): void {
    this.showForm = !this.showForm;
    this.payrollForm.reset()
  }

  onSubmit(): void {
    if (this.payrollForm.valid && !this.isSubmitting) {
      this.isSubmitting = true;
      const companyId = this.companyService.getStoredCompanyId();
      const formData = {
        payroll_batch_name: this.payrollForm.value.payroll_batch_name,
        payroll_batch_date: this.payrollForm.value.payroll_batch_date,
        company_id: companyId,
      };

      try {
        this.payrollService.addPayrollBatch(formData).pipe(
          finalize(() => {
            this.isSubmitting = false;
          })
        ).subscribe({
          next: response => {
            console.log(response['payroll_batch'].payroll_batch_id);
            const addedBatchId = response['payroll_batch'].payroll_batch_id;
            this.toggleForm();
            this.loadPayrollBatches();
            this.toastr.success(response['toaster_success']);
            this.router.navigateByUrl(`company/company-details/payroll-batch/${addedBatchId}/select-invoices`);
          },
          error: error => {
            this.toastr.error(error.error.toaster_error);
          }
        });
      } catch (error:any) {
        this.toastr.error(error.error.toaster_error);
      }
    }
  }

  pageRefresh(){
    this.payrollSearchForm.reset();
    this.searchFormData=this.payrollSearchForm.value;
    this.loadPayrollBatches();
  }


  viewPayrollBatchDetails(id: string, payroll_batch_status:string): void {
    console.log(id, payroll_batch_status);


    if(payroll_batch_status == 'Created'){
      this.router.navigateByUrl('company/company-details/payroll-batch/'+ id+ '/select-invoices');
    }else if(payroll_batch_status == 'Selected'){
      this.router.navigateByUrl('company/company-details/payroll-batch/'+ id+ '/verify-invoices');
    }
    else if(payroll_batch_status == 'Verified' || payroll_batch_status == 'Payrolled' || payroll_batch_status == 'Processing'){
      let flag = true;
      if(payroll_batch_status != 'Verified'){
        let flag = false;
      }
      this.router.navigateByUrl('company/company-details/payroll-batch/'+ id+ '/payroll-details', {state:{flag : true}});
    }

  }
  generateReport(payrollBatchId: any): void {
    this.isLoading = true;
    try {
      this.payrollService.generateReport(payrollBatchId).subscribe({
        next: (response: Blob) => {
          const url = window.URL.createObjectURL(response);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'Payroll_Summary_Report.xlsx';
          a.click();
          window.URL.revokeObjectURL(url);
          this.isLoading = false;
        },
        error: (error) => {
          this.isLoading = false;
          this.toastr.error(error.error.toaster_error);
        }
      });
    } catch (error:any) {
      this.isLoading = false;
      this.toastr.error(error.error.toaster_error);
    }
  }

  public openDeleteModal(id: number): void {
    this.payrollBatchIdToDelete = id;
    this.modal = new Modal(this.deleteModal.nativeElement);
    this.modal.show();
  }

  public confirmDeletePayrollBatch(): void {
    this.payrollService.deletePayrollBatch(this.payrollBatchIdToDelete).subscribe({
      next: (response: any) => {
        this.loadPayrollBatches();
        this.toastr.success(response['message']);
        this.modal?.hide();
      },
      error: (error) => {
        this.toastr.error('An error occurred while deleting the payroll batch');
        this.modal?.hide();
      }
    });
  }

updatePayrollRun(payroll_batch_id:any){

  this.payrollService.updatePayrollProcess(payroll_batch_id).subscribe({
    next: (response:any)=>{
      this.loadPayrollBatches();
      this.toastr.success(response['toaster_success'],'Success');
    },
    error: (msg:any)=>{
      this.toastr.error(msg["toaster_error"], 'Error');
    }
  })

}


searchPayrollBatchResetForm(){
  this.payrollSearchForm.reset();
  this.searchFormData = this.payrollSearchForm.value;
  this.loadPayrollBatches();

  }
  onSearchSubmit(){
    this.searchFormData = this.payrollSearchForm.value;
    this.skip = 0
    this.loadPayrollBatches();
  }
}
