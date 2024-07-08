import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { assignment } from '../models/assignment';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TimesheetService {

  constructor(
    private httpClient : HttpClient
  ) { }

  apiUrl = "http://127.0.0.1:8000/api";

  getAllTimesheets(page: number, perPage: number, company_id:any){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet?page=${page}&perPage=${perPage}&company_id=${company_id}`);
  }

  uploadTimesheet(formData: any){

    return this.httpClient.post<any>(`${this.apiUrl}/timesheet/upload`, formData);
  }

  getAllTimesheetDetails(page: number, perPage: number, timesheet_id:any, company_id:any){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet-details?page=${page}&perPage=${perPage}&timesheet_id=${timesheet_id}&company_id=${company_id}`);
  }

  fetchAssignmentsByCompanyId(company_id: any){
    return this.httpClient.get<any>(`${this.apiUrl}/assignments/company/${company_id}`);
  }

  fetchPeopleNameByAssignmentNum(assignment_num: any){
    return this.httpClient.get<any>(`${this.apiUrl}/assignments/people_name/${assignment_num}`);
  }

  createTimesheet(timesheet_data: any, company_id: any){
    return this.httpClient.post<any>(`${this.apiUrl}/timesheet/create`, {data: timesheet_data, company_id: company_id});
  }

  createTimesheetDetail(timesheet_detail_data: any, timesheet_id:any){
    return this.httpClient.post<any>(`${this.apiUrl}/timesheet-detail/create`, {data: timesheet_detail_data, timesheet_id: timesheet_id});
  }

  getTimeSheetInfo(timesheet_id: string){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet/info/${timesheet_id}`);
  }


  getTimesheetbyMapping(timesheet_id: any, mapping: any){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet/${timesheet_id}/${mapping}`);
  }

  deleteTimesheetDetail(timesheet_detail_id: any){
    return this.httpClient.get(`${this.apiUrl}/timesheet-detail/delete/${timesheet_detail_id}`);
  }

  deleteTimesheet(timesheet_id: any){
    return this.httpClient.get(`${this.apiUrl}/timesheet/delete/${timesheet_id}`);
  }

  searchTimesheet(page: number, perPage: number, company_id: any, search_data: any) {
    return this.httpClient.post<any>(`${this.apiUrl}/search?page=${page}&perPage=${perPage}&company_id=${company_id}`, search_data );
  }

  updateTimesheetDetail(timesheet_detail_id: any, timesheet_detail: any) {
    return this.httpClient.post<any>(`${this.apiUrl}/timesheet-detail/${timesheet_detail_id}`, timesheet_detail);
  }

  unmapTimesheetDetail(timesheet_detail_id: any){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet-detail/${timesheet_detail_id}/unmap`)
  }

  getAssignmentbyWorkerID(workerId: any, companyId: any){
    return this.httpClient.get<any>(`${this.apiUrl}/timesheet-detail/${workerId}/${companyId}/assignments`);
  }

  mapTimesheetDetail(formData: any, timesheetDetailId: any){
    return this.httpClient.post<any>(`${this.apiUrl}/timesheet-detail/${timesheetDetailId}/map`, formData );
  }

  proceedToInvoice(timesheetId : any){
    return this.httpClient.get<any>(`${this.apiUrl}/${timesheetId}/proceed-to-invoice`);
  }
}
