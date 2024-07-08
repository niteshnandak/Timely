import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { BehaviorSubject } from 'rxjs';
@Injectable({
  providedIn: 'root',
})

// CLASS :AdminOrganisationsService
export class AdminOrganisationsService {
  addMinimize: any = new BehaviorSubject<any>(null);
  addEvent: any = this.addMinimize.asObservable();
  constructor(private http: HttpClient) {}
  //api prefix route
  apiUrl = 'http://127.0.0.1:8000/api';

  // FUNCTION TO GET ORGANISATION DETAILS ACCORDING TO THE FILTERS OR ALL
  getAllOrganisations(
    skip: number = 0,
    take: number = 10,
    searchFormData: any
  ) {
    //return
    return this.http.post<any>(
      `${this.apiUrl}/admin-organisations/?skip=` + skip + '&take=' + take,
      { searchFormData }
    );
  }

  // FUNCTION TO CREATE AN ORGANISATION
  createOrg(data: any) {
    const organisation = `${this.apiUrl}/admin-create-organisation`;

    //return
    return this.http.post(organisation, data);
  }

  // FUNCTION TO GET ORG DETAILS TO PATCH THE VALUES IN THE EDIT PAGE
  getOrgDetails(orgId: number) {
    const url = `${this.apiUrl}/admin-organisation/${orgId}`;

    //return
    return this.http.get<any>(url);
  }

  // FUNCTION TO UPDATE THE DETAILS
  updateOrgDetails(orgId: number | string, data: any) {
    //return
    return this.http.post<any>(
      'http://127.0.0.1:8000/api/admin-organisation/update/' + orgId,
      data
    );
  }

  // FUNCTION TO DELETE THE ORGANISATION USING ITS ID
  deleteOrgDetails(orgId: number) {
    //return
    return this.http.delete<any>(
      'http://127.0.0.1:8000/api/admin-organisation/delete/' + orgId
    );
  }

  // FUNCTION TO SET THE TOGGLED ACTIVE STATUS OF THE ORGANISATION
  toggleActiveStatus(orgId: number) {
    //return
    return this.http.get<any>(
      `${this.apiUrl}/admin-organisation/${orgId}/toggle-active-status`
    );
  }

  // FUNCTION TO CHANGE THE SIZE OF THE TEMPLETE ACCORDING TOT THE ACTIONS
  addBoxSizeChange() {
    this.addMinimize.next();
  }

  // // FUNCTION TO FETCH THE ORG STATS DETAILS
  fetchOrgStats() {
    //return
    return this.http.get(
      `${this.apiUrl}/admin-organisation/statistics/org-stats`
    );
  }

  fetchOrgDetails(orgId: any) {
    
    //return
    return this.http.get(
      `${this.apiUrl}/admin-organisation/organisation/${orgId}/details`
    );
  }

  //NOT USED ANYMORE
  // searchOrg(data: any) {
  //   //return
  //   return this.http.post(
  //     `${this.apiUrl}/admin-organisation/search/searchOrg`,
  //     data
  //   );
  // }

  //searched data behaviour subject variables
  searchFormDataEvent: any = new BehaviorSubject<any>(null);
  //we get or retriev data from the variable after converting as plain observables using asObservables
  searchFormData: any = this.searchFormDataEvent.asObservable();

  // FUNCTION TO STORE THE SEARCHED DATA AS AN EVENT
  searchFormDataSend(formData: any, searchFlag: boolean) {
    const data = { formData, searchFlag };
    this.searchFormDataEvent.next(data);
  }

  // orgDetailsEvent: any = new BehaviorSubject<any>({});
  // orgDetails: any = this.orgDetailsEvent.asObservable();

  // storeOrgDetailInfo(orgDetails: any) {
  //   console.log(orgDetails);
  //   //above getting.
  //   this.orgDetailsEvent.next(orgDetails);
  // }
}
