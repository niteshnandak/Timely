import { Component } from '@angular/core';
import { GridModule } from '@progress/kendo-angular-grid';
import { CommonModule } from '@angular/common';
import { CustomersService } from '../../../../../../services/customers.service';

@Component({
  selector: 'app-customer-kendo-grid',
  standalone: true,
  imports: [
    GridModule,
    CommonModule
  ],
  templateUrl: './customer-kendo-grid.component.html',
  styleUrl: './customer-kendo-grid.component.css'
})
export class CustomerKendoGridComponent {
  public customerGridData: any = {
    data : [],
    total: 0
  }
  skip = 0;
  take = 10;
  pageable_status:boolean = false;

  constructor(private customers:CustomersService){}

  ngOnInit(){
    this.loadItem();
  }

  loadItem(){
    // this.customers.fetchCustomerData(this.skip, this.take).subscribe((result:any)=>{

    //   console.log(result)

    //   this.customerGridData = {
    //     data: result.customer,
    //     total: result.total
    //   }

    //   this.pageable_status = result.total >= 10 ? true : false;


    //   //hello World
    // })
  }

  customerPageChange(result:any){
    this.skip = result.skip;
    this.take = result.take;
    this.loadItem();
  }
}
