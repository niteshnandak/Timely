import { Component, inject } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DataToCsvService } from '../../services/data-to-csv.service';
import { GridModule } from '@progress/kendo-angular-grid';
import { DataServiceRouteService } from '../../services/data-service-route.service';
import { Toast, ToastrService } from 'ngx-toastr';
import { HeaderComponent } from '../../header/header.component';

@Component({
  selector: 'app-check-csv',
  standalone: true,
  imports: [
    GridModule,
    HeaderComponent
  ],
  templateUrl: './check-csv.component.html',
  styleUrl: './check-csv.component.css'
})
export class CheckCsvComponent {
  activatedRoute = inject(ActivatedRoute);
  dataService = inject(DataServiceRouteService);
  router = inject(Router);
  timesheetDatas:any;
  timesheetId:any;
  fileId:any;
  noOfRows:any;
  toasterService=inject(ToastrService)

  ngOnInit(): void {
    this.getRequired();
    this.loadItem();
  }

  getRequired(){
    this.timesheetId = this.activatedRoute.snapshot.params['timesheetId'];
    this.fileId = this.activatedRoute.snapshot.params['fileId'];
    this.noOfRows = this.activatedRoute.snapshot.params['noOfRows'];

    console.log(this.fileId, this.timesheetId, this.noOfRows);
  }

  loadItem(){
    this.dataService.fetchCsvDatas(this.fileId, this.timesheetId, this.noOfRows).subscribe({
      next: (result)=>{
        this.timesheetDatas = result.csv_data;
        console.log(this.timesheetDatas);
      },
      error: (error)=>{
        console.log(error)
        this.toasterService.error("Datas are not in the correct format");
        this.router.navigateByUrl("timesheet")
      }
    });
  }

  navigateToDetail(){
    console.log(this.fileId);
      this.dataService.uploadCsvDatas(this.timesheetId,this.fileId).subscribe((result) => {
        this.toasterService.success("csv file added successfully")
      this.router.navigateByUrl("/timesheet/"+this.timesheetId)
    })
  }

  discardUpload(){
    this.toasterService.error("Canceled the csv upload")
    this.router.navigateByUrl("/timesheet");
  }
}
