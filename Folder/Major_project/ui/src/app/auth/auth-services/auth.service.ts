import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiUrl = 'http://127.0.0.1:8000/api';

  constructor(private http: HttpClient, private router: Router) { }


  // FUNTION FOR LOGIN
  login(identifier:string, password:string){
    return this.http.post<any>(`${this.apiUrl}/login`, {identifier, password});
  }

  // FUNCTION FOR LOGOUT OF CURRENR DEVICE
  logout() {
    return this.http.post<any>(`${this.apiUrl}/logout`, {});
  }

  // FUNCTION FOR LOGOUT OF ALL DEVICES
  logoutAllDevices() {
    return this.http.post<any>(`${this.apiUrl}/logoutAllDevices`, {});
  }

  // FUNCTION FOR FORGOT PASSWORD
  forgotPassword(identifier: string) {
    return this.http.post<any>(`${this.apiUrl}/forgot-password`, identifier);
  }

  // FUNCTION TO VALIDATE USER RESETING PASSWORD
  resetPasswordValidateUser(token: any) {
    return this.http.post<any>(`${this.apiUrl}/forgot-validate-user`, {token: token});
  }

  // FUNCTION TO RESET PASSWORD
  resetPassword(token: any, data: any) {
    return this.http.post<any>(`${this.apiUrl}/save-reset-password`, {token, data});
  }





  // FUNCTION TO SET TOKEN TO LOCAL STORAGE
  setToken(token: string): void {
    localStorage.setItem('token', token);
  }

  // FUNCTION TO FETCH TOKEN FROM LOCAL STORAGE
  getToken(): string | null {
    return localStorage.getItem('token');
  }

  // FUNCTION TO SET ROLE TO LOCAL STORAGE
  setRole(role: string): void {
    localStorage.setItem('role', role);
  }

  // FUNCTION TO FETCH ROLE FROM LOCAL STORAGE
  getRole(): string | null {
    return localStorage.getItem('role');
  }

  // TO SET USER TO SESSION STORAGE
  setUser(user:any): void {
    sessionStorage.setItem('user', JSON.stringify(user));
  }

  // TO FETCH USER FROM SESSION STORAGE
  user: any;
  getUser() {
    this.user = sessionStorage.getItem('user');
    return JSON.parse(this.user);
  }

  // FUNCTION TO CHECK IF USER IS LOGGED IN
  isLoggedIn(): boolean {
    return !!this.getToken(); // Returns true if token exists, false otherwise
  }

  // TO REMOVE DATA STORED IN LOCAL AND SESSION STORAGE
  logoutClient(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    sessionStorage.removeItem('user');
  }

}
