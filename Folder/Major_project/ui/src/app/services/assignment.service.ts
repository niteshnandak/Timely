import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { assignment } from '../models/assignment';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AssignmentService {
  assignmentSearchClick = new BehaviorSubject<any>(false);
  assignmentSearchClickEvent = this.assignmentSearchClick.asObservable();

  constructor(private httpClient: HttpClient) {}

  apiUrl = 'http://127.0.0.1:8000/api';

  getAllAssignments(skip: number, take: number, company_id: any) {
    // const allAssignments = `${this.apiUrl}/assignment`;
    return this.httpClient.get<assignment>(
      this.apiUrl +
        '/assignment?skip=' +
        skip +
        '&take=' +
        take +
        '&company_id=' +
        company_id
    );
  }

  addAssignment(assignmentData: any, user: any) {
    //  return this.http.post(this.apiUrl, assignmentData);
    return this.httpClient.post<any>(this.apiUrl + '/assignment/create', {
      data: assignmentData,
      user: user,
    });
  }

  fetchPeopleNamesByCompanyId(companyId: any) {
    return this.httpClient.get<any>(
      `${this.apiUrl}/people/company/${companyId}`
    );
  }

  fetchCustomersNamesByCompanyId(companyId: any) {
    return this.httpClient.get<any>(
      `${this.apiUrl}/customers/company/${companyId}`
    );
  }

  searchAssignment(companyId: any, searchAssignmentData: any) {
    return this.httpClient.post<any>(
      this.apiUrl + '/assignment/search/Assignment?company_id=' + companyId,
      { data: searchAssignmentData }
    );
  }

  getEditAssignmentData(assignment_id: any) {
    return this.httpClient.get<any>(
      `${this.apiUrl}/assignment/${assignment_id}`
    );
  }

  saveEditAssignmentData(assignment_id: any, assignment_data: any, user: any) {
    return this.httpClient.post<any>(
      `${this.apiUrl}/assignment/save-assignment/${assignment_id}`,
      { assignment_data: assignment_data, user: user }
    );
  }

  deleteAssignment(assignment_id: any) {
    return this.httpClient.get(
      `${this.apiUrl}/assignment/delete/${assignment_id}`
    );
  }

  getAssignmentStats(company_id: any) {
    return this.httpClient.get<any>(
      `${this.apiUrl}/assignment/fetchAssignmentStats/${company_id}`
    );
  }

  changeAssignmentSearchState() {
    this.assignmentSearchClick.next(true);
  }

  loadAssignments(
    peopleId: any,
    skip: any,
    pageSize: any,
    searchFormData: any
  ) {
    console.log(peopleId);
    //return
    return this.httpClient.post<any>(
      `${this.apiUrl}/peoples/${peopleId}/assignments/grid/?skip=` +
        skip +
        '&take=' +
        pageSize,
      { searchFormData }
    );
  }
}
