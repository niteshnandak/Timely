import { Component, inject } from '@angular/core';
import { DataServiceRouteService } from '../../services/data-service-route.service';
import { timesheet } from '../../models/timesheet';
import { TimesheetGridComponent } from './timesheet-grid/timesheet-grid.component';
import { GridModule } from '@progress/kendo-angular-grid';
import { ReactiveFormsModule, FormGroup, FormBuilder, Validators, AbstractControl } from '@angular/forms';
import { CommonModule, NgClass } from '@angular/common';
import { Router } from '@angular/router';
import { HttpClientModule, HttpClientXsrfModule } from '@angular/common/http';
import { DataToCsvService } from '../../services/data-to-csv.service';
import { HeaderComponent } from '../../header/header.component';
import { ToastrService } from 'ngx-toastr';
import { maxYearValidator } from '../../validators/custom-validator';

@Component({
  selector: 'app-timesheet-dashboard',
  standalone: true,
  imports: [
    GridModule,
    TimesheetGridComponent,
    ReactiveFormsModule,
    NgClass,
    HeaderComponent,
    CommonModule
  ],
  templateUrl: './timesheet-dashboard.component.html',
  styleUrl: './timesheet-dashboard.component.css'
})
export class TimesheetDashboardComponent {
  manualTimesheetForm: FormGroup;
  uploadCsvForm: FormGroup;
  showManualCreation = false;
  showUploadCSV = false;
  router = inject(Router)
  dataToSend = inject(DataToCsvService)
  dataFromFile:any;
  timesheets:any[] = []
  user:string = ""
  dataService = inject(DataServiceRouteService)
  timesheetId:any;
  formData:FormData = new FormData();
  file:any;
  toasterService = inject(ToastrService);


  private fb = inject(FormBuilder);

  constructor() {
    this.manualTimesheetForm = this.fb.group({
      timesheet_name: ['', [Validators.required]],
      timesheet_date: ['', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/), maxYearValidator()]]
    });

    this.uploadCsvForm = this.fb.group({
      timesheet_name: ['', Validators.required],
      timesheet_date: ['', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/), maxYearValidator()]],
      file_upload: [null, Validators.required]
    });
  }


  clearInvalidDate(event: any) {
    const timesheetDateControl = this.manualTimesheetForm.get('timesheet_date');
    const csvTimesheetDateControl = this.uploadCsvForm.get('timesheet_date');
  
    const handleInvalidDate = (control: AbstractControl) => {
      if (control && this.isYearInvalid(control.value)) {
        const date = new Date(control.value);
        date.setFullYear(new Date().getFullYear());
        event.target.value = date.toISOString().slice(0, 10);
        control.setValue(event.target.value);
        control.markAsPristine();
      }
    };
  
    timesheetDateControl ? handleInvalidDate(timesheetDateControl) : null;
    csvTimesheetDateControl ? handleInvalidDate(csvTimesheetDateControl) : null;
  }

  isYearInvalid(dateValue: string): boolean {
    if (dateValue) {
      const selectedYear = new Date(dateValue).getFullYear();
      const currentYear = new Date().getFullYear();
      return selectedYear > currentYear;
    }
    return false;
  }

  toggleManualCreation() {
    this.showManualCreation = !this.showManualCreation;
    this.showUploadCSV = false;

    if(this.showManualCreation == false){
      this.manualTimesheetForm.reset()
    }
  }

  toggleUploadCSV() {
    this.showUploadCSV = !this.showUploadCSV;
    this.showManualCreation = false;

    if(this.showUploadCSV == false){
      this.uploadCsvForm.reset()
    }
  }

  onManualTimesheetSubmit() {
    if (this.manualTimesheetForm.valid) {
      this.manualTimesheetForm.value['created_by'] = "0";
      this.manualTimesheetForm.value['upload_type_csv'] = "0";
      this.dataService.storeManualTimesheet(this.manualTimesheetForm.value).subscribe({
        next: (result) => {
          this.timesheetId = result.timesheet_id;
          console.log(result.timesheet_id)
          this.toasterService.success("Created manual timesheet")
          this.router.navigateByUrl("timesheet/"+this.timesheetId, {state: {flag : true}});
        },

        error: (error) =>{
          this.toasterService.error("Check the inputs again");
          console.log(error.message);
          this.manualTimesheetForm.reset();
        }

      })
    }
  }

  onUploadCSVSubmit() {
    if (this.uploadCsvForm.valid) {
      // this.formData.append('file_upload', file, file.name);
      // Handle CSV upload submission
      // this.uploadCsvForm.value['created_by'] = "1";
      // this.uploadCsvForm.value['upload_type_csv'] = "0";
      // this.uploadCsvForm.value['file_upload'] = this.file;

      // this.formData.append('created_by',"1");
      // this.formData.append('upload_type_csv',"1");
      // this.formData.append('timesheet_name',this.uploadCsvForm.value['timesheet_name']);
      // this.formData.append('timesheet_date',this.uploadCsvForm.value['timesheet_date']);

      console.log(this.uploadCsvForm.value);

      this.formData.append('file_upload',this.file, this.file.name)
      this.formData.append('timesheet_name',this.uploadCsvForm.value['timesheet_name'])
      this.formData.append('timesheet_date',this.uploadCsvForm.value['timesheet_date'])
      this.formData.append('created_by',"1")
      this.formData.append('upload_type_csv',"1")

      this.dataService.storeCsvTimesheet(this.formData).subscribe({
        next: (result:any) => {
          this.dataFromFile = result;
          this.router.navigateByUrl("timesheet/check-csv/"+result.file_id+"/"+result.timesheet_id+"/"+result.no_of_rows);
        },
        error: (result:any) => {
          console.log("Oh shit errors!!!")

            this.toasterService.error("Check the input file type and format");

          this.uploadCsvForm.reset();
        },
      })

    }
  }

  onFileDropped(event:any){
    this.file = event.target.files[0];
    console.log(this.file);
  }

}
