import { Component } from '@angular/core';
import { ActivatedRoute, Route, Router, RouterLink } from '@angular/router';
import { GridModule, PagerPosition, PagerType } from '@progress/kendo-angular-grid';
import { PayrollSelectionComponent } from '../payroll-selection/payroll-selection.component';
import { PayrollService } from '../../../../../services/payroll.service';
import { CompanyService } from '../../../../../services/company.service';
import { Title } from '@angular/platform-browser';
import { NavigationStart } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { NumberPipe } from '@progress/kendo-angular-intl';

@Component({
  selector: 'app-payroll-verify-invoices',
  standalone: true,
  imports: [RouterLink, 
            GridModule,
            DatePipe,
            CurrencyPipe,
          ],
  templateUrl: './payroll-verify-invoices.component.html',
})
export class PayrollVerifyInvoicesComponent {

  public selectedInvoices: any = {
    data: [],
    total: 0
  };
  public unselectedInvoices: any = {
    data: [],
    total: 0
  };
  public payroll_batch_id: any;

  public type: PagerType = "numeric";
  public buttonCount = 5;
  public info = true;
  public pageSizes = [ 5, 10, 20];
  public previousNext = true;
  public position: PagerPosition = "bottom";

  gridloading = false;

  selectedSkip = 0
  selectedPagesize = 10
  selectedTotal = 0
  companyName:any ='';
  unselectedSkip = 0
  unselectedPagesize = 10
  unselectedTotal = 0

  selectedLength=0;
  unselectedLength=0;


  selectedCurrentPage=1;
  SelectedPerPage=10;

  unselectedCurrentPage=1;
  unselecetedPerPage=10;


  constructor(private payrollService: PayrollService,
    private companyService:CompanyService,
    private route: ActivatedRoute,
    private router:Router,
    private titleService: Title
  ) {
    titleService.setTitle("Payroll");
    route.params.subscribe(val => {
      this.payroll_batch_id = this.route.snapshot.params['id'];
      // console.log(this.payroll_batch_id);
    });
  }

  ngOnInit() {
    this.loadCompanyName();
    this.getSelectedInvoices(this.selectedCurrentPage,this.SelectedPerPage);
    this.getUnselectedInvoices(this.unselectedCurrentPage,this.unselecetedPerPage);


    // checking the route before leaving the page
    this.router.events.subscribe(event => {
      // if (event instanceof NavigationStart) {
      //   console.log(event.url);
      //   if(event.url == "/company/company-details/payroll-batch/"+this.payroll_batch_id+"/select-invoices"){
      //     this.router.navigateByUrl("/company/company-details/payroll-batch");
      //   }
      // }
    })

  }

  selectedPageChange(event: any) {
    this.SelectedPerPage=event.take;
    this.selectedCurrentPage = (event.skip/event.take)+1;
    this.getSelectedInvoices( this.selectedCurrentPage,this.SelectedPerPage);
  }

  unselectedPageChange(event: any) {

    this.unselecetedPerPage=event.take;
    this.unselectedCurrentPage = (event.skip/event.take)+1;
    this.getUnselectedInvoices(this.unselectedCurrentPage,this.unselecetedPerPage);
  }

  loadCompanyName(): void {
    const companyId = this.companyService.getStoredCompanyId();
    if (companyId) {
      this.companyService.getCompanyId(companyId).subscribe({
        next: response => {
          this.companyName = response.company_name;
        }
      });
    }
  }

getSelectedInvoices(selectedCurrentPage:any,SelectedPerPage:any) {
    this.gridloading = true
    this.payrollService.getSelectedPayrollInvoices(selectedCurrentPage, SelectedPerPage, this.payroll_batch_id).subscribe({
      next: (response: any) => {
        console.log(response);
        this.selectedInvoices = {
          data: response.selected.data,
          total: response.selected.total,
        }
        this.selectedLength= this.selectedInvoices.data.length;
        console.log(this.selectedLength);
        this.gridloading = false
      }
    })

  }

getUnselectedInvoices(unselectedCurrentPage:any,unselecetedPerPage:any) {
    this.gridloading = true
    this.payrollService.getUnselectedPayrollInvoices(unselectedCurrentPage, unselecetedPerPage, this.payroll_batch_id).subscribe({
      next: (response: any) => {
        this.unselectedInvoices = {
          data: response.unselected.data,
          total: response.unselected.total
        }
        this.unselectedLength= this.unselectedInvoices.data.length;
        this.gridloading = false
      }
    })

  }

  verifiedSubmit(){
    this.payrollService.verifyPayrollBatch(this.payroll_batch_id).subscribe((result:any) => {
      console.log(result);
    })
    this.router.navigateByUrl("/company/company-details/payroll-batch");
  }
}
