import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { PayrollService } from '../../../../../services/payroll.service';
import { CompanyService } from '../../../../../services/company.service';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { ActivatedRoute, Route, Router, RouterLink } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { TooltipModule } from '@progress/kendo-angular-tooltip';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-payroll-detail',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    RouterLink,
    TooltipModule,
    CurrencyPipe
  ],
  templateUrl: './payroll-detail.component.html'
})
export class PayrollDetailComponent {
  payrollDetailGridData:any = {
    data: [],
    total: 0
  }
  skip:number = 0;
  take:number = 10;
  company_id:any;
  company_name:string = '';
  payroll_batch_id:any;
  gridLoading:boolean = false; //TODO: to be changed to true
  flag:boolean = false;
  total_amount:number = 0

  public constructor(
    private payrollService: PayrollService,
    private companyService: CompanyService,
    private route: ActivatedRoute,
    private toastr:ToastrService,
    private router:Router,
    private title: Title
  ){
    this.title.setTitle('Payroll');
  }

  ngOnInit(){
    this.company_id = this.companyService.getStoredCompanyId();

    this.payroll_batch_id = this.route.snapshot.params['id'];

    this.loadCompanyName();

    this.loadItem();
  }

  loadItem(){
    this.gridLoading = true;

    this.payrollService.fetchPayrollDetailData(this.skip, this.take, this.payroll_batch_id).subscribe((result:any) => {
      console.log(result);
      this.payrollDetailGridData = {
        data: result.payrolls,
        total: result.total
      }
      this.total_amount = result.net_pay;
      if(result.status == "Verified"){
        this.flag = true;
      }
      this.gridLoading = false;
    })

  }

  payrollPageChange(event:any){
    this.skip = event.skip;
    this.take = event.take;
    this.loadItem();
  }

  loadCompanyName(): void {
    this.companyService.getCompanyId(this.company_id).subscribe({
      next: response => {
        this.company_name = response.company_name;
      },
      error: error => {
        console.error('Error loading company details:', error);
      }
    });
  }

  updatePayrollRun(payroll_batch_id:any){

    this.payrollService.updatePayrollProcess(payroll_batch_id).subscribe({
      next: (response:any)=>{
        this.router.navigateByUrl('/company/company-details/payroll-batch');
        this.toastr.success(response['toaster_success'],'Success');
      },
      error: (msg:any)=>{
        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })

  }
}
