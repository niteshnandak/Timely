import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { timesheet } from '../models/timesheet';
import { timesheetDetail } from '../models/timesheet-detail';

@Injectable({
  providedIn: 'root'
})
export class DataServiceRouteService {

  constructor(private httpClient: HttpClient) { }

  apiUrl:string ="http://127.0.0.1:8000/api";

  fetchDataTimesheet(skip: number, take: number){
    return this.httpClient.get<timesheet>(this.apiUrl+"/dashboard?skip="+skip+"&take="+take);
  }

  storeManualTimesheet(timesheet: timesheet){
    return this.httpClient.post<{timesheet_id:any}>(this.apiUrl+"/timesheets", timesheet);
  }

  getTimesheetById(skip:any, take:any, id: any) {
    return this.httpClient.get<timesheetDetail>(this.apiUrl+"/dashboard/"+id+"?skip="+skip+"&take="+take);
  }

  addManualRow(id:number ,timesheetDetail: timesheetDetail){
    return this.httpClient.post<timesheetDetail>(this.apiUrl+"/dashboard/"+id, timesheetDetail)
  }

  editTimesheet(timesheet_id:any, timesheet_detail_id:any, timesheet_detail: any){
    console.log(timesheet_id,timesheet_detail_id,timesheet_detail)

    const baseUrl = this.apiUrl+"/dashboard/"+timesheet_id+"/edittimesheet/"+timesheet_detail_id+"/update";
    console.log(baseUrl);
    return this.httpClient.put<timesheetDetail>(baseUrl,timesheet_detail)
  }

  deleteTimesheet(timesheet_id: any, timesheet_detail_id: any) {

   const  baseUrl = this.apiUrl+"/dashboard/"+timesheet_id+"/"+timesheet_detail_id+"/destroy";
    console.log("Delete:",baseUrl);

    return this.httpClient.post<any>(baseUrl,{timesheet_detail_id:timesheet_detail_id});
  }

  fetchCsvDatas(fileId:any, timesheetId:any, no_of_rows:any){
    return this.httpClient.get<{fileId:number, csv_data:{data:any[]}, timesheetId:number, }>(this.apiUrl+"/dashboard/upload-csv/"+fileId+"/"+timesheetId+"/"+no_of_rows);
  }

  storeCsvTimesheet(data: any){
    const params = new HttpParams();
    return this.httpClient.post<any>(this.apiUrl+"/dashboard/upload-csv", data);
  }

  uploadCsvDatas(timesheetId:any, fileId:any){
    console.log("fileId:",fileId);
    return this.httpClient.post<any>(this.apiUrl+"/dashboard/"+timesheetId+"/store",{fileId:fileId})
  }

   updateTimesheetDraftStatus(timesheetId:any){
    return this.httpClient.get<any>(this.apiUrl+"/dashboard/"+timesheetId+"/draftstatus")
   }


}
