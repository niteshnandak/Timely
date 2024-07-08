import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
@Injectable({
  providedIn: 'root',
})
export class RegistrationService {
  constructor(private http: HttpClient) {}

  // FUNTION REGISTER AND RETURN TO THE API URL
  registerUser(userdetails: object) {

    //return
    return this.http.post('http://127.0.0.1:8000/api/register', userdetails);
  }

  // FUNTION VALIDATE THE TOKE BY CHECKING THE MAIL AND RETURN TO THE API URL
  validateToken(token: any) {

    //return
    return this.http.post<any>('http://127.0.0.1:8000/api/verify-user-token', {
      token: token,
    });
  }

  // FUNTION TO SAVE PASSWORD AND RETURN TO THE API URL
  savePassword(token: any, userdata: any) {

    //retun
    return this.http.post<any>('http://127.0.0.1:8000/api/save-password', {
      token: token,
      data: userdata,
    });
  }
}
