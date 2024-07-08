import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { lineChartData } from '../models/line-chart-data';

@Injectable({
  providedIn: 'root'
})
export class OrganisationService {

  constructor(
    private http: HttpClient
  ){}

  public apiUrl: string = 'http://127.0.0.1:8000/api';

  dashboardData(id: string){
    const params = new HttpParams().set('id', id.toString());
    return this.http.get<any>(`${this.apiUrl}/dashboard`, { params });

  }

  orgInitialInfo(id: string){
    const params = new HttpParams().set('org_id', id);
    return this.http.get<any>(`${this.apiUrl}/org/settings`, { params });
  }

  uploadLogo(formData: any){
    return this.http.post(`${this.apiUrl}/org-logo/upload`, formData);
  }

  editOrgDetails(formData: any, id:any){

    return this.http.put(`${this.apiUrl}/org-details/edit/${id}`, { data: formData });
  }

}
