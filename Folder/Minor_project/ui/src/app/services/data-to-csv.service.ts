import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DataToCsvService {
  dataSend = new BehaviorSubject<any>(null);
  data = this.dataSend.asObservable();

  constructor() { }

  setData(newData:any){
    this.dataSend.next(newData);
  }
}
