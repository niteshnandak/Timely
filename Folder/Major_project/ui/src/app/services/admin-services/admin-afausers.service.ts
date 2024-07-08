import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AdminAfausersService {

  // addMinimize: any = new BehaviorSubject<any>(null);
  // addEvent: any = this.addMinimize.asObservable();

  apiUrl = 'http://127.0.0.1:8000/api';
  orgId!: number;

  constructor(private http: HttpClient) {}

  // to set current orgId
  setOrgId(orgId: number) {
    this.orgId = orgId;
  }

  // fetch current orgId
  getOrgId() {
    return this.orgId;
  }

  // FUNCTION TO GET AFA DETAILS ACCORDING TO THE FILTERS OR ALL
  getAfaUsers(orgId: number, skip: number = 0, take: number = 10, searchFormData: any) {
    // const params = new HttpParams()
    // .set('skip',skip)
    // .set('take',take);

    return this.http.post<any>(`${this.apiUrl}/admin-organisation/${orgId}/afa-users?skip=`+skip+'&take='+take, { searchFormData });
  }

  // Fetch Card Details
  fetchUserStats(orgId: number) {
    return this.http.get<any>(`${this.apiUrl}/admin-organisation/${orgId}/afa-users/afauser-stats`);
  }

  // Toggle Status of User
  toggleActiveStatus(orgId: number, userId: number) {
    return this.http.get<any>(
      `${this.apiUrl}/admin-organisation/${orgId}/afa-users/${userId}/toggleActiveStatus`
    );
  }

  // Soft Delete the user from the grid
  deleteAfaUser(orgId: number, userId: number, admin_id: number) {
    return this.http.delete<any>(
      `${this.apiUrl}/admin-organisation/${orgId}/afa-users/${userId}/deleteAfaUser`,
    );
  }

  // Create Afa User
  createAfaUser(data: any, orgId: number, admin_id: number) {
    return this.http.post<any>(
      `${this.apiUrl}/admin-organisation/${orgId}/afa-users/admin-create-afauser`,
      {data, admin_id}
    );
  }

  // Fetch AFA User Details
  getAfaUserDetail(orgId: number, userId: number) {
    return this.http.get<any>(`${this.apiUrl}/admin-organisation/${orgId}/afa-users/${userId}`);
  }

  // Update Afa User Details
  updateAfaUserDetails(orgId: number, userId: number, data: any, admin_id: number) {
    return this.http.put<any>(`${this.apiUrl}/admin-organisation/${orgId}/afa-users/update/${userId}`, {data, admin_id});
  }



  addMinimize: any = new BehaviorSubject<any>(null);
  addEvent: any = this.addMinimize.asObservable();
  // FUNCTION TO CHANGE THE SIZE OF THE TEMPLETE ACCORDING TOT THE ACTIONS
  addBoxSizeChange(){
    this.addMinimize.next();
  }

  searchDataEvent: any = new BehaviorSubject<any>(null);
  searchedData: any = this.searchDataEvent.asObservable();

  searchDataChange(newData: any) {
    // console.log(data);
    this.searchDataEvent.next(newData);
  }


  // to send the search form data from search component to dashboard componenet
  searchFormDataEvent: any = new BehaviorSubject<any>(null);
  searchFormData: any = this.searchFormDataEvent.asObservable();
   // FUNCTION TO STORE THE SEARCHED DATA AS AN EVENT
  searchFormDataSend(formData: any, searchFlag: boolean) {
    const data = { formData, searchFlag };
    this.searchFormDataEvent.next(data);
  }

}
