import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ReportsDataService {

  constructor() { }

  private reportsData: any;

  setReportsData(data: any) {
    this.reportsData = data;
    console.log(this.reportsData);
  }

  getReportsData() {
    return this.reportsData;
  }
}
