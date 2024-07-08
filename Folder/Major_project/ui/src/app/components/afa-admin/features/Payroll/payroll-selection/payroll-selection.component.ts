import { Component } from '@angular/core';
import { GridModule, RowArgs } from '@progress/kendo-angular-grid';
import { LoaderComponent } from '../../../../loader/loader.component';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { CustomersService } from '../../../../../services/customers.service';
import { CompanyService } from '../../../../../services/company.service';
import { PayrollService } from '../../../../../services/payroll.service';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { NavigationStart } from '@angular/router';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-payroll-selection',
  standalone: true,
  imports: [
    GridModule,
    LoaderComponent,
    CommonModule,
    RouterLink,
    DatePipe,
    CurrencyPipe
  ],
  templateUrl: './payroll-selection.component.html',
  styleUrl: './payroll-selection.component.css'
})
export class PayrollSelectionComponent {
  customerGridData:any = {
    data:[],
    total:0,
  };

  invoiceGridData:any = {
    data:[],
    total:0,
  };

  pageable_status:boolean = true;
  skip:number = 0;
  take:number = 10;
  state:"customer"|"invoices" = "customer";
  isLoading:boolean = false;
  gridLoading:boolean = true;
  company_id:any;

  selectedCustomer:any[] = [];
  selectedInvoice:any[] = [];

  customerSelection:number[] = [];
  invoiceSelection:number[] = [];

  customerNames:any[] = [];
  invoiceNumbers:any[] = [];

  payroll_batch_id:any;
  verifiedInvoices:any = [];
  public companyName: string = '';
  selectAll:boolean = false;

  //Constructor for intiating
  public constructor(
    private customerService : CustomersService,
    private companyService : CompanyService,
    private payrollService : PayrollService,
    private route : ActivatedRoute,
    private router:Router,
    private toastr:ToastrService,
    private title: Title
  ){
    this.title.setTitle('Payroll');

    route.params.subscribe(val => {
      this.payroll_batch_id = this.route.snapshot.params['id'];
    });

  }

  ngOnInit(){
    this.company_id = this.companyService.getStoredCompanyId();

    // loading the company name and all the customers while intitation the server
    this.loadCompanyName();
    this.loadCustomer();
  }

  // for loading the company name with the company_id
  loadCompanyName(): void {

    // checking the company name and consoling if there is any error
    this.companyService.getCompanyId(this.company_id).subscribe({
      next: response => {
        this.companyName = response.company_name;
      },
      error: error => {
        console.error('Error loading company details:', error);
      }
    });
  }

  // for pagination while selecting the customers
  customerPageChange(event:any){
    this.skip = event.skip;
    this.take = event.take;
    this.loadCustomer();
  }

  // for pagination while selecting the customers
  invoicePageChange(event:any){
    this.skip = event.skip;
    this.take = event.take;
    this.loadInvoices();
  }

  loadCustomer(){
    this.gridLoading = true;
    // fetching customer datas from the backend for the selection process
    this.payrollService.fetchCustomerDataForPayroll(this.skip, this.take, this.company_id).subscribe((result:any) => {

      // select customer names
      console.log(this.customerNames);

      // updating the grid datas using the backend datas
      this.customerGridData = {
        data: result.customerActive,
        total: result.total
      }

      this.gridLoading = false;
    })
  }



  // function is used for fetching the invoices of the selected customers
  fetchInvoice(){
    console.log(this.customerSelection);
    this.gridLoading = true;
    this.state = this.state === "customer" ? "invoices" : "customer";
    this.skip = 0;
    this.take = 10;


    // getting the invoices for the customers and if there is any error or the invoices for the slected customer is empty
    this.loadInvoices();

  }

  //load Invoices for the customer
  loadInvoices(){
    console.log(this.customerSelection);
    // a toaster message will be intitated
    this.payrollService.fetchInvoiceData(this.skip, this.take, this.customerSelection).subscribe({

      next: (result:any)=>{

        this.invoiceGridData = {
          data: result.data,
          total: result.total
        };
        this.gridLoading = false;
      },

      error: (result:any)=>{
        this.toastr.error(result["error"]["message"]);

        //back button if wanna select the datas again
        this.state = this.state === "customer" ? "invoices" : "customer";

        this.loadCustomer();
      }

    })
  }


  // fucntion to go back to the customer slection part
  backButton(){
    //back button if wanna select the datas again
    this.state = this.state === "customer" ? "invoices" : "customer";

    //getting the selection back to null before going back
    this.invoiceSelection = []
  }

  // select all is toggled
  selectAllButton(){
    this.selectAll = true;
  }

  // getting the custmer id and the customer name clubbing to find the names and ids
  public getCompositeKeyCustomer(item: any): string {
    return item.dataItem.customer_id+"-"+item.dataItem.customer_name;
  }

  // getting the invoice id and the invoice number clubbing to find the numbers and ids
  public getCompositeKeyInvoice(item: any): string {
    return item.dataItem.invoice_id+"-"+item.dataItem.invoice_number;
  }

  // when there is a in the selected of the invoices grid the names of the invoices are noted
  onSelectedKeysChangeInvoice(){
    let invoiceNumber:any[] = [];
    let invoiceId:any[] = [];
    this.selectedInvoice.map(key => {
      const [invoice_id, invoice_number] = key.split('-');
      invoiceNumber.unshift(invoice_number);
      invoiceId.push(invoice_id);
    })
    this.invoiceSelection = invoiceId;
    this.invoiceNumbers = invoiceNumber;
    console.log(this.invoiceNumbers);
  }

  // when there is changes in the selected of the customers grid the names of the customers are noted
  onSelectedKeysChangeCustomer(){
    let customerName:any[] = [];
    let customerId:any[] = [];
    this.selectedCustomer.map(key => {
      const [customer_id, customer_name] = key.split('-');
      customerName.unshift(customer_name);
      customerId.push(customer_id);
    })
    this.customerNames = customerName;
    this.customerSelection = customerId;
  }

  // function is used to submit all the selected invoices to the next phase
  saveInvoices(){

    console.log(this.invoiceSelection);

    // getting the invoice ids from the selected invoices to send the data to the back end to store the invoices
    this.selectedInvoice.map(key => {
      const [invoice_id, invoice_number] = key.split('-');
      this.invoiceSelection.unshift(invoice_id);
    })
    this.payrollService.verifyPayrollInvoice(this.invoiceSelection,this.payroll_batch_id).subscribe({
      next : (response:any)=>{

        if(response['toaster_success']){
          this.toastr.success(response['toaster_success'],'Success');
        }

        if(response['toaster_unselected_error']){
          this.toastr.warning(response['toaster_unselected_error'],'Select Invoices');
        }


       this.router.navigateByUrl('company/company-details/payroll-batch/'+ this.payroll_batch_id+ '/verify-invoices');
      },
      error: (msg)=>{

        this.toastr.error(msg["toaster_error"], 'Error');
      }
    })
  }
}
