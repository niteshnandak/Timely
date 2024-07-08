import { TimesheetService } from './../../../../../services/timesheet.service';
import { Component } from '@angular/core';
import { LoaderComponent } from '../../../../loader/loader.component';
import { ActivatedRoute, Router, RouterLink, RouterOutlet } from '@angular/router';
import { MappingGridComponent } from '../mapping-grid/mapping-grid.component';
import { CommonModule } from '@angular/common';
import { ModalComponent } from '../modal/modal.component';
import { ToastrService } from 'ngx-toastr';
import { DecimalNumberOnlyDirective } from '../../../../../directive/decimal-only/decimal-only.directive';

@Component({
  selector: 'app-upload-timesheet',
  standalone: true,
  imports: [
    LoaderComponent,
    RouterOutlet,
    MappingGridComponent,
    RouterOutlet,
    RouterLink,
    CommonModule,
    ModalComponent
  ],
  templateUrl: './upload-timesheet.component.html',
  styleUrl: './upload-timesheet.component.css'
})
export class UploadTimesheetComponent {

  public isLoading !: boolean;
  public timesheetId : any;
  public timesheetName !: string;
  public invoiceStatus !: string;
  public confirmModal !: boolean;

  // To make tabs for Mapped and Unmapped
  selectedTab: string = 'mapped';

  // Select tabs based on selected buttons
  selectTab(tab: string) {
    this.selectedTab = tab;
  }

  constructor(
    private route : ActivatedRoute,
    private router : Router,
    private toastr : ToastrService,
    private timesheetService : TimesheetService
  ){}

  // Fetch timesheet details here
  ngOnInit(){
    // this.isLoading = true;
    const timesheetId = this.route.snapshot.params['id'];
    this.timesheetId = timesheetId;
    this.timesheetService.getTimeSheetInfo(timesheetId).subscribe(
      (result) => {
        this.timesheetName = result.timesheet_name;
        this.invoiceStatus = result.invoice_status;
        this.isLoading = false;
      }
    )
    console.log(this.invoiceStatus);
  }

  /**
   * Function to send the timesheet to invoicing
   */

  public proceedToInvoice(details: any){
    console.log(details);
    this.timesheetService.proceedToInvoice(details).subscribe(
      (result) => {
        console.log(result);
        this.toastr.success(result.message);
        this.router.navigateByUrl('/company/company-details/timesheets');
      }
    )
  }

  /**
   * open and close modals based on type
   */
  public openModal(type : any){
    if(type == 'confirm'){
      this.confirmModal = true;
    }
  }

  public closeModal(type : any){
    if(type == 'confirm'){
      this.confirmModal = false;
    }
  }
}
