import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class InvoiceService {

  //  api prefix url
  apiUrl: string = 'http://127.0.0.1:8000/api';

  //constructor
  constructor(private http: HttpClient) {}

  // REQUEST TO GET ALL INVOICES
  getAllInvoices(
    companyId: any,
    skip: number = 0,
    take: number = 10,
    searchFormData: any
  ) {
    //return
    return this.http.post<any>(
      `${this.apiUrl}/${companyId}/invoices/grid/?skip=` +
        skip +
        '&take=' +
        take,
      { searchFormData }
    );
  }

  // REQUEST TO SEND MAIL
  sendMail(id: any) {
    const mailPdf = `${this.apiUrl}/invoice/mail-pdf?id=${id}`;

    // return
    return this.http.get<any>(mailPdf);
  }

  // REQUEST TO DOWNLOAD MAIL
  downloadMail(invId: number): Observable<Blob> {
    const downloadPdfUrl = `${this.apiUrl}/invoice/${invId}/download-pdf`;

    // return
    return this.http.get(downloadPdfUrl, { responseType: 'blob' });
  }

  // REQUEST TO GET THE COMPANY ID FROM SESSION STORAGE
  getStoredCompanyId(): string | null {
    // return
    return sessionStorage.getItem('companyId');
  }

  // REQUEST TO DELETE INVOICE
  deleteInvoice(invId: any) {
    const url = `${this.apiUrl}/invoice/delete/${invId}`;

    // return
    return this.http.delete(url);
  }

  // REQUEST TO GET INVOICE ASSIGNMENTS
  getInvAssignments(companyId: any) {
    const url = `${this.apiUrl}/invoice/get-select-assignmnets/${companyId}`;

    // return
    return this.http.get(url);
  }

  // REQUEST TO GET INVOICES ACCORDING TO THE PEOPLE
  loadInvoices(peopleId: any, skip: any, pageSize: any, searchFormData: any) {
    //return
    return this.http.post<any>(
      `${this.apiUrl}/peoples/${peopleId}/invoices/grid/?skip=` +
        skip +
        '&take=' +
        pageSize,
      { searchFormData }
    );
  }

  // REQUEST TO CREATE INVOICE
  createInvoice(companyId: any, data: any) {
    const url = `${this.apiUrl}/${companyId}/invoices/create-invoice`;

    //return
    return this.http.post(url, data);
  }

  // REQUEST TO UPDATE THE INVOICE
  updateInvoice(companyId: any,invId:any, data: any) {
    const url = `${this.apiUrl}/${companyId}/invoices/update-invoice/${invId}`;

    //return
    return this.http.post(url, data);
  }

  // REQUEST TO GET THE EDIT INVOICE DETAILS TO PATCH
  getEditDetails(invId:any){
    const url = `${this.apiUrl}/invoices/get-edit-details/${invId}`;

    //return
    return this.http.get(url);
  }
}
