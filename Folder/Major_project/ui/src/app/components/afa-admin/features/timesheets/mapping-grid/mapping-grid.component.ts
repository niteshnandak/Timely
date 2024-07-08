import { TimesheetService } from './../../../../../services/timesheet.service';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { GridModule, PageChangeEvent, DataItem } from '@progress/kendo-angular-grid';
import { ModalComponent } from '../modal/modal.component';
import { ToastrService } from 'ngx-toastr';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';
import { DecimalNumberOnlyDirective } from '../../../../../directive/decimal-only/decimal-only.directive';

@Component({
  selector: 'app-mapping-grid',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    ModalComponent,
    ReactiveFormsModule,
    NumberOnlyDirective,
    DecimalNumberOnlyDirective,

  ],
  templateUrl: './mapping-grid.component.html',
  styleUrl: './mapping-grid.component.css'
})
export class MappingGridComponent {

  public gridData !: any;
  public gridView !: any;
  public pageSize = 10;
  public skip = 0;
  public gridLoading !: boolean;
  public mapModalVisible !: boolean;
  public unmapModalVisible !: boolean;
  public deleteModalVisible !: boolean;
  public workerName !: any;
  public assignmentName !: any;
  public timesheetDetailID !: any;
  public assignmentsForWorker : any = [];
  public count !: any;
  public timesheetStatus !: any;

  constructor(
    private timesheetService: TimesheetService,
    private toastr : ToastrService,
    private router : Router,
    private fb: FormBuilder
  ){}

  @Input() timesheetId : any;
  @Input() mapping : any;
  @Input() invoiceStatus : any;

  // Load the timesheet data on init
  ngOnInit(){
    console.log("inputs mapp", this.timesheetId, this.mapping);
    this.gridLoading = true;
    this.loadData();
  }

  ngOnChanges(){
    this.timesheetStatus = this.invoiceStatus;
  }

  // Page change handler function
  public pageChange(event: PageChangeEvent){
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadData();
  }

  // Load Mapped and unmapped Data
  private loadData(){
    this.gridLoading = true;
    this.timesheetService.getTimesheetbyMapping(this.timesheetId, this.mapping).subscribe(
      (result)=>{
        this.gridData = result.timesheetbyMapping;
        this.gridView = {
          data : this.gridData.slice(this.skip, this.skip + this.pageSize),
          total : this.gridData.length,
        }
        this.gridLoading = false;
      }
    )
  }

  // To unmap a timesheet detail, Set a detail's status to unmapped
  public unmapTimesheetDetail(){
    this.gridLoading = true;
    this.timesheetService.unmapTimesheetDetail(this.timesheetDetailID).subscribe(
      (result) => {
        console.log(result);
        if(result.message.includes('Unmapped')){
          this.toastr.success(result.message);
          this.closeModal('unmapping');
          this.loadData();
        }
      }
    )
  }

  // Map timesheet Details Form
  mapTimesheetDetailForm : FormGroup = this.fb.group({
    assignment_num : [''],
    people_name : [''],
    quantity: ['', Validators.maxLength(5)],
    unit_price : ['', Validators.maxLength(6)],
  });

  // To map a timesheet detail
  public mapFormSubmit(){
    console.log(this.mapTimesheetDetailForm.value);
    const formData = this.mapTimesheetDetailForm.value;
    if(this.mapTimesheetDetailForm.get('assignment_num')?.value === ''){
      this.toastr.error("Please select an assignment");
    }
    else {
      this.gridLoading = true;
      this.timesheetService.mapTimesheetDetail(formData, this.timesheetDetailID).subscribe(
        (result) => {
          this.toastr.success(result.message);
          this.closeModal('mapping');
          this.loadData();
        }
      )
    }
  }


  // Get Assignments for that Worker using worker Id
  private getAssignmentsforWorker(workerId: any, companyId: any){
    this.timesheetService.getAssignmentbyWorkerID(workerId, companyId).subscribe(
      (result) => {
        console.log(result);
        this.assignmentsForWorker = [];
        result.forEach((item : any )=> {
          let newItem = {
            assignment_num: item.assignment_num,
            combined: `${item.assignment_num} - ${item.people_name}`
          };
          this.assignmentsForWorker.push(newItem);
        });

        this.count = this.assignmentsForWorker.length
        if(this.count == 1){
          this.toastr.info("This Worker has only one Assignment", '', {timeOut: 1300});
        }
      }
    )
  }


  //Delete a timesheet Detail
  public deleteTimesheetDetail(){
    this.gridLoading = true;
    this.timesheetService.deleteTimesheetDetail(this.timesheetDetailID).subscribe(
      (result)=>{
        console.log(result);
        this.toastr.success("Timesheet Detail Deleted Succesfully");
        this.closeModal('delete');
        this.loadData();
      }
    )
  }


  // openModal - Open the modal based on the param
  // closeModal - Closes the modal based on the param
  public openModal(type: any, dataItem: any){
    if(type == 'mapping'){
      this.mapModalVisible = true;
      this.timesheetDetailID = dataItem.timesheet_detail_id;
      this.getAssignmentsforWorker(dataItem.people_id, dataItem.company_id);
      this.mapTimesheetDetailForm.patchValue(dataItem);
    }
    else if(type == 'delete'){
      this.deleteModalVisible = true;
      this.timesheetDetailID = dataItem.timesheet_detail_id;
    }
    else {
      this.unmapModalVisible = true;
      this.timesheetDetailID = dataItem.timesheet_detail_id;
      this.workerName = dataItem.people_name;
      this.assignmentName = dataItem.assignment_num;
    }
  }

  public closeModal(type: any){
    if(type == 'mapping'){
      this.mapModalVisible = false;
    } else if(type == 'delete'){
      this.deleteModalVisible = false;
    }
     else {
      this.unmapModalVisible = false;
    }
  }

}
