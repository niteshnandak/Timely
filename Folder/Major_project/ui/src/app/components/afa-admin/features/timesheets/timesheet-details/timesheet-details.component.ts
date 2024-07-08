import { CommonModule, Location } from '@angular/common';
import { Component, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { GridModule, PagerPosition, PagerType } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../../loader/loader.component';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ModalComponent } from '../modal/modal.component';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TimesheetService } from '../../../../../services/timesheet.service';
import { CompanyService } from '../../../../../services/company.service';
import { ToastrService } from 'ngx-toastr';
import { DropDownsModule } from '@progress/kendo-angular-dropdowns';
import { DecimalNumberOnlyDirective } from '../../../../../directive/decimal-only/decimal-only.directive';
import { NumberOnlyDirective } from '../../../../../directive/number-only/number-only.directive';

@Component({
  selector: 'app-timesheet-details',
  standalone: true,
  imports: [GridModule,
    CommonModule,
    LoaderComponent,
    RouterLink,
    ModalComponent,
    ReactiveFormsModule,
    DropDownsModule,
    NumberOnlyDirective,
    DecimalNumberOnlyDirective,
  ],
  templateUrl: './timesheet-details.component.html',
  styleUrl: './timesheet-details.component.css',
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class TimesheetDetailsComponent {

  public isLoading!: boolean;
  addTimesheetDetailForm!: FormGroup;
  timesheet_details: any;
  total: number = 0;
  currentPage: number = 1;
  perPage: number = 10;
  company_id: any;
  timesheet_id: any;
  assignments: any;
  assignment_num: any;
  gridLoading !: boolean;
  deleteTimesheetDetailId !: any;
  timesheet_name: any;
  createModalVisible = false;
  isEditMode = false;
  editTimesheetDetailId: any;
  invoiceStatus !: any;
  num_of_rows !: any;
  comboData !: any;
  originalComboData !: any;
  customer_name !: any;

  public type: PagerType = "numeric";
  public buttonCount = 5;
  public info = true;
  public pageSizes = [1,5, 10, 20,50];
  public previousNext = true;
  public position: PagerPosition = "bottom";

  constructor(
    private timesheetService: TimesheetService,
    private toastr: ToastrService,
    private activatedRoute: ActivatedRoute,
    private router: Router,
    private location: Location,
    private companyService: CompanyService,
  ) { }

  ngOnInit() {
    this.isLoading = true;
    this.initializeFormGroup();
    this.loadTimesheetDetails(this.currentPage, this.perPage);
    this.company_id = sessionStorage.getItem('companyId');

    this.getAssignments(this.company_id);
    this.addTimesheetDetailForm.get('assignment_num')?.valueChanges.subscribe(
      assignment_num => {
        if (assignment_num) {
          this.getPeopleName(assignment_num);
        }
      }
    );
  }

  loadTimesheetDetails(page: number, perPage: number) {
    this.gridLoading = true;
    this.timesheet_id = this.activatedRoute.snapshot.params['id']
    this.company_id = this.companyService.getStoredCompanyId();
    this.timesheetService.getAllTimesheetDetails(page, perPage, this.timesheet_id, this.company_id).subscribe({
      next: data => {
        this.timesheet_name = data.timesheet_name[0].timesheet_name;
        this.timesheet_details = data.timesheet_details;
        this.total = data.timesheet_details.total;
        this.currentPage = page;
        this.gridLoading = false;
        this.isLoading = false;
        this.customer_name = this.timesheet_details.data[0].customer_name;
      },
      error: error => {
        console.error('Error fetching timesheet details', error);
      }
    });

    this.timesheetService.getTimeSheetInfo(this.timesheet_id).subscribe(
      (result) => {
        console.log(result.num_of_rows);
        this.invoiceStatus = result.invoice_status;
        this.num_of_rows = result.num_of_rows;
      }
    )
  }

  onPageChange(event: any) {
    this.perPage=event.take;
    this.currentPage = (event.skip / event.take) + 1;
    this.loadTimesheetDetails(this.currentPage, this.perPage);
  }

  initializeFormGroup(){
    this.addTimesheetDetailForm = new FormGroup({
      assignment_num: new FormControl('', [Validators.required]),
      people_name: new FormControl('', [Validators.required]),
      quantity: new FormControl('', [Validators.required, Validators.maxLength(5)]),
      unit_price: new FormControl('', [Validators.required, Validators.maxLength(6)]),
      description: new FormControl('', [Validators.required, Validators.maxLength(2000), Validators.minLength(3)])
    })
  }

  get assignmentNumber() {
    return this.addTimesheetDetailForm.get('assignment_num')
  }
  get peopleName() {
    return this.addTimesheetDetailForm.get('people_name')
  }
  get quantity() {
    return this.addTimesheetDetailForm.get('quantity')
  }
  get unitPrice() {
    return this.addTimesheetDetailForm.get('unit_price')
  }
  get description() {
    return this.addTimesheetDetailForm.get('description')
  }

  addTimesheetDetailFormSubmit() {
    if (this.isEditMode) {
          this.isLoading = true;
          this.timesheetService.updateTimesheetDetail(this.editTimesheetDetailId, this.addTimesheetDetailForm.value).subscribe({
            next: response => {
              console.log(this.addTimesheetDetailForm.value);
              console.log('Timesheet detail updated successfully');
              this.toastr.success('Timesheet detail updated successfully!');
              this.loadTimesheetDetails(this.currentPage, this.perPage);
              this.closeModal('create');
              this.isLoading = false;
            },
            error: error => {
              console.error('Error updating timesheet detail', error);
              this.toastr.error('Failed to update timesheet detail.');
            }
        });
    } else {
          console.log(this.addTimesheetDetailForm.value);
          this.isLoading = true;
          this.timesheetService.createTimesheetDetail(this.addTimesheetDetailForm.value, this.timesheet_id).subscribe({
            next: response => {
              const timesheet_detail = response.timesheet_detail;
              console.log('timesheet detail added successfully');
              this.toastr.success('Timesheet detail added successfully!');
              this.loadTimesheetDetails(this.currentPage, this.perPage);
              this.closeModal('create');
              this.isLoading = false;
            },
            error: error => {
              console.error('Error creating timesheet detail', error);
              this.toastr.error('Failed to add timesheet detail.');
            }
        });
    }
  }


  addTimesheetDetailResetForm() {
    this.addTimesheetDetailForm.reset();
  }

  getAssignments(company_id: any) {
    this.timesheetService.fetchAssignmentsByCompanyId(company_id).subscribe({
        next: (response) => {
            this.assignments = response.assignment;
            const transformedAssignments = this.assignments.map((assignment: any) => ({
            text: `${assignment.assignment_num} - ${assignment.people_name}`,
            value: assignment.assignment_num
          }));

          this.comboData = transformedAssignments.slice();
          this.originalComboData = transformedAssignments.slice();
          console.log(this.comboData);

        },
        error: (error) => {
          console.error('Error fetching assignments', error);
        }
    });
  }

  // handleFilter(value: any) {
  //   if (value) {
  //     this.comboData = this.originalComboData.filter(
  //       (s: any) => s.text.toLowerCase().indexOf(value.toLowerCase()) !== -1
  //     );
  //   } else {
  //     this.comboData = this.originalComboData.slice();
  //   }
  // }

  onAssignmentChange(event: any): void {
    console.log(event.value);
    this.addTimesheetDetailForm.patchValue({ assignment_num: event.value });
    this.getPeopleName(event.value);
  }

  getPeopleName(assignment_num: any) {
    this.timesheetService.fetchPeopleNameByAssignmentNum(assignment_num).subscribe({
      next: (response) => {
        console.log(response.customer_name.customer_name);
        const people_name = response.people_name.people_name;
        const customer_name = response.customer_name.customer_name;
        this.addTimesheetDetailForm.patchValue({ people_name: people_name });
      },
      error: (error) => {
        console.error('Error fetching people name', error);
      }
    });
  }

  setDataItem(value: any) {
    this.deleteTimesheetDetailId = value;
  }

  deleteTimesheetDetailData(timesheet_detail_id: Number) {
    this.isLoading = true;
    this.timesheetService.deleteTimesheetDetail(timesheet_detail_id).subscribe({
      next: (result: any) => {
        this.loadTimesheetDetails(this.currentPage, this.perPage);
        this.isLoading = false;
        this.toastr.success(result["message"]);
      },
      error: (result: any) => {
        this.toastr.error(result["message"]);
      }
    });
  }

  openModal(modal: string, dataItem?: any) {
    if (modal == "create") {
      this.isEditMode = false;
      this.addTimesheetDetailResetForm();
      this.createModalVisible = true;
    } else if (modal === 'edit' && dataItem) {
      this.isEditMode = true;
      this.editTimesheetDetailId = dataItem.timesheet_detail_id;
      this.patchFormValues(dataItem);
      this.createModalVisible = true;
    }
  }

  closeModal(modal: string) {
    if (modal == 'create') {
      this.addTimesheetDetailResetForm();
      this.createModalVisible = false;
    }
  }

  patchFormValues(dataItem: any): void {
    // const transformedAssignments = {
    //   text: `${dataItem.assignment_num} - ${dataItem.people_name}`,
    //   value: dataItem.assignment_num
    // };
    this.addTimesheetDetailForm.patchValue({
      assignment_num: dataItem.assignment_num,
      quantity: dataItem.quantity,
      unit_price: dataItem.unit_price,
      description: dataItem.description,
    });
  }

  proceedToInvoice(details: any) {
    console.log(details);
    if (this.num_of_rows == 0) {
      this.toastr.warning("Add a timesheet detail to proceed to invoice");
    } else {
      this.timesheetService.proceedToInvoice(details).subscribe(
        (result) => {
          console.log(result);
          this.toastr.success(result["message"]);
          this.location.back();
        }
      )
    }
  }
}
