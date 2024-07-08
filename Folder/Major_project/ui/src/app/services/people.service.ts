import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PeopleService {

  peopleDataChange = new BehaviorSubject<any>(null);
  peopleData = this.peopleDataChange.asObservable();


  peopleChange = new BehaviorSubject<any>(null);
  peopleEvent = this.peopleChange.asObservable();


  peopleSearchClick = new BehaviorSubject<any>(false);
  peopleSearchClickEvent = this.peopleSearchClick.asObservable();


  PeopleEditCloseChange = new BehaviorSubject<any>(false);
  PeopleEditCloseChangeEvent = this.PeopleEditCloseChange.asObservable();


  constructor(
    private httpClient: HttpClient
  ) { }

  apiUrl = "http://127.0.0.1:8000/api";

  getAllPeople(skip: number = 0, take: number = 10,user:any, searchData : any): Observable<any> {
    const params = new HttpParams()
              .set('skip',skip)
              .set('take',take);
    return this.httpClient.post<any>(`${this.apiUrl}/people?skip=`+skip+"&take="+take, {params, user, searchData});
  }

  getEditPeopleData(people_id: any) {
    
    const url = `${this.apiUrl}/people/${people_id}`;
    return this.httpClient.get<any>(url);
  }

  saveEditPeopleData(people_id: any,people_data:any,user:any){
    const url = `${this.apiUrl}/people/save-edit/${people_id}`;
    return this.httpClient.post<any>("http://127.0.0.1:8000/api/people/save-edit/"+people_id,{people_data:people_data,user:user});
  }

  updatePeopleStatus(id: string, newStatus: number): Observable<any> {
    return this.httpClient.put(`${this.apiUrl}/people/${id}/status`, { status: newStatus });
  }

  addPeopleData(addPeopleData:any,user:any){
    const url = `${this.apiUrl}/people/add-people`;
    return this.httpClient.post<any>(url, {addPeopleData :addPeopleData,user:user});
  }
  getCompanies(){
    return this.httpClient.get<any>(`${this.apiUrl}/allcompanies`);
  }
  searchPeopleData(searchPeopleData:any,user:any){
    const url = `${this.apiUrl}/people/search-people`;
    return this.httpClient.post<any>(url, {searchPeopleData :searchPeopleData,user:user});
  }

  
  cardPeopleData(user:any){
    const url = `${this.apiUrl}/people/people-card`;
    return this.httpClient.post<any>(url,{user:user});
  }
  // function for the peopleDataChange event
  peopleDataChangeEvent(newData:any){
    this.peopleDataChange.next(newData);
  }

  // trigger event
  triggerEvent(){
    this.peopleChange.next(null);
  }

  deletePeople(id: any){
    return this.httpClient.get(`${this.apiUrl}/people/remove/${id}`, )
  }


  changePeopleSearchState(){
    this.peopleSearchClick.next(true);
  }

  closePeopleEditState(){
    this.PeopleEditCloseChange.next(true);
  }

}
