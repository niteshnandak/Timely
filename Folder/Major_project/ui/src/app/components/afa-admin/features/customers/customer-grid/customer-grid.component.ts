import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { CommonModule } from '@angular/common';
import {
  trigger,
  state,
  style,
  animate,
  transition
 } from '@angular/animations';
import { transform } from '@progress/kendo-drawing/dist/npm/geometry';
import { CustomerKendoGridComponent } from './customer-kendo-grid/customer-kendo-grid.component';
import { CustomersService } from '../../../../../services/customers.service';
import { Router ,RouterOutlet} from '@angular/router';
import { CompanyService } from '../../../../../services/company.service';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '../../../../../auth/auth-services/auth.service';
import { LoaderComponent } from '../../../../loader/loader.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-customer-grid',
  standalone: true,
  imports: [
    GridModule,
    CommonModule,
    CustomerKendoGridComponent,
    RouterOutlet,
    LoaderComponent
  ],
  templateUrl: './customer-grid.component.html',
  styleUrl: './customer-grid.component.css',

  animations:[
    // This is for the search box to occupy the whole page
    trigger('popOverState',[
      state('open', style({
        height: '90vh',
      })),
      transition('* => *',animate('500ms ease')),
    ]),

    //This is for the change of height of the upper row
    trigger('heightControl',[
      state('open',style({
        height: 0,
        opacity: 0,
        display: "none",
      })),
      transition("* => *", animate('500ms ease'))
    ]),

    // font change
    trigger('fontControl',[
      state('open',style({
        height: 20,
        fontSize: 25
      })),
      transition("* => *", animate('500ms ease'))
    ]),

    //this is to change the opacity of a component and to remove from the display
    trigger('opacityControl',[
      state('open',style({
        opacity: 0,
        display: 'none',
      })),
      transition("* => *", animate('500ms ease'))
    ]),

    //This for the display level change in the upper row
    trigger('displayControl', [
      state('open',style({
        display: "none"
      })),
      transition("* => *", animate('100ms ease'))
    ])


  ]
})
export class CustomerGridComponent {

  //all the datas required for the animations
  state: 'none' | 'search' | 'add' | 'edit' | 'expand' = 'none';
  stateOpen = 'null';

  //all the datas required for the kendo grid
  public customerGridData: any = {
    data : [],
    total: 0
  }
  skip = 0;
  take = 10;
  pageable_status:boolean = false;
  customers_active_count:any;
  customers_inactive_count:any;
  customer_total_count:any;
  customer_week_count:any;
  customer_month_count:any;
  company_id:any;
  company_name: any;
  deleteCustomerId:any;
  isLoading !: boolean;
  gridloading: any;
  top_customers: any;


  constructor(
    private customers:CustomersService,
    private router: Router,
    private companyService: CompanyService,
    private toastr: ToastrService,
    private titleService: Title
  ){
    this.titleService.setTitle('Customer');
   }

  ngOnInit(){

    this.isLoading = true;
    this.state = 'none';

    // getting the company id to get the customers based on the company id
    this.company_id = this.companyService.getStoredCompanyId();

    // get company name using the company id
    this.companyService.getCompanyId(this.company_id).subscribe((result:any) => {
      this.company_name = result['company_name'];
    })

    // making the state go back to the native if not when coming to the page
    if(this.state === 'none'){
      this.router.navigateByUrl('company/company-details/customers')
    }

    // making the state back to native when the event is triggered
    this.customers.searchEvent.subscribe(()=>{
      this.state = 'none';
      this.loadItem();
    });

    // getting the datas to show from the search component
    this.customers.searchData.subscribe((result:any)=>{
      this.pageable_status = false;
      this.customerGridData = {
        data: result['result'],
        total: result['count']
      }
    })

    this.loadItem();

  }

  toggleState(value: 'none' | 'search' | 'add' | 'edit' | 'expand', customer_id: any) {
    this.state = this.state === value && value != 'edit' ? 'none' : value;

    if(this.state === 'search'){
      setTimeout(() => {
        this.router.navigateByUrl('company/company-details/customers/search-customer')
      }, 500);

    }else if(this.state === 'add'){
      setTimeout(() => {
        this.router.navigateByUrl('company/company-details/customers/add-customer')
      }, 500);

    }else if(this.state === 'none'){
      this.router.navigateByUrl('company/company-details/customers')
      this.pageable_status = true;
      this.loadItem();
    }else if (this.state === 'edit') {
      this.router.navigateByUrl('company/company-details/customers/edit-customer/' + customer_id);
    }
  }

  editCustomerData(customer_id:Number){

    this.router.navigateByUrl('company/company-details/customers/edit-customer/'+customer_id)  ;
    // this.toggleState('edit');

  }
  deleteCustomerData(customer_id:Number){
    this.customers.deleteCustomer(customer_id)
    .subscribe({
      next:(result:any)=>{
        this.toastr.success(result["message"], 'Deleted');
      },
      error:(result:any)=>{
        this.toastr.error(result['error']["message"]);
      }
    })
    this.loadItem();
  }


  // to get all the data from the back end
  loadItem(){
    this.isLoading = true;


    // to get the customer datas to show in the kendo grid
    this.customers.fetchCustomerData(this.skip, this.take, this.company_id).subscribe((result:any)=>{

      console.log(result)

      this.customerGridData = {
        data: result.customerActive,
        total: result.total
      }

      this.pageable_status = result.total >= 10 ? true : false;


    })

    // to get the customer stats to show in the kendo grid
    this.customers.fetchCustomerStats(this.company_id).subscribe((result:any)=>{

      console.log(result);


      this.customers_active_count = result.customer_active;
      this.customers_inactive_count = result.customer_inactive;
      this.customer_total_count = result.customer_total;
      this.customer_week_count = result.customer_last_week;
      this.customer_month_count = result.customer_last_month;
      this.top_customers = result.top_customers;

      this.isLoading = false;
    });
  }

  // to get the customer to be deleted
  setDataItem(value:any){
    this.deleteCustomerId = value;
  }

  // function for pagination
  customerPageChange(result:any){
    this.skip = result.skip;
    this.take = result.take;
    this.loadItem();
  }



}
