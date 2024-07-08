import { Component } from '@angular/core';
import { SideNavbarComponent } from '../../side-navbar/side-navbar.component';
import { RouterOutlet } from '@angular/router';
@Component({
  selector: 'app-app-adminpage',
  standalone: true,
  imports: [SideNavbarComponent,RouterOutlet],
  templateUrl: './app-adminpage.component.html',
  styleUrl: './app-adminpage.component.css'
})
export class AppAdminpageComponent {

}
