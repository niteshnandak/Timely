import { Component } from '@angular/core';
import { Router, RouterOutlet } from '@angular/router';
import { HeaderComponent } from './header/header.component';
import { CommonModule } from '@angular/common';
import { HeaderComponent as LoginHeader} from './components/users/header/header.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterOutlet,
    CommonModule,
    HeaderComponent,
    LoginHeader
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'angular-timesheet';
  headerDisplay !: boolean;

  constructor(
    private router: Router
  ){}

  shouldShowHeader() : boolean{
    return this.router.url !== "/login" && 
           this.router.url !== "/register" && 
           this.router.url !== "/forgot-password" &&
           !this.router.url.includes("reset-password") &&
           !this.router.url.includes("set-password");
  }
}
