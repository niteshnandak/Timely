import { Component, ViewChild } from '@angular/core';
import { InvoiceDashboardComponent } from '../invoice-dashboard/invoice-dashboard.component';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { HeaderComponent } from '../../header/header.component';
import { InvoiceService } from '../../services/invoice.service';
import { ToastrModule, ToastrService } from 'ngx-toastr';
import { CharOnlyDirective } from '../../directive/char-only/char-only.directive';
import { NumberOnlyDirective } from '../../directive/number-only/number-only.directive';
import { Router } from '@angular/router';
import { ReportsDataService } from '../../services/reports-data.service';
import { RouterLink } from '@angular/router';
import { maxYearValidator } from '../../validators/custom-validator';
@Component({
  selector: 'app-invoice-home',
  standalone: true,
  imports: [
    InvoiceDashboardComponent,
    InvoiceHomeComponent,
    CommonModule,
    ReactiveFormsModule,
    HeaderComponent,
    CharOnlyDirective,
    NumberOnlyDirective,
    RouterLink
  ],
  templateUrl: './invoice-home.component.html',
  styleUrl: './invoice-home.component.css'
})
export class InvoiceHomeComponent {

  isCollapsed : boolean = false;
  showInvoiceForm: boolean = false;
  newInvoiceForm !: FormGroup;

  showGenerateReports : boolean = false;
  generateReportsForm !: FormGroup;

  reportsData: any;

  @ViewChild('invoiceDashboard') invoiceDashboard !: InvoiceDashboardComponent;

  constructor(
    private router : Router,
    private formBuilder : FormBuilder,
    private invoiceService: InvoiceService,
    private reportsDataService: ReportsDataService,
    private toastService : ToastrService
  ){
    this.newInvoiceForm = this.formBuilder.group({
      worker_name: ['', [Validators.required, Validators.pattern(/^[a-zA-Z][a-zA-Z ]*$/)]],
      worker_id: ['', [Validators.required, Validators.pattern(/^[0-9]*$/)]],
      invoice_date: ['', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/), maxYearValidator()]],
      hourly_pay: ['', [Validators.required, Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/)]],
      hours_worked: ['', [Validators.required, Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/)]],
      organisation: ['', Validators.required],
      timesheet_id: [0],
    });

    this.generateReportsForm = this.formBuilder.group({
      worker_id: [''],
      organisation: [''],
      dateFrom: ['', (Validators.pattern(/^\d{4}-\d{2}-\d{2}$/), maxYearValidator())],
      dateTo: ['', (maxYearValidator())] // (Validators.pattern(/^\d{4}-\d{2}-\d{2}$/))
    }, { validators: [this.dateValidator, this.workerIdValidator] });
  }

  resetForm(){
    this.newInvoiceForm.reset();
  }

  clearInvalidDate(event: any) {
    const timesheetDateControl = this.newInvoiceForm.get('invoice_date');
    if (timesheetDateControl && this.isYearInvalid(timesheetDateControl.value)) {
      const date = new Date(timesheetDateControl.value);
      date.setFullYear(new Date().getFullYear()); // Set the year to the current year
      event.target.value = date.toISOString().slice(0, 10);
      timesheetDateControl.setValue(event.target.value); // Update the form control value
      timesheetDateControl.markAsPristine(); // Update the input value with the modified date
    }

    const generateDateFromControl = this.generateReportsForm.get('dateFrom');
    if (generateDateFromControl && generateDateFromControl.invalid) {
      event.target.value = '';
    }

    const generateDateToControl = this.generateReportsForm.get('dateTo');
    if (generateDateToControl && generateDateToControl.invalid) {
      event.target.value = '';
    }
  }

  isYearInvalid(dateValue: string): boolean {
    if (dateValue) {
      const selectedYear = new Date(dateValue).getFullYear();
      const currentYear = new Date().getFullYear();
      return selectedYear > currentYear;
    }
    return false;
  }

  workerIdValidator(formGroup: FormGroup) {
    const value = formGroup.get('worker_id')?.value;

    if(value && isNaN(value)){
      // console.log('hi');
      // formGroup.get('dateTo')?.setErrors({ notANumber: true });
      return { notANumber: true };
    }
    formGroup.get('worker_id')?.setErrors(null);
    return null;
  }

  isAnyFieldFilled(): boolean {
    const formValues = this.generateReportsForm.value;
    return Object.values(formValues).some(value => value !== null && value !== '');
  }

  // Custom validator for dateFrom and dateTo
  dateValidator(formGroup: FormGroup) {
    const dateFrom = formGroup.get('dateFrom')?.value;
    const dateTo = formGroup.get('dateTo')?.value;

    // Check if dateTo matches the pattern
    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
    const isDateToValid = dateTo ? datePattern.test(dateTo) : true;

    if (dateFrom && dateTo && dateTo < dateFrom) {
      formGroup.get('dateTo')?.setErrors({ dateRangeInvalid: true });
      return { dateRangeInvalid: true };
    }
    else if (!isDateToValid) {
      formGroup.get('dateTo')?.setErrors({ patternInvalid: true });
      return { patternInvalid: true };
  }
    else {
      formGroup.get('dateTo')?.setErrors(null);
      return null;
    }
  }



  toggleInvoiceForm() {
    this.showInvoiceForm = !this.showInvoiceForm;
    this.newInvoiceForm.reset();
  }

  addNewInvoice(){
    this.invoiceDashboard.gridLoading= true;
    console.log(this.newInvoiceForm.value)
    this.invoiceService.createInvoice(this.newInvoiceForm.value).subscribe({
      next:(response)=>{
        this.invoiceDashboard.loadInvoices(this.invoiceDashboard.skip, this.invoiceDashboard.take);
        this.showInvoiceForm = !this.showInvoiceForm;
        this.toastService.success(response.Success);
      },
      error:(err) => {
        console.log("error occured", err);
        this.showInvoiceForm = !this.showInvoiceForm;
        this.toastService.error(err.error.Error);
        this.invoiceDashboard.gridLoading=false;
      }
    })
  }


  toggleGenerateReports() {
    console.log('Toggle Generate Reports OffCanvas');
    this.showGenerateReports = !this.showGenerateReports;
    this.isCollapsed = false;

    // Clear form values on offcanvas close (assuming 'worker_id', etc. are form control names)
    if (!this.showGenerateReports) {
      this.generateReportsForm.reset();
      // this.generateReportsForm.get('worker_id')?.setValue('');
      // this.generateReportsForm.get('organisation')?.setValue('');
      // this.generateReportsForm.get('dateFrom')?.setValue('');
      // this.generateReportsForm.get('dateTo')?.setValue('');
    }
  }

  resetGenerateForm(){
    this.generateReportsForm.reset();
  }

  // formData:FormData = new FormData();

  GenerateReports() {
    console.log(this.generateReportsForm.value);

    // if (this.generateReportsForm.valid) {}

    this.invoiceService.showReports(this.generateReportsForm.value).subscribe(
      (response) => {
        console.log(response);
        this.reportsData = response;
        sessionStorage.setItem('reportsData', JSON.stringify(this.reportsData)); // storing the reports data in session storage
        // this.reportsDataService.setReportsData(this.reportsData);
        this.router.navigateByUrl("invoices/download-reports");
      },
      (error) => {
        console.error('Error downloading report:', error);
      }
    );

  }

}
