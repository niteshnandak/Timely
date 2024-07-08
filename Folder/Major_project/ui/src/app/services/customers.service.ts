import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { customer } from '../models/customer';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CustomersService {

  // to trigger a event to close the searchbox in the front end
  searchMinimize:any = new BehaviorSubject<any>(null);
  searchEvent:any = this.searchMinimize.asObservable();

  // search kendo data update event
  searchDataEvent:any = new BehaviorSubject<any>(null);
  searchData:any = this.searchDataEvent.asObservable();


  constructor(private httpClient:HttpClient) { }


  apiUrl:string = "http://127.0.0.1:8000/api";




  // for the connection between the back end

  // to fetch all the customer datas from the back end to he front end
  fetchCustomerData(skip:number, take:number, company_id:any){
    return this.httpClient.get<customer>(this.apiUrl+'/customers?skip='+skip+'&take='+take+"&company_id="+company_id);
  }

  // to fetch all the customer stats like active customer from the back end to he front end
  fetchCustomerStats(company_id:any){
    return this.httpClient.get<any>(this.apiUrl+"/customers/stats?company_id="+company_id);
  }

  // to add a new customer in the cutomer table
  addCustomerData(companyId:any, customer_data:any){
    return this.httpClient.post<any>(this.apiUrl+"/customers/add-customer?company_id="+companyId,{customer_data:customer_data});
  }

  // to edit the customer already available in the customer table
  editCustomerData(customer_id:any, customer_data:any){
    return this.httpClient.post<any>(this.apiUrl+"/customers/edit-customer?customer_id="+customer_id,{customer_data:customer_data});
  }

  // to get all the customer data based on the search filter from the front end
  searchCustomers(company_id:any, customer_data: any){
    return this.httpClient.post<any>(this.apiUrl+"/customers/searchCustomer?company_id="+company_id, {customer_data:customer_data});
  }

  // fetch the datas of the customer for whom the customer edit is checked
  fetchEditCustomer(customer_id:any){
    return this.httpClient.get<any>(this.apiUrl+"/customers/fetch-customer-data?customer_id="+customer_id);
  }

  // to delete the customer from the table
  deleteCustomer(customer_id:any){
    return this.httpClient.get<any>(this.apiUrl+"/customers/delete-customer?customer_id="+customer_id);
  }




  // functionalities to trigger a event in another component
  searchBoxSizeChange(){
    this.searchMinimize.next();
  }

  // functionalities to send data from one component to another
  searchDataChange(newData:any){
    this.searchDataEvent.next(newData);
  }


}
