import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { GridComponent, GridModule, PageChangeEvent, GridDataResult, CancelEvent, EditEvent,RemoveEvent, SaveEvent, AddEvent, } from '@progress/kendo-angular-grid';
import { CompositeFilterDescriptor, GroupDescriptor, GroupResult, SortDescriptor, filterBy, groupBy, orderBy, process, State } from '@progress/kendo-data-query';
import { DataServiceRouteService } from '../../services/data-service-route.service';
import { Subscription, filter, take } from 'rxjs';
import { FormBuilder, FormControl, FormGroup, FormsModule, NgModel, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { timesheetDetail } from '../../models/timesheet-detail';
import { timesheet } from '../../models/timesheet';
import { ActivatedRoute, ActivatedRouteSnapshot, NavigationEnd, Router, RouterLink, RouterOutlet } from '@angular/router';
import { InputsModule } from '@progress/kendo-angular-inputs';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { BrowserModule } from '@angular/platform-browser';
import { HeaderComponent } from '../../header/header.component';
import { ToastrService } from 'ngx-toastr';
import { NumberOnlyDirective } from '../../directive/number-only/number-only.directive';
import { CharOnlyDirective } from '../../directive/char-only/char-only.directive';
import { DecimalOnlyDirective } from '../../directive/decimal-only/decimal-only.directive';
import { maxYearValidator } from '../../validators/custom-validator';

@Component({
  selector: 'app-timesheet-detail',
  standalone: true,
  imports: [GridModule,
    FormsModule,
    CommonModule,
    ReactiveFormsModule,
    InputsModule,
    HeaderComponent,
    RouterOutlet,
    RouterLink,
    NumberOnlyDirective,
    CharOnlyDirective,
    DecimalOnlyDirective
  ],
  templateUrl: './timesheet-detail.component.html',
  styleUrl: './timesheet-detail.component.css',
})
export class TimesheetDetailComponent{
  isEditing: { [id: number]: boolean } = {};
  offSave !: boolean;
  groups: GroupDescriptor[] = [];
  timesheetDetail: any[] = [];
  timesheet: any[]=[];
  gridData: any = { data: [], total: 0 };
  user: string = '';
  gridloading = false;
  timesheetId: number = 0;
  router = inject(Router);
  public formGroup : any;
  private editedRowIndex ?: number;
  toastService = inject(ToastrService);
  formData:FormData = new FormData();
  timesheets_name: string = '';
  timesheet_skip:any;
  showAddRowForm: boolean = false;
  addRowForm: FormGroup;

  private fb = inject(FormBuilder);

  constructor() {
    this.addRowForm = this.fb.group({
      worker_name: ['', [Validators.required, Validators.pattern(/^[a-zA-Z][a-zA-Z ]*$/)]],
      worker_id: ['', [Validators.required, Validators.pattern(/^[0-9]*$/)]],
      timesheet_detail_date: ['', [Validators.required, Validators.pattern(/^\d{4}-\d{2}-\d{2}$/), maxYearValidator()]],
      organisation: ['', Validators.required],
      hourly_pay: ['', [Validators.required, Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/)]],
      hours_worked: ['', [Validators.required, Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/)]],
    });
  }

  ngOnInit() {
    this.timesheet_skip =  window.history.state;
    console.log("This is timesheet_skip",this.timesheet_skip);
    this.loadItem();
  }

  clearInvalidDate(event: any) {
    const timesheetDateControl = this.addRowForm.get('timesheet_date');
    if (timesheetDateControl && this.isYearInvalid(timesheetDateControl.value)) {
      const date = new Date(timesheetDateControl.value);
      date.setFullYear(new Date().getFullYear()); // Set the year to the current year
      event.target.value = date.toISOString().slice(0, 10);
      timesheetDateControl.setValue(event.target.value); // Update the form control value
      timesheetDateControl.markAsPristine(); // Update the input value with the modified date
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

  getTimesheetDetail : any;

  grid: any | GridDataResult = {
    data: [],
    total: 0,
  };

  filter: CompositeFilterDescriptor = {
    logic: 'and',
    filters: [],
  };

  dataService = inject(DataServiceRouteService);
  pageSize = 10;
  skip = 0;

  total = 0;

  pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadItem();
  }

  groupChange(groups: any) {
    this.groups = groups;
    this.groupItem();
  }

  filterChange(filter: CompositeFilterDescriptor) {
    this.filter = {
      logic: filter.logic,
      filters: filter.filters,
    };
    this.filterItem();
  }

  filterItem() {
    console.log(filter);
    this.grid = {
      data: filterBy(this.timesheetDetail, this.filter),
      total: this.total,
    };
    console.log(this.grid);
  }

  groupItem() {
    if (this.groups.length == 0) {
      this.loadItem();
    }
    this.grid = groupBy(this.timesheetDetail, this.groups);
  }
  activatedRoute = inject(ActivatedRoute);

  loadItem() {
    this.timesheetId = this.activatedRoute.snapshot.params['id'];
    console.log(this.timesheetId);
    this.gridloading = true;
    this.dataService
      .getTimesheetById(this.skip, this.pageSize, this.timesheetId)
      .subscribe({
        next:(result)=>{
          this.getTimesheetDetail = result;
          console.log(result);
          console.log(this.getTimesheetDetail);
            this.timesheetDetail = result.timesheet_detail;
            this.user = result.user;
            this.total = result.total;
            console.log(this.timesheetDetail);

            this.grid = {
              data: this.timesheetDetail,
              total: this.total,
            };
            console.log(this.grid);
            this.gridloading = false;
        },
        error:(msg)=>{
          console.log(msg);
          this.gridloading = false;
          this.router.navigate(['']);
        }
      }
    )
  }

  toggleAddRowForm() {
    this.showAddRowForm = !this.showAddRowForm;

    if(this.showAddRowForm == false){
      this.addRowForm.reset()
    }

  }

  onAddRow() {
    if (this.addRowForm.valid) {
      console.log('row added', this.addRowForm.value);
      console.log('timesheet', this.timesheetId);
      this.gridloading = true;
      this.skip = 0;
      this.dataService
        .addManualRow(this.timesheetId, this.addRowForm.value)
        .subscribe((result : any) => {
          // this.timesheetDetail = result?.details_data ? result.details_data : [];
          // this.gridloading = false;
          this.loadItem();
        });
        this.showAddRowForm = !this.showAddRowForm;
        this.addRowForm.reset();
    }
  }

  resetForm() {
    this.addRowForm.reset();
  }

  public editHandler(args: EditEvent){

    // Starts an Inline Edit Form
    const { dataItem } = args;
    this.closeEditor(args.sender);

    console.log(this.timesheetId  )

    this.formGroup = new FormGroup({
      id: new FormControl(dataItem.id, Validators.required),
      timesheet_id: new FormControl(dataItem.timesheet_id, Validators.required),
      worker_name: new FormControl(dataItem.worker_name, [
        Validators.required,
        Validators.pattern(/^[a-zA-Z ]*$/),
      ]),
      worker_id: new FormControl(dataItem.worker_id, [
        Validators.required,
        Validators.pattern(/^[0-9]*$/),
      ]),
      organisation: new FormControl(dataItem.organisation, Validators.required),
      hourly_pay: new FormControl(dataItem.hourly_pay, [
        Validators.required,
        Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/),
      ]),
      hours_worked: new FormControl(dataItem.hours_worked, [
        Validators.required,
        Validators.pattern(/^-?0*[0-9]+(\.[0-9]+)?$/),
      ]),
    });

    this.editedRowIndex = args.rowIndex;
    args.sender.editRow(args.rowIndex, this.formGroup);

  }

  public saveHandler(args: SaveEvent){
    if(args.isNew === false){
      const updatedData = {
        id: this.formGroup.value.id,
        timesheet_id: this.formGroup.value.timesheet_id,
        worker_name: this.formGroup.value.worker_name,
        worker_id: this.formGroup.value.worker_id,
        organisation: this.formGroup.value.organisation,
        hourly_pay: this.formGroup.value.hourly_pay,
        hours_worked: this.formGroup.value.hours_worked,
        timesheet_detail_date: this.formGroup.value.timesheet_detail_date
      };
      console.log("formData",updatedData);
      this.dataService
          .editTimesheet(args.dataItem.timesheet_id,args.dataItem.id,updatedData)
          .subscribe((result)=>{
            console.log(result);
            this.closeEditor(args.sender);
            this.toastService.success("Edited Successfully");
            this.loadItem();
            this.isEditing[args.dataItem.id] = false;
          })
    }
  }

  public removeHandler(timesheet_id:any, id:any) {

    this.dataService.deleteTimesheet(timesheet_id, id).subscribe((result) => {
      this.toastService.success("Deleted Successfully")
      this.loadItem();
    })
  }


  public cancelHandler(args: CancelEvent): void {
    this.closeEditor(args.sender, args.rowIndex);
    this.isEditing[args.dataItem.id] = false;
  }


  private closeEditor(grid: GridComponent, rowIndex = this.editedRowIndex) {
    // close the editor
    grid.closeRow(rowIndex);
    // reset the helpers
    this.editedRowIndex = undefined;
    this.formGroup = undefined;
  }


  updateDraftStatus(){
    this.gridloading = true
    this.dataService
      .updateTimesheetDraftStatus(this.timesheetId)
      .subscribe((result)=>{
        console.log(result);
        this.toastService.success("Sent for invoicing");
        this.loadItem();
      }
    )
  }

  clickHandler(id:any){
    this.isEditing = {[id]: true};
    // console.log("id",id);
  }

}
