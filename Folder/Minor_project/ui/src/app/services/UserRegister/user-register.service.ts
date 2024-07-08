import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class UserRegisterService {

  constructor(private http: HttpClient) { }

  registerUser(user: any) {
    return this.http.post<any>('http://127.0.0.1:8000/api/register', user)
  }

  setPasswordPage(token: any) {
    return this.http.post<any>('http://127.0.0.1:8000/api/set-password', { token: token });
  }
  saveUserPassword(token: any, userdata: any) {
    return this.http.post<any>('http://127.0.0.1:8000/api/save-password', { token: token, data: userdata });
  }
}
