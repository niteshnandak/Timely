import { Component } from '@angular/core';
import { Router, RouterLink, RouterModule } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { TokenService } from '../services/token.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [
    RouterLink,
    RouterModule,
    CommonModule
  ],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent {

  constructor(
    private router:Router, 
    private auth:AuthService, 
    private toast:ToastrService, 
    private tokenService:TokenService) {}

  loggedout() {
    const token = localStorage.getItem('currentUserToken');
    // Check if token is not null
    if (token !== null) {
      // Here TypeScript knows that `token` is of type `string`
      this.auth.logout(token).subscribe({
        next:(res)=>{
          this.tokenService.clearUserAndToken();
          this.toast.success(res.toaster_success, 'Success');
          this.router.navigateByUrl('login');
        }, 
        error: (msg)=>{
          this.toast.error(msg['error']["toaster_error"],'Error');
        }
    });
    } else {
      // Handle case when token is not found in local storage
      console.error("Token not found in local storage");
    }
  }
}
