import { CommonModule, NgClass } from '@angular/common';
import { Component, EventEmitter, Output, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router, RouterLink, RouterLinkActive } from '@angular/router';
import { adminNavbarData, companyNavData, navbarData } from './nav-data';
import { AuthService } from '../../auth/auth-services/auth.service';
import { FormsModule } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { CompanyService } from '../../services/company.service';
import { AppAdminpageComponent } from '../application-admin/app-adminpage/app-adminpage.component';
import { LoaderComponent } from '../loader/loader.component';

@Component({
  selector: 'app-side-navbar',
  standalone: true,
  imports: [CommonModule, RouterLink, NgClass, RouterLinkActive, FormsModule, LoaderComponent],
  templateUrl: './side-navbar.component.html',
  styleUrls: ['./side-navbar.component.css']
})
export class SideNavbarComponent implements OnInit {
  companyId: string | null = null;
  isOpen = false;
  screenWidth = 0;
  showAdminOptions = false;
  showCompanyOptions = false;
  navData = navbarData.filter(item => !item.isBottom);
  bottomNavdata = navbarData.filter(item => item.isBottom);
  companyNav = companyNavData;
  adminNav = adminNavbarData;
  name: any;

  isLoading!: boolean;

  constructor(private route: ActivatedRoute, private router: Router, private toastr: ToastrService, private auth: AuthService, private companyService: CompanyService) {}

  ngOnInit() {
    this.name = this.auth.getUser();
    console.log(this.name.firstname);

    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.updateNavOptionsVisibility();
      }
    });
    // this.showCompanyOptions = this.checkIfCompanyDetailRoute(this.route);
    this.updateNavOptionsVisibility();
  }

  updateNavOptionsVisibility() {
    const companyId = this.companyService.getStoredCompanyId();
    this.companyId = companyId;
    this.showCompanyOptions = this.checkIfCompanyDetailRoute();
    this.showAdminOptions = this.checkIfAdminRoute();
  }

  checkIfCompanyDetailRoute(): boolean {
    const currentUrl = this.router.url;
    const allowedPaths = [
      '/company/company-details',
      '/company/company-details/company-settings',
      '/company/company-details/assignments',
      '/company/company-details/customers',
      '/company/company-details/timesheet'
    ];

    return allowedPaths.some(path => currentUrl.includes(path));
  }


  checkIfAdminRoute(): boolean {
    const currentUrl = this.router.url;
    return currentUrl.startsWith('/app-admin');
  }


  expandSidebar(): void {
    this.isOpen = true;
  }

  collapseSidebar(): void {
    this.isOpen = false;
  }




  // Logout Functionality
  logoutFromAllDevices: boolean = false;

  logout() {
    console.log(this.logoutFromAllDevices);

    const token = this.auth.getToken();

    this.isLoading = true;

    if (token && token !== null) {
      if (this.logoutFromAllDevices) {
        this.auth.logoutAllDevices().subscribe(
          (response: any) => {
            this.auth.logoutClient();
            console.log('Logged out from all Devices Successfully');
            this.toastr.success('Logged out from all Devices Successfully');
            this.router.navigate(['home']);

            this.isLoading = false;
          },
          (error) => {
            this.isLoading = false;

            console.log(error.error.message);
          }
        )
      } else {
        this.auth.logout().subscribe(
          (response: any) => {
            this.auth.logoutClient();
            console.log('Logged out Successfully');
            this.toastr.success('Logged out Successfully');
            this.router.navigate(['home']);

            this.isLoading = false;
          },
          (error) => {
            this.isLoading = false;

            console.log(error.error.message);
          }
        )
      }
    } else {
      this.isLoading = false;
      console.log('No token found in localstorage');
    }

  }
}
