import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { company } from '../models/company';
import { Observable, shareReplay } from 'rxjs';
import { AuthService } from '../auth/auth-services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class CompanyService {

  private companyId: string | null = null;

  constructor(private httpClient: HttpClient,
    private authService: AuthService
  ) { }

  apiUrl:string = "http://127.0.0.1:8000/api";
  private companyCache = new Map<string, Observable<any>>();

  fetchCompanyData(skip: number = 0, take: number = 10, organisation_id:any): Observable<any> {
    return this.httpClient.get<any>(`${this.apiUrl}/companies`, {
      params: { skip: skip.toString(), take: take.toString() , organisation_id: organisation_id}
    });
  }

  // getCompanyId(id: string): Observable<any> {
  //   const userId = this.authService.getUser()['user_id'];
  //   return this.httpClient.get<company>(`${this.apiUrl}/company/${id}`);
  // }

  // getCompanyId(companyId: string): Observable<any> {
  //   return this.httpClient.post<any>(`${this.apiUrl}/company/details`, { company_id: companyId });
  // }

  getCompanyId(companyId: string): Observable<any> {
    if (!this.companyCache.has(companyId)) {
      const request = this.httpClient.post<any>(`${this.apiUrl}/company/details`, { company_id: companyId }).pipe(
        shareReplay(1)
      );
      this.companyCache.set(companyId, request);
    }
    return this.companyCache.get(companyId)!;
  }

  updateCompany(id: string, data: any): Observable<any> {
    return this.httpClient.post<any>(`${this.apiUrl}/company/${id}`, {data:data});
  }
  uploadCompanyImage(formData:any, id:any){
    return this.httpClient.post<any>(`${this.apiUrl}/company/image-upload/${id}`, formData);
  }



  addCompany(data: any): Observable<any> {
    return this.httpClient.post<any>(`${this.apiUrl}/companies`, data);
  }

  deleteCompany(id: string): Observable<any> {
    return this.httpClient.put<any>(`${this.apiUrl}/companies/${id}`,{});
  }

  searchCompany(company_data:any): Observable<any> {
    return this.httpClient.post<any>(`${this.apiUrl}/companies/searchCompany`, company_data);
  }
  updateCompanyStatus(id: string, newStatus: number): Observable<any> {
    return this.httpClient.put(`${this.apiUrl}/companies/${id}/status`, { status: newStatus });
  }

  getCompanyStats(): Observable<any> {
    return this.httpClient.get(`${this.apiUrl}/companies/stats`);
  }

  storeCompanyId(companyId: string) {
    console.log('Storing company ID in session storage:', companyId);
    sessionStorage.setItem('companyId', companyId);
  }

  getStoredCompanyId(): string | null {
    return sessionStorage.getItem('companyId');
  }

  // removeCompanyId() {
  //   console.log('Removing company ID from session storage');
  //   sessionStorage.removeItem('companyId');
  // }
}
