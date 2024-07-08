import { Component } from '@angular/core';
import { HeaderComponent } from '../../header/header.component';
import { GridModule } from '@progress/kendo-angular-grid';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { InvoiceService } from '../../services/invoice.service';
import { ReportsDataService } from '../../services/reports-data.service';

@Component({
  selector: 'app-check-reports',
  standalone: true,
  imports: [HeaderComponent, GridModule],
  templateUrl: './check-reports.component.html',
  styleUrl: './check-reports.component.css'
})
export class CheckReportsComponent {
  reportsData : any;

  constructor(
    private router : Router,
    private invoiceService : InvoiceService,
    private reportsDataService : ReportsDataService,
    private toastService : ToastrService
  ) {}

  ngOnInit() {
    this.reportsData = sessionStorage.getItem('reportsData');
    this.reportsData = JSON.parse(this.reportsData); // get reports Data from session storage
    // this.reportsData = this.reportsDataService.getReportsData(); // get reports Data from service
  }


  downloadReports() {
    console.log(this.reportsData);

    this.invoiceService.generateReports(this.reportsData).subscribe(
      (response) => {
        console.log(response);
        // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'report_data.xlsx';
        link.click();
        this.toastService.success('Report Downloaded Succesfully');
        window.URL.revokeObjectURL(url); // Clean up

        sessionStorage.removeItem('reportsData');
        this.router.navigateByUrl('/invoices');
      },
      (error) => {
        this.toastService.error('Error Downloading Report');
        console.error('Error downloading report:', error);
      }
    );
  }

  discardReports() {
    // this.reportsData = null;
    // this.reportsDataService.setReportsData(this.reportsData);

    sessionStorage.removeItem('reportsData'); // removing session storage of reports Data
    this.toastService.error('Report has been Discarded');
    this.router.navigateByUrl('/invoices');
  }

}
