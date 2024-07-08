import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PayslipService {

  apiUrl: string = "http://127.0.0.1:8000/api";

  constructor(private http: HttpClient) { }

  getPayrollHistory(skip: number, take: number, organisation_id: any, company_id: any): Observable<any> {
    let params = new HttpParams()
      .set('skip', skip.toString())
      .set('take', take.toString())
      .set('organisation_id', organisation_id)
      .set('company_id', company_id);
    return this.http.get(`${this.apiUrl}/payroll-history`, { params });
  }

  viewPdf(id: number): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/payroll-history/view-pdf/${id}`, {
      responseType: 'blob',
    });
  }

  sendMail(id: any) {
    const sendMailUrl = `${this.apiUrl}/payroll-history/email-payslip/${id}`;
    return this.http.post<any>(sendMailUrl, {});  
  }
  searchPayslip(page: number, perPage: number, search_data: any, companyId: any) {
    return this.http.post<any>(`${this.apiUrl}/payroll-history/${companyId}/search?page=${page}&perPage=${perPage}`, search_data);
  }

}
