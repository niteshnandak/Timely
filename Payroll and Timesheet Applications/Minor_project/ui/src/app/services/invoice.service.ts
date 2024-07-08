import { HttpClient, HttpHeaders, HttpResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class InvoiceService {

  constructor(
    private httpClient : HttpClient
  ) {}

  apiUrl = "http://127.0.0.1:8000/api";

  getAllInvoices(skip:number, take:number){
    const allInvoices = `${this.apiUrl}/invoice?skip=${skip}&take=${take}`;

    return this.httpClient.get<any>(allInvoices);
  }

  editInvoice(id:number, invoiceData:any[]){

    const editInvoice = `${this.apiUrl}/invoice/edit?id=${id}&data=${invoiceData}`

    return this.httpClient.get<any>(editInvoice);
  }


  deleteInvoice(id:any){
    const removeInvoice = `${this.apiUrl}/invoice/delete?invoiceId=${id}`

    return this.httpClient.get<any>(removeInvoice);
  }


  generatePdf(id: any){
    const generatePdf = `${this.apiUrl}/invoice/generate-pdf?id=${id}`;

    return this.httpClient.get<any>(generatePdf);

  }

  viewPdf(id: any) {
    const viewPdf = `${this.apiUrl}/invoice/view-pdf?id=${id}`;

    const headers = new HttpHeaders();

    return this.httpClient.get<Blob>(viewPdf, {headers: headers, responseType: 'blob' as 'json'});

  }

  downloadPdf(id: any) {
    const downloadPdf = `${this.apiUrl}/invoice/download-pdf?id=${id}`;

    return this.httpClient.get<any>(downloadPdf, {responseType: 'blob' as 'json'});
  }

  deletePdf(id: any) {
    const deletePdf = `${this.apiUrl}/invoice/delete-pdf?id=${id}`;

    return this.httpClient.get<any>(deletePdf);
  }

  sendMail(id: any) {
    const sendMail = `${this.apiUrl}/email-invoice?id=${id}`;

    return this.httpClient.get<any>(sendMail);
  }


  createInvoice(invoiceData:any){

    // const createInvoice = this.apiUrl+`/invoice/create?id=${id}&data=${invoiceData}`

    return this.httpClient.post<any>(`${this.apiUrl}/invoices/create`, { data:invoiceData});
  }

  showReports(reportsData: any) {
    const showReports = `${this.apiUrl}/show-reports`;

    return this.httpClient.post<any>(showReports, reportsData);
  }

  generateReports(reportsData: any) {
    const generateReports = `${this.apiUrl}/generate-reports`;

    return this.httpClient.post<any>(generateReports, reportsData, {responseType: 'blob' as 'json'});
  }

}
