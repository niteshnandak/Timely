import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ExpensesService {

  apiUrl = 'http://127.0.0.1:8000/api';

  constructor(private http: HttpClient) { }

  getExpenses(companyId: any, skip: number = 0, take: number = 10, searchFormData: any) { // skip: number = 0, take: number = 10, searchFormData: any
    return this.http.post<any>(
      `${this.apiUrl}/expenses/${companyId}?skip=`+skip+'&take='+take,
      {searchFormData}
    );
  }

  addExpense(companyId: any, formData: any) {
    return this.http.post<any>(`${this.apiUrl}/expenses/${companyId}/addExpense`, {formData});
  }

  deleteExpense(companyId: any, expense_id: any) {
    return this.http.delete<any>(`${this.apiUrl}/expenses/${companyId}/deleteExpense/${expense_id}`);
  }

  approveExpense(companyId: any, expense_id: any) {
    return this.http.get<any>(`${this.apiUrl}/expenses/${companyId}/approveExpense/${expense_id}`);
  }

  getExpenseTypes() {
    return this.http.get<any>(`${this.apiUrl}/getExpenseTypes`);
  }

  // for dropdowns
  getPeopleName(companyId: any) {
    return this.http.get<any>(`${this.apiUrl}/expenses/${companyId}/getPeopleNames`);
  }

  //for payroll people dropdowns
  getPayrollBatchNames(people_id: any) {
    return this.http.get<any>(`${this.apiUrl}/payrolls/people/getPayrollBatchNames/${people_id}`);
  }

  getEditExpenseData(expense_id: any) {
    return this.http.get<any>(`${this.apiUrl}/expenses/${expense_id}`);
  }

  updateExpenseData(expense_id:any, formData: any) {
    return this.http.post<any>(`${this.apiUrl}/editExpense/${expense_id}`, {formData});
  }

  getExpensePersonName(people_id: any) {
    return this.http.get<any>(`${this.apiUrl}/getExpensePeopleName/${people_id}`);
  }

  getPeopleExpenses(peopleId: any, skip: number = 0, take: number = 10, searchFormData: any) {
    return this.http.post<any>(
      `${this.apiUrl}/expenses/people/${peopleId}?skip=`+skip+'&take='+take,
      {searchFormData}
    );
  }



  getPeoplePayrolls(peopleId: any, skip: number = 0, take: number = 10, searchFormData: any) {
    return this.http.post<any>(
      `${this.apiUrl}/payrolls/people/${peopleId}?skip=`+skip+'&take='+take,
      {searchFormData}
    );
  }

}
