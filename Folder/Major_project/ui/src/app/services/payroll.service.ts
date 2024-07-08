import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { customer } from '../models/customer';
// import { saveAs } from 'file-saver';


@Injectable({
  providedIn: 'root'
})
export class PayrollService {

  payrollVerifyInvoices = new BehaviorSubject<any>(null);
  verifiedInvoices = this.payrollVerifyInvoices.asObservable();

  apiUrl:string = "http://127.0.0.1:8000/api";

  constructor(private http: HttpClient) { }

  getPayrollBatches(skip: number, take: number, organisation_id: any, company_id: any,searchData:any): Observable<any> {
    let params = new HttpParams()
    .set('skip', skip.toString())
    .set('take', take.toString())
    .set('organisation_id', organisation_id)
    .set('company_id', company_id);;
    return this.http.post(this.apiUrl+'/payroll-batch?skip='+skip+'&take='+take, {params,searchData,company_id,organisation_id});
  }

  addPayrollBatch(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/payroll-batches`, data);
  }

  getPayrollBatchDetails(id: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/payroll-batches/${id}`);
  }

  generateReport(payrollBatchId: number) {
    return this.http.get(`${this.apiUrl}/payroll-batches/generate-report/${payrollBatchId}`, { responseType: 'blob' });
}

deletePayrollBatch(id: number): Observable<any> {
  return this.http.post(`${this.apiUrl}/payroll-batch/delete`,{id});
}

  // Bhaveshraj Sureshkumar code
  fetchCustomerDataForPayroll(skip:number, take:number, company_id:any){
    return this.http.get<customer>(this.apiUrl+'/payroll-selection/fetch-customers?skip='+skip+'&take='+take+"&company_id="+company_id);
  }

  fetchInvoiceData(skip:any =0, take:any =10,customers:any): Observable<any> {
    return this.http.post(this.apiUrl+'/payroll-selection/fetch-invoices?skip='+skip+'&take='+take,{customers:customers});
  }

  fetchPayrollDetailData(skip:any =0, take:any =10, payroll_batch_id:any): Observable<any> {
    return this.http.post(this.apiUrl+'/payroll-selection/payroll-batch-details?skip='+skip+'&take='+take, {payroll_batch_id: payroll_batch_id});
  }


  verifyPayrollInvoice(invoices:any,payroll_batch_id:any){
    return this.http.post(this.apiUrl+'/payroll-verify-invoices',{invoices,payroll_batch_id,status});
  }

  sendVerifyInvoices(data:any){
    this.payrollVerifyInvoices.next(data);
  }

  verifyPayrollBatch(payroll_batch_id:any){
    return this.http.post<any>(this.apiUrl+'/payroll-batches/verified-status-change', {payroll_batch_id: payroll_batch_id});
  }

  getSelectedPayrollInvoices(skip:any,take:any,payroll_batch_id:any){
    const params = new HttpParams()
              .set('skip',skip)
              .set('take',take);
    return this.http.post(this.apiUrl+'/payroll-selected-invoices?skip='+skip+"&take="+take,{params,payroll_batch_id});
  }
  getUnselectedPayrollInvoices(skip:any,take:any,payroll_batch_id:any){
    const params = new HttpParams()
              .set('skip',skip)
              .set('take',take);
    return this.http.post(this.apiUrl+'/payroll-unselected-invoices?skip='+skip+"&take="+take,{payroll_batch_id});
  }

  updatePayrollProcess(payroll_batch_id:any){
    return this.http.post(this.apiUrl+'/payroll-run',{payroll_batch_id});
  }

  rollBackPayrollBatch(payroll_batch_id:any,people_id:any){
    return this.http.post(this.apiUrl+'/payroll-rollback',{payroll_batch_id,people_id});
  }

}
