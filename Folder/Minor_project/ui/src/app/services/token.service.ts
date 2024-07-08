import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class TokenService {

  // private user: any;
  private token = 'currentUserToken';

  constructor(
    private router:Router,
    private http:HttpClient
  ) { }

    // Set user and token
    setUserAndToken(user: any, token: string){
      localStorage.setItem('currentUser', JSON.stringify(user));
      localStorage.setItem(this.token, token);
    }
  
    // Get token
    getToken(): string {
      return localStorage.getItem(this.token) || '';
    }
  
    // Clear user and token
    clearUserAndToken() {
      localStorage.removeItem('currentUser');
      localStorage.removeItem(this.token);
    }
}
