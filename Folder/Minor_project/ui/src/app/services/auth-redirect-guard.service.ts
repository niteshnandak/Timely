import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';
import { Router, ActivatedRouteSnapshot, RouterStateSnapshot  } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthRedirectGuardService {

    constructor(private authService: AuthService, private router: Router) {}
  
    canActivate(): boolean {
      // Check if the user is already logged in
      if (this.authService.isLoggedIn()) {
        // Redirect the user to the dashboard or any other route
        this.router.navigateByUrl('/invoices');
        return false; // Prevent navigation to the login page
      } else {
        return true; // Allow navigation to the login page
      }
    }
  }
