import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(
    private router:Router,
    private http:HttpClient
  ){ }

  isLoggedIn(): boolean {
    // Check if the token exists in local storage
    const token = localStorage.getItem('currentUserToken');
    return !!token; // Returns true if token exists, false otherwise
  }

  apiUrl = "http://127.0.0.1:8000/api";


  login(data:any) {
    return this.http.post<any>(this.apiUrl+'/login',{data: data})
  }

  forgotPassword(data: any){
    return this.http.post<any>(this.apiUrl+'/forgot-password',{email:data})
  }

  resetPasswordValidateUser(token:any){
    return this.http.post<any>(this.apiUrl+'/forgot-validate-user', {token: token})
  }

  saveResetPassword(token:any, data:any){
    return this.http.post<any>(this.apiUrl+'/forgot-set-password', {token: token, data: data})
  }

  logout(token:any){
    return this.http.post<any>(this.apiUrl+'/logout', {token: token});
  }
}
