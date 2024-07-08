// import { NumbersAndcharsDirective } from './../../../../../directive/num-and-chars/numbers-andchars.directive';
// import { ModalComponent } from '../modal/modal.component';
// import { CommonModule, DatePipe } from '@angular/common';
// import { Component, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
// import { DataItem, GridModule, PagerPosition, PagerType } from '@progress/kendo-angular-grid';
// import { LoaderComponent } from '../../../../loader/loader.component';
// import { Router, RouterLink } from '@angular/router';
// import { FormBuilder, FormGroup, FormsModule, Validators, ReactiveFormsModule, FormControl, AbstractControl, ValidationErrors } from '@angular/forms';
// import { ToastrModule, ToastrService } from 'ngx-toastr';
// import { TimesheetService } from '../../../../../services/timesheet.service';
// import { CompanyService } from '../../../../../services/company.service';
// import { DecimalNumberOnlyDirective } from '../../../../../directive/decimal-only/decimal-only.directive';
// import { Title } from '@angular/platform-browser';

// @Component({
//   selector: 'app-timesheet-dashboard',
//   standalone: true,
//   imports: [GridModule,
//     CommonModule,
//     LoaderComponent,
//     RouterLink,
//     ModalComponent,
//     ReactiveFormsModule,
//     NumbersAndcharsDirective,
//     DecimalNumberOnlyDirective,
//     DatePipe
//   ],
//   templateUrl: './timesheet-dashboard.component.html',
//   styleUrl: './timesheet-dashboard.component.css',
//   schemas: [CUSTOM_ELEMENTS_SCHEMA]
// })
// export class TimesheetDashboardComponent {

//   public isLoading!: boolean;
//   addTimesheetForm!: FormGroup;
//   uploadTimesheetForm !: FormGroup;
//   timesheets: any[] = [];
//   total: number = 0;
//   currentPage: number = 1;
//   perPage: number = 10;
//   company_id: any;
//   company_name: any;
//   gridLoading !: boolean;
//   timesheet_id !: any;
//   deleteTimesheetId: any;
//   searchTimesheetForm: any;
//   isSearchActive: boolean = false;

//   public type: PagerType = "numeric";
//   public buttonCount = 5;
//   public info = true;
//   public pageSizes = [5, 10, 20,50];
//   public previousNext = true;
//   public position: PagerPosition = "bottom";

//   constructor(
//     private timesheetService: TimesheetService,
//     private companyService: CompanyService,
//     private fb: FormBuilder,
//     private toastr: ToastrService,
//     private router: Router,
//     private title: Title
//   ) {
//     this.title.setTitle('Timesheets')
//   }

//   ngOnInit() {
//     this.isLoading = true;
//     this.initializeFormGroup();
//     this.loadTimesheets(this.currentPage, this.perPage);
//   }

//   loadTimesheets(page: number, perPage: number) {
//     this.gridLoading = true;
//     this.company_id = this.companyService.getStoredCompanyId();
//     this.timesheetService.getAllTimesheets(page, perPage, this.company_id).subscribe({
//         next: data => {
//           this.company_name = data.company_name;
//           this.timesheets = data.timesheets;
//           this.total = data.timesheets.total;
//           this.currentPage = page;
//           this.gridLoading = false;
//           this.isLoading = false;
//         },
//         error: error => {
//           console.error('Error fetching timesheets', error);
//         }
//     });
//   }

//   onPageChange(event: any) {
//     this.perPage=event.take;
//     this.currentPage = (event.skip / event.take) + 1;
//     // this.loadTimesheets(this.currentPage, this.perPage);
//     if (this.isSearchActive) {
//       this.searchTimesheetFormSubmit();
//     } else {
//       this.loadTimesheets(this.currentPage, this.perPage);
//     }
//   }

//   navigateToTimesheetDetails(dataItem: any): void {
//     this.timesheet_id = dataItem.timesheet_id;
//     if (dataItem.upload_type == 'manual') {
//       this.router.navigate([`/company/company-details/timesheets/${dataItem.timesheet_id}/timesheet-details`]);
//     } else {
//       this.router.navigate([`/company/company-details/${dataItem.timesheet_id}/uploaded-timesheet`]);
//       console.log("csv timesheet");
//     }
//   }

//   initializeFormGroup() {
//     this.addTimesheetForm = new FormGroup({
//       timesheet_name: new FormControl('', [Validators.required]),
//       period_end_date: new FormControl('', [Validators.required, this.dateValidator.bind(this)]),
//       invoice_date: new FormControl('', [Validators.required, this.dateValidator.bind(this)])
//     })

//     this.searchTimesheetForm = new FormGroup({
//       timesheet_num: new FormControl(null),
//       timesheet_name: new FormControl(null),
//       period_end_date: new FormControl(null),
//       upload_type: new FormControl(null),
//       invoice_status: new FormControl(null)
//     })

//     //Upload Timesheet Form Group
//     this.uploadTimesheetForm = this.fb.group({
//       timesheet_file: [null],
//       timesheet_name: ['', Validators.required],
//       period_end_date: ['', [Validators.required, this.dateValidator.bind(this)]],
//       invoice_date: ['', [Validators.required, this.dateValidator.bind(this)]]
//     })

//   }

//   get timesheetName() {
//     return this.addTimesheetForm.get('timesheet_name')
//   }
//   get periodEndDate() {
//     return this.addTimesheetForm.get('period_end_date')
//   }
//   get invoiceDate() {
//     return this.addTimesheetForm.get('invoice_date')
//   }

//   addTimesheetFormSubmit() {
//     console.log(this.addTimesheetForm.value);
//     this.isLoading = true;
//     this.timesheetService.createTimesheet(this.addTimesheetForm.value, this.company_id).subscribe({
//         next: response => {
//           this.timesheet_id = response.timesheet['timesheet_id'];
//           this.toastr.success('Timesheet created successfully');
//           this.loadTimesheets(this.currentPage, this.perPage);
//           this.closeModal('create');
//           this.isLoading = false;
//         },
//         error: error => {
//           this.toastr.error('Error creating timesheet');
//         }
//     });
//   }

//   resetForm(form: any) {
//     form.reset();
//     console.log(form);
//   }

//   setDataItem(value: any) {
//     this.deleteTimesheetId = value;
//   }

//   // Delete a timesheet completely
//   deleteTimesheetData(timesheet_id: Number)
//   {
//       this.isLoading = true;
//       this.timesheetService.deleteTimesheet(timesheet_id).subscribe({
//         next:(result:any)=>{
//           this.loadTimesheets(this.currentPage, this.perPage);
//           this.isLoading = false;
//           if(result.message.includes('already')){
//             this.toastr.warning(result['message']);
//           }else{
//             this.toastr.success(result["message"]);
//           }
//         },
//         error:(result:any)=>{
//           this.toastr.error(result["message"]);
//         }
//     });
//   }

//   // On submitting the Search form
//   searchTimesheetFormSubmit() {
//     this.isSearchActive = true;
//     this.gridLoading = true;
//     console.log(this.searchTimesheetForm.value);
//     this.timesheetService.searchTimesheet(this.currentPage, this.perPage, this.company_id, this.searchTimesheetForm.value).subscribe({
//         next: (result: any) => {
//           this.timesheets = result.result;
//           this.total = result.total;
//           this.gridLoading = false;
//         },
//         error: error => {
//           console.error('Error searching timesheets', error);
//           this.gridLoading = false;
//         }
//     });
//   }

//   //Resetting the search form
//   searchTimesheetResetForm() {
//     this.searchTimesheetForm.reset();
//     this.isSearchActive = false;
//     this.loadTimesheets(this.currentPage, this.perPage);
//   }

//   // Modal Visibility Variables
//   createModalVisible = false;
//   uploadModalVisible = false;

//   // To make the modal visible for create or Upload based on parameters
//   openModal(modal: string) {
//     if (modal == "upload") {
//       this.uploadModalVisible = true;
//     } else {
//       this.createModalVisible = true;
//     }
//   }

//   // Closing the modal
//   closeModal(modal: string) {
//     if (modal == 'upload') {
//       this.uploadModalVisible = false;
//       this.resetForm(this.uploadTimesheetForm);
//     } else {
//       this.resetForm(this.addTimesheetForm);
//       this.createModalVisible = false;
//     }
//   }


//   //Appending the file to the form only if conditions are satisfied
//   // onFileChange(event: any) {
//   //   if (event.target.files.length > 0) {
//   //     const file = event.target.files[0];

//   //     const fileName = file.name;
//   //     const dotIndex = fileName.lastIndexOf('.');
//   //     const extension = fileName.slice(dotIndex + 1);

//   //     if (extension == 'csv') {
//   //       this.uploadTimesheetForm.patchValue({
//   //         timesheet_file: file
//   //       })
//   //     } else {
//   //       this.toastr.error("File format is invalid");
//   //       this.clearFile(event);
//   //     }
//   //   }
//   // }
//   onFileChange(event: any) {
//     if (event.target.files.length > 0) {
//       const file = event.target.files[0];

//       const fileName = file.name;
//       const dotIndex = fileName.lastIndexOf('.');
//       const extension = fileName.slice(dotIndex + 1).toLowerCase();

//       if (['csv', 'jpg', 'jpeg', 'png', 'pdf'].includes(extension)) {
//         this.uploadTimesheetForm.patchValue({
//           timesheet_file: file
//         })
//       } else {
//         this.toastr.error("File format is invalid");
//         this.clearFile(event);
//       }
//     }
//   }

//   private clearFile(event: any) {
//     const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
//     if (fileInput) {
//       fileInput.value = '';
//     }
//   }

//   refreshData(){
//     this.loadTimesheets(this.currentPage, this.perPage);
//     this.resetForm(this.searchTimesheetForm);
//   }

//   // Date picker for upload Timesheet Form
//   handleDatePickerOpen(dateField: any): void {
//     const payrollBatchDateControl = dateField;
//     if (payrollBatchDateControl?.errors) {
//       const currentDate = this.formatDate(new Date());
//       payrollBatchDateControl.setValue(currentDate);
//     }
//   }

//   // Format the date for input field
//   formatDate(date: Date): string {
//     const year = date.getFullYear();
//     const month = ('0' + (date.getMonth() + 1)).slice(-2);
//     const day = ('0' + date.getDate()).slice(-2);
//     return `${year}-${month}-${day}`;
//   }

//   // Validate dates allow only dates from a year in the past to a year in the future
//   dateValidator(control: AbstractControl): ValidationErrors | null {
//     const inputDate = new Date(control.value);
//     const currentDate = new Date();
//     const maxDate = new Date();

//     maxDate.setFullYear(maxDate.getFullYear() + 1);
//     const minDate = new Date();
//     minDate.setFullYear(minDate.getFullYear() - 1);

//     if (isNaN(inputDate.getTime())) {
//       return { 'invalidDate': true };
//     }
//     if (inputDate > maxDate) {
//       return { 'futureDateTooFar': true };
//     }
//     if (inputDate < minDate) {
//       return { 'pastDateTooFar': true };
//     }
//     return null;
//   }

//   //On submitting the uploadTimesheetForm, append into formData and send it in request
//   // onSubmit() {
//   //   if (this.uploadTimesheetForm.get('timesheet_file')!.value == null) {
//   //     this.toastr.warning("select a file to upload")
//   //   } else {
//   //     const formData = new FormData();

//   //     formData.append('file', this.uploadTimesheetForm.get('timesheet_file')!.value);
//   //     formData.append('timesheet_name', this.uploadTimesheetForm.get('timesheet_name')!.value);
//   //     formData.append('invoice_date', this.uploadTimesheetForm.get('invoice_date')!.value);
//   //     formData.append('period_end_date', this.uploadTimesheetForm.get('period_end_date')!.value);
//   //     formData.append('company_id', this.company_id);

//   //     console.log(formData);

//   //     this.isLoading = true;
//   //     this.timesheetService.uploadTimesheet(formData).subscribe(
//   //       (result) => {
//   //         console.log("response", result);
//   //         if (result.message.includes("Uploaded")) {
//   //           this.toastr.success(result.message);
//   //           this.isLoading = false;
//   //           this.closeModal('upload');
//   //           this.resetForm(this.uploadTimesheetForm);

//   //           // If there are rows with missing fields, they are dropped
//   //           // This toastr tells Rows have been dropped not what rows !
//   //           if (result.missing_fields.length !== 0) {
//   //             this.toastr.info("Few Rows Were dropped because of incomplete Information");
//   //           }
//   //           this.router.navigate([`/company/company-details/${result.timesheet_id}/uploaded-timesheet`]);
//   //         } else {
//   //           this.toastr.warning(result.message);
//   //           this.isLoading = false;
//   //         }
//   //       }
//   //     )
//   //   }
//   // }
//   onSubmit() {
//     if (this.uploadTimesheetForm.get('timesheet_file')!.value == null) {
//       this.toastr.warning("select a file to upload")
//     } else {
//       const formData = new FormData();

//       formData.append('file', this.uploadTimesheetForm.get('timesheet_file')!.value);
//       formData.append('timesheet_name', this.uploadTimesheetForm.get('timesheet_name')!.value);
//       formData.append('invoice_date', this.uploadTimesheetForm.get('invoice_date')!.value);
//       formData.append('period_end_date', this.uploadTimesheetForm.get('period_end_date')!.value);
//       formData.append('company_id', this.company_id);

//       console.log(formData);

//       this.isLoading = true;
//       this.timesheetService.uploadTimesheet(formData).subscribe(
//         (result) => {
//           console.log("response", result);
//           if (result.message.includes("Uploaded")) {
//             this.toastr.success(result.message);
//             this.isLoading = false;
//             this.closeModal('upload');
//             this.resetForm(this.uploadTimesheetForm);

//             // If there are rows with missing fields, they are dropped
//             // This toastr tells Rows have been dropped not what rows !
//             if (result.missing_fields.length !== 0) {
//               this.toastr.info("Few Rows Were dropped because of incomplete Information");
//             }
//             this.router.navigate([`/company/company-details/${result.timesheet_id}/uploaded-timesheet`]);
//           } else {
//             this.toastr.warning(result.message);
//             this.isLoading = false;
//           }
//         }
//       )
//     }
//   }
// }
import { NumbersAndcharsDirective } from './../../../../../directive/num-and-chars/numbers-andchars.directive';
import { ModalComponent } from '../modal/modal.component';
import { CommonModule, DatePipe } from '@angular/common';
import { Component, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { DataItem, GridModule, PagerPosition, PagerType } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, FormGroup, FormsModule, Validators, ReactiveFormsModule, FormControl, AbstractControl, ValidationErrors } from '@angular/forms';
import { ToastrModule, ToastrService } from 'ngx-toastr';
import { TimesheetService } from '../../../../../services/timesheet.service';
import { CompanyService } from '../../../../../services/company.service';
import { DecimalNumberOnlyDirective } from '../../../../../directive/decimal-only/decimal-only.directive';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-timesheet-dashboard',
  standalone: true,
  imports: [GridModule,
    CommonModule,
    LoaderComponent,
    RouterLink,
    ModalComponent,
    ReactiveFormsModule,
    NumbersAndcharsDirective,
    DecimalNumberOnlyDirective,
    DatePipe
  ],
  templateUrl: './timesheet-dashboard.component.html',
  styleUrls: ['./timesheet-dashboard.component.css'],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class TimesheetDashboardComponent {

  public isLoading!: boolean;
  addTimesheetForm!: FormGroup;
  uploadTimesheetForm !: FormGroup;
  timesheets: any[] = [];
  total: number = 0;
  currentPage: number = 1;
  perPage: number = 10;
  company_id: any;
  company_name: any;
  gridLoading !: boolean;
  timesheet_id !: any;
  deleteTimesheetId: any;
  searchTimesheetForm: any;
  isSearchActive: boolean = false;

  public type: PagerType = "numeric";
  public buttonCount = 5;
  public info = true;
  public pageSizes = [5, 10, 20,50];
  public previousNext = true;
  public position: PagerPosition = "bottom";

  constructor(
    private timesheetService: TimesheetService,
    private companyService: CompanyService,
    private fb: FormBuilder,
    private toastr: ToastrService,
    private router: Router,
    private title: Title
  ) {
    this.title.setTitle('Timesheets')
  }

  ngOnInit() {
    this.isLoading = true;
    this.initializeFormGroup();
    this.loadTimesheets(this.currentPage, this.perPage);
  }

  loadTimesheets(page: number, perPage: number) {
    this.gridLoading = true;
    this.company_id = this.companyService.getStoredCompanyId();
    this.timesheetService.getAllTimesheets(page, perPage, this.company_id).subscribe({
        next: data => {
          this.company_name = data.company_name;
          this.timesheets = data.timesheets;
          this.total = data.timesheets.total;
          this.currentPage = page;
          this.gridLoading = false;
          this.isLoading = false;
        },
        error: error => {
          console.error('Error fetching timesheets', error);
        }
    });
  }

  onPageChange(event: any) {
    this.perPage=event.take;
    this.currentPage = (event.skip / event.take) + 1;
    // this.loadTimesheets(this.currentPage, this.perPage);
    if (this.isSearchActive) {
      this.searchTimesheetFormSubmit();
    } else {
      this.loadTimesheets(this.currentPage, this.perPage);
    }
  }

  navigateToTimesheetDetails(dataItem: any): void {
    this.timesheet_id = dataItem.timesheet_id;
    if (dataItem.upload_type == 'manual') {
      this.router.navigate([`/company/company-details/timesheets/${dataItem.timesheet_id}/timesheet-details`]);
    } else {
      this.router.navigate([`/company/company-details/${dataItem.timesheet_id}/uploaded-timesheet`]);
      console.log("csv timesheet");
    }
  }

  initializeFormGroup() {
    this.addTimesheetForm = new FormGroup({
      timesheet_name: new FormControl('', [Validators.required]),
      period_end_date: new FormControl('', [Validators.required, this.dateValidator.bind(this)]),
      invoice_date: new FormControl('', [Validators.required, this.dateValidator.bind(this)])
    })

    this.searchTimesheetForm = new FormGroup({
      timesheet_num: new FormControl(null),
      timesheet_name: new FormControl(null),
      period_end_date: new FormControl(null),
      upload_type: new FormControl(null),
      invoice_status: new FormControl(null)
    })

    //Upload Timesheet Form Group
    this.uploadTimesheetForm = this.fb.group({
      timesheet_file: [null],
      timesheet_name: ['', Validators.required],
      period_end_date: ['', [Validators.required, this.dateValidator.bind(this)]],
      invoice_date: ['', [Validators.required, this.dateValidator.bind(this)]]
    })

  }

  get timesheetName() {
    return this.addTimesheetForm.get('timesheet_name')
  }
  get periodEndDate() {
    return this.addTimesheetForm.get('period_end_date')
  }
  get invoiceDate() {
    return this.addTimesheetForm.get('invoice_date')
  }

  addTimesheetFormSubmit() {
    console.log(this.addTimesheetForm.value);
    this.isLoading = true;
    this.timesheetService.createTimesheet(this.addTimesheetForm.value, this.company_id).subscribe({
        next: response => {
          this.timesheet_id = response.timesheet['timesheet_id'];
          this.toastr.success('Timesheet created successfully');
          this.loadTimesheets(this.currentPage, this.perPage);
          this.closeModal('create');
          this.isLoading = false;
        },
        error: error => {
          this.toastr.error('Error creating timesheet');
        }
    });
  }

  resetForm(form: any) {
    form.reset();
    console.log(form);
  }

  setDataItem(value: any) {
    this.deleteTimesheetId = value;
  }

  // Delete a timesheet completely
  deleteTimesheetData(timesheet_id: Number)
  {
      this.isLoading = true;
      this.timesheetService.deleteTimesheet(timesheet_id).subscribe({
        next:(result:any)=>{
          this.loadTimesheets(this.currentPage, this.perPage);
          this.isLoading = false;
          if(result.message.includes('already')){
            this.toastr.warning(result['message']);
          }else{
            this.toastr.success(result["message"]);
          }
        },
        error:(result:any)=>{
          this.toastr.error(result["message"]);
        }
    });
  }

  // On submitting the Search form
  searchTimesheetFormSubmit() {
    this.isSearchActive = true;
    this.gridLoading = true;
    console.log(this.searchTimesheetForm.value);
    this.timesheetService.searchTimesheet(this.currentPage, this.perPage, this.company_id, this.searchTimesheetForm.value).subscribe({
        next: (result: any) => {
          this.timesheets = result.result;
          this.total = result.total;
          this.gridLoading = false;
        },
        error: error => {
          console.error('Error searching timesheets', error);
          this.gridLoading = false;
        }
    });
  }

  //Resetting the search form
  searchTimesheetResetForm() {
    this.searchTimesheetForm.reset();
    this.isSearchActive = false;
    this.loadTimesheets(this.currentPage, this.perPage);
  }

  // Modal Visibility Variables
  createModalVisible = false;
  uploadModalVisible = false;

  // To make the modal visible for create or Upload based on parameters
  openModal(modal: string) {
    if (modal == "upload") {
      this.uploadModalVisible = true;
    } else {
      this.createModalVisible = true;
    }
  }

  // Closing the modal
  closeModal(modal: string) {
    if (modal == 'upload') {
      this.uploadModalVisible = false;
      this.resetForm(this.uploadTimesheetForm);
    } else {
      this.resetForm(this.addTimesheetForm);
      this.createModalVisible = false;
    }
  }

  onFileChange(event: any) {
    if (event.target.files.length > 0) {
        const file = event.target.files[0];

        const fileName = file.name;
        const dotIndex = fileName.lastIndexOf('.');
        const extension = fileName.slice(dotIndex + 1).toLowerCase();

        if (['csv', 'jpg', 'jpeg', 'png', 'pdf'].includes(extension)) {
            this.uploadTimesheetForm.patchValue({
                timesheet_file: file
            })
        } else {
            this.toastr.error("File format is invalid");
            this.clearFile(event);
        }
    }
}
  private clearFile(event: any) {
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  }

  refreshData(){
    this.loadTimesheets(this.currentPage, this.perPage);
    this.resetForm(this.searchTimesheetForm);
  }

  // Date picker for upload Timesheet Form
  handleDatePickerOpen(dateField: any): void {
    const payrollBatchDateControl = dateField;
    if (payrollBatchDateControl?.errors) {
      const currentDate = this.formatDate(new Date());
      payrollBatchDateControl.setValue(currentDate);
    }
  }

  // Format the date for input field
  formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);
    return `${year}-${month}-${day}`;
  }

  // Validate dates allow only dates from a year in the past to a year in the future
  dateValidator(control: AbstractControl): ValidationErrors | null {
    const inputDate = new Date(control.value);
    const currentDate = new Date();
    const maxDate = new Date();

    maxDate.setFullYear(maxDate.getFullYear() + 1);
    const minDate = new Date();
    minDate.setFullYear(minDate.getFullYear() - 1);

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

  //On submitting the uploadTimesheetForm, append into formData and send it in request
  onSubmit() {
    if (this.uploadTimesheetForm.get('timesheet_file')!.value == null) {
        this.toastr.warning("select a file to upload")
    } else {
        const formData = new FormData();

        formData.append('file', this.uploadTimesheetForm.get('timesheet_file')!.value);
        formData.append('timesheet_name', this.uploadTimesheetForm.get('timesheet_name')!.value);
        formData.append('invoice_date', this.uploadTimesheetForm.get('invoice_date')!.value);
        formData.append('period_end_date', this.uploadTimesheetForm.get('period_end_date')!.value);
        formData.append('company_id', this.company_id);

        console.log(formData);

        this.isLoading = true;
        this.timesheetService.uploadTimesheet(formData).subscribe(
            (result) => {
                console.log("response", result);
                if (result.message.includes("Uploaded")) {
                    this.toastr.success(result.message);
                    this.isLoading = false;
                    this.closeModal('upload');
                    this.resetForm(this.uploadTimesheetForm);

                    // If there are rows with missing fields, they are dropped
                    // This toastr tells Rows have been dropped not what rows !
                    if (result.missing_fields.length !== 0) {
                        this.toastr.info("Few Rows Were dropped because of incomplete Information");
                    }
                    this.router.navigate([`/company/company-details/${result.timesheet_id}/uploaded-timesheet`]);
                } else {
                    this.toastr.warning(result.message);
                    this.isLoading = false;
                }
            }
        )
    }
}
}
